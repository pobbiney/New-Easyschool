<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Pos\PosCategory;
use App\Models\Pos\PosProduct;
use App\Models\Pos\PosSale;
use App\Models\Pos\PosSaleTransaction;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Services\Billing\PaystackService;
use App\Services\Pos\PosSaleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class PosSaleController extends Controller
{
    public function __construct(
        private PosSaleService $saleService,
        private PaystackService $paystackService,
    ) {}

    public function index()
    {
        $products = PosProduct::with('category')
            ->where('status', 'Active')
            ->orderBy('name')
            ->get();

        $categories = PosCategory::query()
            ->where('status', 'Active')
            ->withCount(['products' => fn ($query) => $query->where('status', 'Active')])
            ->orderBy('name')
            ->get();

        return view('pos.sale', [
            'products' => $products,
            'categories' => $categories,
            'totalProducts' => $products->count(),
            'placeholderImage' => asset('assets/images/pos-product-placeholder.svg'),
            'paymentMethods' => ['Cash', 'Paystack'],
            'paystackPublicKey' => $this->paystackService->publicKey(),
            'paystackConfigured' => $this->paystackService->isConfigured(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'nullable|exists:students,id',
            'customer_name' => 'nullable|string|max:150',
            'customer_phone' => 'nullable|string|max:30',
            'payment_method' => 'required|in:Cash,Paystack',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:pos_products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validated['payment_method'] === 'Paystack') {
            return $this->errorResponse($request, 'Use Paystack checkout to complete this payment.', 422);
        }

        return $this->finalizeSale($request, $validated);
    }

    public function initializePaystack(Request $request)
    {
        if (! $this->paystackService->isConfigured()) {
            return $this->errorResponse($request, 'Paystack is not configured.', 503);
        }

        $validated = $request->validate([
            'student_id' => 'nullable|exists:students,id',
            'customer_name' => 'nullable|string|max:150',
            'customer_phone' => 'nullable|string|max:30',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:pos_products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $student = ! empty($validated['student_id'])
            ? Student::findOrFail($validated['student_id'])
            : null;

        $productIds = collect($validated['items'])->pluck('product_id')->unique();
        $products = PosProduct::whereIn('id', $productIds)->get();
        $totals = $this->saleService->calculateTotals(
            $products,
            $validated['items'],
            (float) ($validated['discount'] ?? 0)
        );

        if ($totals['total'] <= 0) {
            return $this->errorResponse($request, 'Sale total must be greater than zero.', 422);
        }

        try {
            $this->validateCartStock($products, $validated['items']);
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($request, $e->getMessage(), 422);
        }

        PosSaleTransaction::query()
            ->where('created_by', Auth::id())
            ->where('status', PosSaleTransaction::STATUS_PENDING)
            ->update([
                'status' => PosSaleTransaction::STATUS_FAILED,
                'gateway_response' => ['message' => 'Superseded by a new checkout attempt.'],
            ]);

        $reference = $this->generatePaystackReference();
        $amountInPesewas = (int) round($totals['total'] * 100);

        $email = $this->paystackCustomerEmail($student, $validated['customer_phone'] ?? null);

        PosSaleTransaction::create([
            'reference' => $reference,
            'subtotal' => $totals['subtotal'],
            'discount' => $totals['discount'],
            'amount' => $totals['total'],
            'currency' => config('paystack.currency', 'GHS'),
            'status' => PosSaleTransaction::STATUS_PENDING,
            'student_id' => $student?->id,
            'customer_name' => $student ? null : ($validated['customer_name'] ?? null),
            'customer_phone' => $student ? null : ($validated['customer_phone'] ?? null),
            'notes' => $validated['notes'] ?? null,
            'cart_items' => $validated['items'],
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'message' => 'Paystack checkout ready.',
            'reference' => $reference,
            'public_key' => $this->paystackService->publicKey(),
            'email' => $email,
            'label' => $this->paystackCustomerLabel($student, $validated['customer_name'] ?? null),
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
            $sale = $this->completePaystackTransaction($validated['reference']);
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($request, $e->getMessage(), 422);
        } catch (RuntimeException $e) {
            return $this->errorResponse($request, $e->getMessage(), 422);
        }

        return $this->successResponse($request, $sale, 'Paystack payment verified successfully.');
    }

    public function history(Request $request)
    {
        $query = PosSale::with(['student', 'cashier'])->withCount('items')->latest('sold_at');

        if ($request->filled('receipt_no')) {
            $query->where('receipt_no', 'like', '%'.trim($request->receipt_no).'%');
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('sold_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('sold_at', '<=', $request->to_date);
        }

        if ($request->filled('q')) {
            $term = trim($request->q);
            $query->where(function ($inner) use ($term) {
                $inner->where('customer_name', 'like', "%{$term}%")
                    ->orWhere('customer_phone', 'like', "%{$term}%")
                    ->orWhereHas('student', function ($studentQuery) use ($term) {
                        $studentQuery->where('student_id', 'like', "%{$term}%")
                            ->orWhere('firstname', 'like', "%{$term}%")
                            ->orWhere('othername', 'like', "%{$term}%")
                            ->orWhere('surname', 'like', "%{$term}%");
                    });
            });
        }

        $sales = $query->paginate(25)->withQueryString();

        return view('pos.sales', [
            'sales' => $sales,
            'paymentMethods' => ['Cash', 'Paystack'],
            'filters' => $request->only(['receipt_no', 'payment_method', 'from_date', 'to_date', 'q']),
        ]);
    }

    public function receipt($id)
    {
        $sale = PosSale::with(['items', 'student.schoolClass.category', 'cashier'])->findOrFail($id);

        return view('pos.receipt', [
            'sale' => $sale,
            'school' => SchoolSetting::current(),
        ]);
    }

    public function searchStudents(Request $request)
    {
        $validated = $request->validate([
            'q' => 'nullable|string|max:100',
        ]);

        $query = trim($validated['q'] ?? '');

        if (strlen($query) < 2) {
            return response()->json(['students' => []]);
        }

        $students = Student::query()
            ->with('schoolClass.category')
            ->where(function ($inner) use ($query) {
                $inner->where('student_id', 'like', "%{$query}%")
                    ->orWhere('firstname', 'like', "%{$query}%")
                    ->orWhere('othername', 'like', "%{$query}%")
                    ->orWhere('surname', 'like', "%{$query}%");
            })
            ->orderBy('surname')
            ->orderBy('firstname')
            ->limit(20)
            ->get()
            ->map(fn (Student $student) => [
                'id' => $student->id,
                'student_id' => $student->student_id,
                'full_name' => $student->full_name,
                'class_name' => $student->class_name ?: 'Unassigned',
                'category_name' => $student->schoolClass?->category?->name,
            ]);

        return response()->json(['students' => $students]);
    }

    public function completePaystackFromWebhook(string $reference): PosSale
    {
        return $this->completePaystackTransaction($reference);
    }

    private function finalizeSale(Request $request, array $validated)
    {
        $productIds = collect($validated['items'])->pluck('product_id')->unique();
        $products = PosProduct::whereIn('id', $productIds)->get();
        $totals = $this->saleService->calculateTotals(
            $products,
            $validated['items'],
            (float) ($validated['discount'] ?? 0)
        );

        try {
            $sale = $this->saleService->processSale([
                'student_id' => $validated['student_id'] ?? null,
                'customer_name' => $validated['customer_name'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'payment_method' => $validated['payment_method'],
                'discount' => $totals['discount'],
                'subtotal' => $totals['subtotal'],
                'total' => $totals['total'],
                'notes' => $validated['notes'] ?? null,
                'sold_at' => now(),
            ], $validated['items']);
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($request, $e->getMessage(), 422);
        } catch (\Throwable $e) {
            report($e);

            return $this->errorResponse($request, 'Unable to complete sale. Please try again.', 500);
        }

        return $this->successResponse($request, $sale);
    }

    private function completePaystackTransaction(string $reference): PosSale
    {
        $transaction = PosSaleTransaction::query()
            ->where('reference', $reference)
            ->firstOrFail();

        if ($transaction->status === PosSaleTransaction::STATUS_SUCCESS && $transaction->pos_sale_id) {
            return PosSale::with(['items', 'student.schoolClass.category', 'cashier'])
                ->findOrFail($transaction->pos_sale_id);
        }

        $paystackData = $this->paystackService->verifyTransaction($reference);

        if (($paystackData['status'] ?? null) !== 'success') {
            $transaction->update([
                'status' => PosSaleTransaction::STATUS_FAILED,
                'gateway_response' => $paystackData,
            ]);

            throw new InvalidArgumentException('Paystack payment was not successful.');
        }

        $verifiedAmount = round(((int) ($paystackData['amount'] ?? 0)) / 100, 2);

        if (abs($verifiedAmount - (float) $transaction->amount) > 0.009) {
            throw new InvalidArgumentException('Verified payment amount does not match the initiated transaction.');
        }

        return DB::transaction(function () use ($transaction, $paystackData, $verifiedAmount) {
            $lockedTransaction = PosSaleTransaction::query()
                ->whereKey($transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedTransaction->status === PosSaleTransaction::STATUS_SUCCESS && $lockedTransaction->pos_sale_id) {
                return PosSale::with(['items', 'student.schoolClass.category', 'cashier'])
                    ->findOrFail($lockedTransaction->pos_sale_id);
            }

            $cartItems = $lockedTransaction->cart_items ?? [];

            $sale = $this->saleService->processSale([
                'student_id' => $lockedTransaction->student_id,
                'customer_name' => $lockedTransaction->customer_name,
                'customer_phone' => $lockedTransaction->customer_phone,
                'payment_method' => 'Paystack',
                'payment_reference' => $lockedTransaction->reference,
                'paystack_transaction_id' => isset($paystackData['id']) ? (string) $paystackData['id'] : null,
                'paystack_channel' => $paystackData['channel'] ?? null,
                'discount' => (float) $lockedTransaction->discount,
                'subtotal' => (float) $lockedTransaction->subtotal,
                'total' => $verifiedAmount,
                'notes' => $lockedTransaction->notes,
                'sold_at' => data_get($paystackData, 'paid_at', now()),
            ], $cartItems);

            $lockedTransaction->update([
                'status' => PosSaleTransaction::STATUS_SUCCESS,
                'paystack_transaction_id' => isset($paystackData['id']) ? (string) $paystackData['id'] : null,
                'paystack_channel' => $paystackData['channel'] ?? null,
                'gateway_response' => $paystackData,
                'pos_sale_id' => $sale->id,
            ]);

            return $sale;
        });
    }

    /**
     * @param  \Illuminate\Support\Collection<int, PosProduct>  $products
     * @param  array<int, array{product_id:int, quantity:int}>  $cartItems
     */
    private function validateCartStock($products, array $cartItems): void
    {
        foreach ($cartItems as $line) {
            $product = $products->firstWhere('id', $line['product_id']);

            if (! $product) {
                throw new InvalidArgumentException('One or more products are no longer available.');
            }

            $quantity = (int) $line['quantity'];

            if ($product->stock_qty < $quantity) {
                throw new InvalidArgumentException('Insufficient stock for '.$product->name.'. Available: '.$product->stock_qty);
            }
        }
    }

    private function generatePaystackReference(): string
    {
        do {
            $reference = 'POSPAY-'.now()->format('YmdHis').'-'.strtoupper(Str::random(8));
        } while (PosSaleTransaction::query()->where('reference', $reference)->exists());

        return $reference;
    }

    private function paystackCustomerEmail(?Student $student, ?string $phone): string
    {
        if ($student) {
            foreach ([$student->email, $student->guardian_email] as $candidate) {
                $candidate = trim((string) $candidate);

                if ($candidate !== '' && filter_var($candidate, FILTER_VALIDATE_EMAIL)) {
                    return $candidate;
                }
            }

            $digits = preg_replace('/\D/', '', (string) ($student->phone ?: $student->guardian_phone));

            if ($digits === '') {
                $digits = 'student'.$student->id;
            }

            return $this->buildPlaceholderEmail($digits);
        }

        $digits = preg_replace('/\D/', '', (string) $phone);

        if ($digits !== '') {
            return $this->buildPlaceholderEmail($digits);
        }

        $schoolEmail = trim((string) (SchoolSetting::current()->email ?? ''));

        if ($schoolEmail !== '' && filter_var($schoolEmail, FILTER_VALIDATE_EMAIL)) {
            return $schoolEmail;
        }

        return $this->buildPlaceholderEmail('walkin'.Auth::id().now()->format('His'));
    }

    private function buildPlaceholderEmail(string $localPart): string
    {
        $localPart = strtolower(preg_replace('/[^a-z0-9._+-]/', '', $localPart));

        if ($localPart === '' || ! preg_match('/[a-z]/', $localPart)) {
            $localPart = 'customer'.now()->format('YmdHis');
        }

        $email = $localPart.'@'.$this->paystackPlaceholderDomain();

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Unable to build a valid Paystack customer email.');
        }

        return $email;
    }

    private function paystackCustomerLabel(?Student $student, ?string $customerName): string
    {
        if ($student) {
            return $student->full_name;
        }

        return $customerName ?: 'Walk-in Customer';
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

    private function successResponse(Request $request, PosSale $sale, ?string $message = null)
    {
        $payload = [
            'success' => true,
            'message' => $message ?? 'Sale completed successfully.',
            'receipt_no' => $sale->receipt_no,
            'receipt_url' => route('pos-receipt', $sale->id),
        ];

        if ($request->expectsJson()) {
            return response()->json($payload);
        }

        return redirect($payload['receipt_url'])->with('message_success', $payload['message']);
    }

    private function errorResponse(Request $request, string $message, int $status = 422)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], $status);
        }

        return back()->with('message_error', $message);
    }
}
