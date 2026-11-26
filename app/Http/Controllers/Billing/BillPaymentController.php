<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\BillPayment;
use App\Models\BillPendingPayment;
use App\Http\Controllers\Pos\PosSaleController;
use App\Models\BillPaymentTransaction;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\StudentBill;
use App\Services\Billing\BillPaymentProcessor;
use App\Services\Billing\BillPaymentSmsService;
use App\Services\Billing\PaystackService;
use App\Services\Billing\PaymentMethodEligibility;
use App\Services\Billing\StudentBillCreditService;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class BillPaymentController extends Controller
{
    public function __construct(
        private PaystackService $paystackService,
        private StudentBillCreditService $creditService,
        private BillPaymentSmsService $billPaymentSmsService,
        private BillPaymentProcessor $paymentProcessor,
        private PaymentMethodEligibility $paymentMethodEligibility,
    ) {}

    public function cashier($id)
    {
        $student = Student::with(['schoolClass.category'])->findOrFail($id);
        $bills = $this->paymentProcessor->outstandingBillsForStudent($student);
        $totalOutstanding = $bills->sum('balance');
        $categoryBalances = $bills->groupBy('financial_category')->map->sum('balance');

        $creditBalance = $this->creditService->creditBalance($student);

        if ($bills->isEmpty() && $creditBalance <= 0) {
            return redirect()->route('student-bills')
                ->with('message_error', $student->full_name.' has no outstanding bills.');
        }

        return view('billing.record-payment', [
            'student' => $student,
            'bills' => $bills,
            'totalOutstanding' => $totalOutstanding,
            'categoryBalances' => $categoryBalances,
            'creditBalance' => $creditBalance,
            'netPayable' => Money::fromMinor(max(Money::toMinor((string) $totalOutstanding) - Money::toMinor($creditBalance), 0)),
            'printStatementUrl' => route('student-bill-print', $student->id),
            'paystackPublicKey' => $this->paystackService->publicKey(),
            'paystackConfigured' => $this->paystackService->isConfigured(),
            'hasOutstandingBills' => $bills->isNotEmpty(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'financial_category' => 'required|in:school_fees,feeding_fee',
            'amount' => 'required|decimal:0,2|min:0|max:9999999999.99',
            'credit_applied' => 'nullable|decimal:0,2|min:0|max:9999999999.99',
            'payment_method' => 'required|in:Cash,Bank',
            'bank_instrument' => 'required_if:payment_method,Bank|nullable|in:Cheque,Bankers Draft',
            'reference' => 'required_if:payment_method,Bank|nullable|string|max:100',
            'receipt_file' => 'required_if:payment_method,Bank|nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:4096',
            'paid_at' => 'required|date',
            'notes' => 'nullable|string|max:500',
            'allocations' => 'nullable|array',
            'allocations.*.student_bill_id' => 'required|exists:student_bills,id',
            'allocations.*.amount' => 'required|decimal:0,2|min:0.01|max:9999999999.99',
        ]);

        $student = Student::findOrFail($validated['student_id']);
        $cashAmount = (string) $validated['amount'];
        $creditApplied = (string) ($validated['credit_applied'] ?? '0');

        if (Money::toMinor($cashAmount) <= 0 && Money::toMinor($creditApplied) <= 0) {
            return $this->errorResponse($request, 'Enter a payment amount or apply credit.', 422);
        }

        try {
            $allocations = $this->paymentProcessor->resolveAllocations(
                $student->id,
                $validated['financial_category'],
                $cashAmount,
                $creditApplied,
                collect($validated['allocations'] ?? [])
            );

            if ($validated['payment_method'] === 'Bank') {
                $pending = $this->storePendingBankPayment(
                    $request,
                    $validated,
                    $student,
                    $cashAmount,
                    $creditApplied,
                    $allocations
                );

                return response()->json([
                    'message' => 'Bank payment saved as pending. It will post after approval.',
                    'pending' => true,
                    'pending_id' => $pending->id,
                    'redirect_url' => route('pending-bank-payments'),
                ]);
            }

            $payment = DB::transaction(function () use ($validated, $student, $cashAmount, $creditApplied, $allocations) {
                return $this->paymentProcessor->finalizePayment(
                    student: $student,
                    financialCategory: $validated['financial_category'],
                    cashAmount: $cashAmount,
                    creditApplied: $creditApplied,
                    paymentMethod: (Money::toMinor($cashAmount) <= 0 && Money::toMinor($creditApplied) > 0) ? 'Credit' : $validated['payment_method'],
                    paidAt: $validated['paid_at'],
                    allocations: $allocations,
                    reference: $validated['reference'] ?? null,
                    notes: $validated['notes'] ?? null,
                    createdBy: Auth::id(),
                );
            });
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($request, $e->getMessage(), 422);
        } catch (\Throwable $e) {
            report($e);

            return $this->errorResponse($request, 'Unable to record payment. Please try again.', 500);
        }

        $sms = $this->notifyParentBySms($payment);

        return $this->successResponse($request, $payment, 'Payment recorded successfully.', $sms);
    }

    public function initializePaystack(Request $request)
    {
        if (! $this->paystackService->isConfigured()) {
            return $this->errorResponse($request, 'Paystack is not configured.', 503);
        }

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'financial_category' => 'required|in:school_fees,feeding_fee',
            'amount' => 'required|decimal:0,2|min:0|max:9999999999.99',
            'credit_applied' => 'nullable|decimal:0,2|min:0|max:9999999999.99',
            'allocations' => 'nullable|array',
            'allocations.*.student_bill_id' => 'required|exists:student_bills,id',
            'allocations.*.amount' => 'required|decimal:0,2|min:0.01|max:9999999999.99',
        ]);

        $student = Student::findOrFail($validated['student_id']);
        $cashAmount = (string) $validated['amount'];
        $creditApplied = (string) ($validated['credit_applied'] ?? '0');

        if (Money::toMinor($cashAmount) <= 0 && Money::toMinor($creditApplied) <= 0) {
            return $this->errorResponse($request, 'Enter a payment amount or apply credit.', 422);
        }

        try {
            $this->paymentMethodEligibility->assertPaystackInitializationAllowed($validated['financial_category']);
            $allocations = $this->paymentProcessor->resolveAllocations(
                $student->id,
                $validated['financial_category'],
                $cashAmount,
                $creditApplied,
                collect($validated['allocations'] ?? [])
            );
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($request, $e->getMessage(), 422);
        }

        BillPaymentTransaction::query()
            ->where('student_id', $student->id)
            ->where('initiated_by', 'cashier')
            ->where('status', BillPaymentTransaction::STATUS_PENDING)
            ->update([
                'status' => BillPaymentTransaction::STATUS_FAILED,
                'gateway_response' => ['message' => 'Superseded by a new checkout attempt.'],
            ]);

        $reference = $this->generatePaystackReference();
        $amountInPesewas = Money::toMinor($cashAmount);

        $transaction = BillPaymentTransaction::create([
            'student_id' => $student->id,
            'financial_category' => $validated['financial_category'],
            'reference' => $reference,
            'amount' => $cashAmount,
            'credit_applied' => $creditApplied,
            'currency' => config('paystack.currency', 'GHS'),
            'status' => BillPaymentTransaction::STATUS_PENDING,
            'allocations' => $allocations->values()->all(),
            'created_by' => Auth::id(),
            'initiated_by' => 'cashier',
        ]);

        if (Money::toMinor($cashAmount) <= 0) {
            try {
                $payment = DB::transaction(fn () => $this->paymentProcessor->finalizePayment(
                    student: $student,
                    financialCategory: $validated['financial_category'],
                    cashAmount: 0,
                    creditApplied: $creditApplied,
                    paymentMethod: 'Credit',
                    paidAt: now()->toDateTimeString(),
                    allocations: $allocations,
                    reference: $reference,
                    notes: 'Paid fully from credit balance.',
                    createdBy: Auth::id(),
                ));

                $transaction->update([
                    'status' => BillPaymentTransaction::STATUS_SUCCESS,
                    'bill_payment_id' => $payment->id,
                ]);

                $sms = $this->notifyParentBySms($payment);

                return response()->json([
                    'message' => 'Payment completed using credit balance.',
                    'payment_id' => $payment->id,
                    'receipt_no' => $payment->receipt_no,
                    'receipt_url' => route('bill-payment-receipt', $payment->id),
                    'statement_url' => route('student-bill-print', $payment->student_id),
                    'credit_balance' => round((float) $payment->student->credit_balance, 2),
                    'credit_generated' => round((float) $payment->credit_generated, 2),
                    'paid_with_credit_only' => true,
                    'sms' => $sms,
                ]);
            } catch (InvalidArgumentException $e) {
                return $this->errorResponse($request, $e->getMessage(), 422);
            }
        }

        return response()->json([
            'message' => 'Paystack checkout ready.',
            'reference' => $reference,
            'public_key' => $this->paystackService->publicKey(),
            'email' => $this->paystackCustomerEmail($student),
            'label' => $student->full_name,
            'amount' => $amountInPesewas,
            'currency' => config('paystack.currency', 'GHS'),
        ]);
    }

    public function verifyPaystack(Request $request)
    {
        $validated = $request->validate([
            'reference' => 'required|string|max:100',
        ]);

        try {
            $payment = $this->completePaystackTransaction($validated['reference']);
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($request, $e->getMessage(), 422);
        } catch (RuntimeException $e) {
            return $this->errorResponse($request, $e->getMessage(), 422);
        }

        $sms = $this->notifyParentBySms($payment);

        return $this->successResponse($request, $payment, 'Paystack payment verified successfully.', $sms);
    }

    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('x-paystack-signature');

        if (! $this->paystackService->verifyWebhookSignature($payload, $signature)) {
            return response()->json(['message' => 'Invalid signature.'], 401);
        }

        $event = json_decode($payload, true);

        if (($event['event'] ?? null) !== 'charge.success') {
            return response()->json(['message' => 'Ignored.']);
        }

        $reference = data_get($event, 'data.reference');

        if (! $reference) {
            return response()->json(['message' => 'Missing reference.'], 422);
        }

        try {
            $this->completePaystackTransaction($reference);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            try {
                app(PosSaleController::class)->completePaystackFromWebhook($reference);
            } catch (InvalidArgumentException|RuntimeException|\Illuminate\Database\Eloquent\ModelNotFoundException) {
                return response()->json(['message' => 'Unable to process webhook.'], 422);
            }
        } catch (InvalidArgumentException|RuntimeException) {
            return response()->json(['message' => 'Unable to process webhook.'], 422);
        }

        return response()->json(['message' => 'Webhook processed.']);
    }

    public function receipt($id)
    {
        $payment = BillPayment::with([
            'student.schoolClass.category',
            'allocations.studentBill.billingItem',
            'allocations.studentBill.academicTerm',
            'allocations.studentBill.academicYear',
            'schoolVaultTransactions',
        ])->findOrFail($id);

        $payment->student->refresh();

        return view('billing.payment-receipt', [
            'payment' => $payment,
            'school' => SchoolSetting::current(),
            'statementUrl' => route('student-bill-print', $payment->student_id),
            'backUrl' => route('student-bills'),
            'backLabel' => 'Back to Student Bills',
            'categoryOutstanding' => $payment->financial_category
                ? StudentBill::query()->where('student_id', $payment->student_id)->where('financial_category', $payment->financial_category)->sum('balance')
                : null,
        ]);
    }

    private function completePaystackTransaction(string $reference): BillPayment
    {
        $transaction = BillPaymentTransaction::query()
            ->where('reference', $reference)
            ->firstOrFail();

        if ($transaction->status === BillPaymentTransaction::STATUS_SUCCESS && $transaction->bill_payment_id) {
            return BillPayment::with(['student', 'allocations.studentBill.billingItem'])
                ->findOrFail($transaction->bill_payment_id);
        }

        $paystackData = $this->paystackService->verifyTransaction($reference);

        if (($paystackData['status'] ?? null) !== 'success') {
            $transaction->update([
                'status' => BillPaymentTransaction::STATUS_FAILED,
                'gateway_response' => $paystackData,
            ]);

            throw new InvalidArgumentException('Paystack payment was not successful.');
        }

        if ((string) ($paystackData['reference'] ?? '') !== $transaction->reference) {
            throw new InvalidArgumentException('Verified payment reference does not match the initiated transaction.');
        }

        if (strtoupper((string) ($paystackData['currency'] ?? '')) !== strtoupper($transaction->currency)) {
            throw new InvalidArgumentException('Verified payment currency does not match the initiated transaction.');
        }

        $verifiedAmountInMinorUnits = (int) ($paystackData['amount'] ?? 0);
        $expectedAmountInMinorUnits = Money::toMinor((string) $transaction->amount);

        if ($verifiedAmountInMinorUnits !== $expectedAmountInMinorUnits) {
            throw new InvalidArgumentException('Verified payment amount does not match the initiated transaction.');
        }

        $verifiedAmount = Money::fromMinor($verifiedAmountInMinorUnits);

        return DB::transaction(function () use ($transaction, $paystackData, $verifiedAmount) {
            $lockedTransaction = BillPaymentTransaction::query()
                ->whereKey($transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedTransaction->status === BillPaymentTransaction::STATUS_SUCCESS && $lockedTransaction->bill_payment_id) {
                return BillPayment::with(['student', 'allocations.studentBill.billingItem'])
                    ->findOrFail($lockedTransaction->bill_payment_id);
            }

            $student = Student::findOrFail($lockedTransaction->student_id);
            $allocations = collect($lockedTransaction->allocations ?? []);
            $financialCategory = $lockedTransaction->financial_category
                ?: $this->paymentProcessor->financialCategoryForAllocations($student->id, $allocations);
            $creditApplied = (string) $lockedTransaction->credit_applied;

            $payment = $this->paymentProcessor->finalizePayment(
                student: $student,
                financialCategory: $financialCategory,
                cashAmount: $verifiedAmount,
                creditApplied: $creditApplied,
                paymentMethod: 'Paystack',
                paidAt: data_get($paystackData, 'paid_at', now()->toDateTimeString()),
                allocations: $allocations,
                reference: $lockedTransaction->reference,
                notes: null,
                paymentChannel: $paystackData['channel'] ?? null,
                gatewayTransactionId: isset($paystackData['id']) ? (string) $paystackData['id'] : null,
                createdBy: $lockedTransaction->created_by,
                historicalGatewayCompletion: true,
            );

            $lockedTransaction->update([
                'status' => BillPaymentTransaction::STATUS_SUCCESS,
                'paystack_transaction_id' => isset($paystackData['id']) ? (string) $paystackData['id'] : null,
                'paystack_channel' => $paystackData['channel'] ?? null,
                'gateway_response' => $paystackData,
                'bill_payment_id' => $payment->id,
            ]);

            $this->notifyParentBySms($payment);

            return $payment;
        });
    }

    private function notifyParentBySms(BillPayment $payment): array
    {
        try {
            return $this->billPaymentSmsService->sendPaymentConfirmation($payment);
        } catch (\Throwable $e) {
            report($e);

            return [
                'sent' => false,
                'phone' => null,
                'message' => 'Unable to send SMS notification.',
            ];
        }
    }

    private function generatePaystackReference(): string
    {
        do {
            $reference = 'BILL-'.now()->format('YmdHis').'-'.strtoupper(Str::random(8));
        } while (BillPaymentTransaction::query()->where('reference', $reference)->exists());

        return $reference;
    }

    private function paystackCustomerEmail(Student $student): string
    {
        $phone = preg_replace('/\D/', '', (string) $student->phone);

        if ($phone === '') {
            $phone = 'student'.$student->id;
        }

        return $phone.'@'.$this->paystackPlaceholderDomain();
    }

    private function paystackPlaceholderDomain(): string
    {
        $configured = trim((string) config('paystack.placeholder_domain', ''));

        if ($configured !== '') {
            return $configured;
        }

        $school = SchoolSetting::current();
        $schoolEmail = trim((string) ($school->email ?? ''));

        if ($schoolEmail !== '' && filter_var($schoolEmail, FILTER_VALIDATE_EMAIL)) {
            return substr(strrchr($schoolEmail, '@'), 1);
        }

        $websiteHost = parse_url((string) ($school->website ?? ''), PHP_URL_HOST);

        if (is_string($websiteHost) && $websiteHost !== '' && ! in_array($websiteHost, ['localhost', '127.0.0.1'], true)) {
            return preg_replace('/^www\./', '', $websiteHost);
        }

        return 'easyschool.com';
    }

    public function pendingBankPayments()
    {
        $pending = BillPendingPayment::with(['student.schoolClass', 'creator'])
            ->where('status', BillPendingPayment::STATUS_PENDING)
            ->latest()
            ->get();

        $history = BillPendingPayment::with(['student', 'approver', 'billPayment'])
            ->where('status', '!=', BillPendingPayment::STATUS_PENDING)
            ->latest('updated_at')
            ->limit(40)
            ->get();

        return view('billing.pending-bank-payments', [
            'pending' => $pending,
            'history' => $history,
        ]);
    }

    public function approvePendingBankPayment(Request $request, BillPendingPayment $pending)
    {
        if (! $pending->isPending()) {
            return $this->errorResponse($request, 'This bank payment is no longer pending.', 422);
        }

        try {
            $payment = DB::transaction(function () use ($pending) {
                $locked = BillPendingPayment::query()->whereKey($pending->id)->lockForUpdate()->firstOrFail();

                if (! $locked->isPending()) {
                    throw new InvalidArgumentException('This bank payment is no longer pending.');
                }

                $student = Student::findOrFail($locked->student_id);
                $cashAmount = (string) $locked->amount;
                $creditApplied = (string) $locked->credit_applied;
                $storedAllocations = collect($locked->allocations ?? []);
                $financialCategory = $locked->financial_category
                    ?: $this->paymentProcessor->financialCategoryForAllocations($student->id, $storedAllocations);
                $allocations = $this->paymentProcessor->resolveAllocations(
                    $student->id,
                    $financialCategory,
                    $cashAmount,
                    $creditApplied,
                    $storedAllocations
                );

                $notes = trim((string) $locked->notes);
                $instrumentNote = $locked->bank_instrument.' · '.$locked->reference;
                $posted = $this->paymentProcessor->finalizePayment(
                    student: $student,
                    financialCategory: $financialCategory,
                    cashAmount: $cashAmount,
                    creditApplied: $creditApplied,
                    paymentMethod: 'Bank',
                    paidAt: $locked->paid_at?->toDateTimeString() ?? now()->toDateTimeString(),
                    allocations: $allocations,
                    reference: $locked->reference,
                    notes: $notes !== '' ? $notes.' ('.$instrumentNote.')' : $instrumentNote,
                    paymentChannel: $locked->bank_instrument,
                    createdBy: $locked->created_by,
                );

                $locked->update([
                    'status' => BillPendingPayment::STATUS_APPROVED,
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                    'bill_payment_id' => $posted->id,
                ]);

                return $posted;
            });
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($request, $e->getMessage(), 422);
        } catch (\Throwable $e) {
            report($e);

            return $this->errorResponse($request, 'Unable to approve this bank payment.', 500);
        }

        $sms = $this->notifyParentBySms($payment);

        return redirect()
            ->route('pending-bank-payments')
            ->with('message_success', 'Bank payment approved and posted. Receipt '.$payment->receipt_no.'.')
            ->with('payment_sms_status', $sms);
    }

    public function rejectPendingBankPayment(Request $request, BillPendingPayment $pending)
    {
        $validated = $request->validate([
            'rejected_reason' => 'nullable|string|max:500',
        ]);

        try {
            DB::transaction(function () use ($pending, $validated) {
                $locked = BillPendingPayment::query()
                    ->whereKey($pending->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $locked->isPending()) {
                    throw new InvalidArgumentException('This bank payment is no longer pending.');
                }

                $locked->update([
                    'status' => BillPendingPayment::STATUS_REJECTED,
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                    'rejected_reason' => $validated['rejected_reason'] ?? null,
                ]);
            });
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($request, $e->getMessage(), 422);
        }

        return redirect()
            ->route('pending-bank-payments')
            ->with('message_success', 'Bank payment rejected. Student bills were not updated.');
    }

    private function storePendingBankPayment(
        Request $request,
        array $validated,
        Student $student,
        string $cashAmount,
        string $creditApplied,
        $allocations
    ): BillPendingPayment {
        $receiptPath = $this->storeReceiptFile($request->file('receipt_file'));

        return BillPendingPayment::create([
            'student_id' => $student->id,
            'financial_category' => $validated['financial_category'],
            'amount' => $cashAmount,
            'credit_applied' => $creditApplied,
            'allocations' => $allocations->values()->all(),
            'payment_method' => 'Bank',
            'bank_instrument' => $validated['bank_instrument'],
            'reference' => trim((string) $validated['reference']),
            'receipt_path' => $receiptPath,
            'paid_at' => $validated['paid_at'],
            'notes' => $validated['notes'] ?? null,
            'status' => BillPendingPayment::STATUS_PENDING,
            'created_by' => Auth::id(),
        ]);
    }

    private function storeReceiptFile($file): string
    {
        $folder = 'uploads/bill-payments';

        if (! is_dir(public_path($folder))) {
            mkdir(public_path($folder), 0755, true);
        }

        $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
        $file->move(public_path($folder), $filename);

        return $folder.'/'.$filename;
    }

    private function successResponse(Request $request, BillPayment $payment, string $message, ?array $sms = null)
    {
        if ($request->expectsJson()) {
            $payment->loadMissing('student');

            return response()->json([
                'message' => $message,
                'payment_id' => $payment->id,
                'receipt_no' => $payment->receipt_no,
                'receipt_url' => route('bill-payment-receipt', $payment->id),
                'statement_url' => route('student-bill-print', $payment->student_id),
                'credit_balance' => round((float) $payment->student->credit_balance, 2),
                'credit_generated' => round((float) $payment->credit_generated, 2),
                'sms' => $sms,
            ]);
        }

        $redirect = redirect()->route('bill-payment-receipt', $payment->id)
            ->with('message_success', $message);

        if ($sms) {
            $redirect->with('payment_sms_status', $sms);
        }

        return $redirect;
    }

    private function errorResponse(Request $request, string $message, int $status = 422)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $status);
        }

        return back()->with('message_error', $message);
    }
}
