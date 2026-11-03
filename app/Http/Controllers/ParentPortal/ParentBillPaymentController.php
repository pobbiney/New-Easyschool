<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\BillPayment;
use App\Models\BillPaymentTransaction;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Services\Billing\BillPaymentProcessor;
use App\Services\Billing\BillPaymentSmsService;
use App\Services\Billing\PaystackService;
use App\Services\ParentPortal\ParentStudentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class ParentBillPaymentController extends Controller
{
    public function __construct(
        private ParentStudentService $parentStudentService,
        private BillPaymentProcessor $paymentProcessor,
        private PaystackService $paystackService,
        private BillPaymentSmsService $billPaymentSmsService,
    ) {}

    public function applyCredit(Request $request, Student $student)
    {
        $parent = Auth::guard('parent')->user();
        $child = $this->parentStudentService->findOwnedStudent($parent, $student->id);

        if (! $child) {
            abort(403);
        }

        $validated = $request->validate([
            'credit_applied' => 'required|numeric|min:0.01',
            'allocations' => 'nullable|array',
            'allocations.*.student_bill_id' => 'required|exists:student_bills,id',
            'allocations.*.amount' => 'required|numeric|min:0.01',
        ]);

        $creditApplied = round((float) $validated['credit_applied'], 2);

        try {
            $allocations = $this->paymentProcessor->resolveAllocations(
                $child->id,
                0,
                $creditApplied,
                collect($validated['allocations'] ?? [])
            );

            $payment = DB::transaction(function () use ($child, $creditApplied, $allocations) {
                return $this->paymentProcessor->finalizePayment(
                    student: $child,
                    cashAmount: 0,
                    creditApplied: $creditApplied,
                    paymentMethod: 'Credit',
                    paidAt: now()->toDateTimeString(),
                    allocations: $allocations,
                    notes: 'Credit applied via parent portal.',
                    createdBy: null,
                );
            });
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $sms = $this->notifyParentBySms($payment);

        return response()->json([
            'message' => 'Credit applied successfully.',
            'payment_id' => $payment->id,
            'receipt_no' => $payment->receipt_no,
            'receipt_url' => route('parent.payment.receipt', [$child, $payment]),
            'sms' => $sms,
        ]);
    }

    public function initializePaystack(Request $request, Student $student)
    {
        if (! $this->paystackService->isConfigured()) {
            return response()->json(['message' => 'Paystack is not configured.'], 503);
        }

        $parent = Auth::guard('parent')->user();
        $child = $this->parentStudentService->findOwnedStudent($parent, $student->id);

        if (! $child) {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'credit_applied' => 'nullable|numeric|min:0',
            'allocations' => 'nullable|array',
            'allocations.*.student_bill_id' => 'required|exists:student_bills,id',
            'allocations.*.amount' => 'required|numeric|min:0.01',
        ]);

        $cashAmount = (float) $validated['amount'];
        $creditApplied = round((float) ($validated['credit_applied'] ?? 0), 2);

        try {
            $allocations = $this->paymentProcessor->resolveAllocations(
                $child->id,
                $cashAmount,
                $creditApplied,
                collect($validated['allocations'] ?? [])
            );
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $reference = $this->generatePaystackReference();
        $amountInPesewas = (int) round($cashAmount * 100);

        BillPaymentTransaction::query()
            ->where('student_id', $child->id)
            ->where('initiated_by', 'parent')
            ->where('parent_account_id', $parent->id)
            ->where('status', BillPaymentTransaction::STATUS_PENDING)
            ->update([
                'status' => BillPaymentTransaction::STATUS_FAILED,
                'gateway_response' => ['message' => 'Superseded by a new checkout attempt.'],
            ]);

        BillPaymentTransaction::create([
            'student_id' => $child->id,
            'reference' => $reference,
            'amount' => $cashAmount,
            'credit_applied' => $creditApplied,
            'currency' => config('paystack.currency', 'GHS'),
            'status' => BillPaymentTransaction::STATUS_PENDING,
            'allocations' => $allocations->values()->all(),
            'created_by' => null,
            'parent_account_id' => $parent->id,
            'initiated_by' => 'parent',
        ]);

        return response()->json([
            'message' => 'Paystack checkout ready.',
            'reference' => $reference,
            'public_key' => $this->paystackService->publicKey(),
            'email' => $this->paystackCustomerEmail($child),
            'label' => $child->full_name,
            'amount' => $amountInPesewas,
            'currency' => config('paystack.currency', 'GHS'),
        ]);
    }

    public function verifyPaystack(Request $request, Student $student)
    {
        $parent = Auth::guard('parent')->user();
        $child = $this->parentStudentService->findOwnedStudent($parent, $student->id);

        if (! $child) {
            abort(403);
        }

        $validated = $request->validate([
            'reference' => 'required|string|max:100',
        ]);

        try {
            $payment = $this->completePaystackTransaction($validated['reference'], $child->id);
        } catch (InvalidArgumentException|RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $sms = $this->notifyParentBySms($payment);

        return response()->json([
            'message' => 'Payment verified successfully.',
            'payment_id' => $payment->id,
            'receipt_no' => $payment->receipt_no,
            'receipt_url' => route('parent.payment.receipt', [$child, $payment]),
            'sms' => $sms,
        ]);
    }

    private function completePaystackTransaction(string $reference, int $studentId): BillPayment
    {
        $transaction = BillPaymentTransaction::query()
            ->where('reference', $reference)
            ->where('student_id', $studentId)
            ->where('initiated_by', 'parent')
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

        $verifiedAmount = round(((int) ($paystackData['amount'] ?? 0)) / 100, 2);

        if (abs($verifiedAmount - (float) $transaction->amount) > 0.009) {
            throw new InvalidArgumentException('Verified payment amount does not match the initiated transaction.');
        }

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
            $creditApplied = round((float) $lockedTransaction->credit_applied, 2);

            $payment = $this->paymentProcessor->finalizePayment(
                student: $student,
                cashAmount: $verifiedAmount,
                creditApplied: $creditApplied,
                paymentMethod: 'Paystack',
                paidAt: data_get($paystackData, 'paid_at', now()->toDateTimeString()),
                allocations: $allocations,
                reference: $lockedTransaction->reference,
                notes: 'Paid via parent portal.',
                paymentChannel: $paystackData['channel'] ?? null,
                gatewayTransactionId: isset($paystackData['id']) ? (string) $paystackData['id'] : null,
                createdBy: null,
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
            $reference = 'PBILL-'.now()->format('YmdHis').'-'.strtoupper(Str::random(8));
        } while (BillPaymentTransaction::query()->where('reference', $reference)->exists());

        return $reference;
    }

    private function paystackCustomerEmail(Student $student): string
    {
        $phone = preg_replace('/\D/', '', (string) $student->guardian_phone);

        if ($phone === '') {
            $phone = 'parent'.$student->id;
        }

        $school = SchoolSetting::current();
        $schoolEmail = trim((string) ($school->email ?? ''));

        if ($schoolEmail !== '' && filter_var($schoolEmail, FILTER_VALIDATE_EMAIL)) {
            $domain = substr(strrchr($schoolEmail, '@'), 1);

            return $phone.'@'.$domain;
        }

        return $phone.'@parents.easyschool.local';
    }
}
