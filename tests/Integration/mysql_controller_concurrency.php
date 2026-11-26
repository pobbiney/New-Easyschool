<?php

use App\Http\Controllers\Billing\BillPaymentController;
use App\Models\BillPaymentTransaction;
use App\Models\BillPendingPayment;
use App\Models\BillingItem;
use App\Services\Billing\PaystackService;
use App\Support\Money;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

if (DB::connection()->getDriverName() !== 'mysql' || DB::connection()->getDatabaseName() !== 'new_easyschool_task010_clean') {
    fwrite(STDERR, "Refusing to run outside the dedicated Task 010 MySQL database.\n");
    exit(2);
}

final class ConcurrencyPaystackService extends PaystackService
{
    public function verifyTransaction(string $reference): array
    {
        $transaction = BillPaymentTransaction::where('reference', $reference)->firstOrFail();

        return [
            'id' => 'mock-'.$transaction->id,
            'status' => 'success',
            'reference' => $reference,
            'currency' => 'GHS',
            'amount' => Money::toMinor((string) $transaction->amount),
            'channel' => 'mobile_money',
            'paid_at' => now()->toDateTimeString(),
        ];
    }

    public function verifyWebhookSignature(string $payload, ?string $signature): bool
    {
        return is_string($signature) && hash_equals(hash_hmac('sha512', $payload, 'task010-test-secret'), $signature);
    }
}

$app->instance(PaystackService::class, new ConcurrencyPaystackService());

$request = function (string $uri, array $parameters = [], ?string $content = null, array $headers = []): Request {
    $server = ['HTTP_ACCEPT' => 'application/json'];
    foreach ($headers as $name => $value) {
        $server['HTTP_'.strtoupper(str_replace('-', '_', $name))] = $value;
    }
    return Request::create($uri, 'POST', $parameters, [], [], $server, $content);
};

$concurrent = function (array $jobs): array {
    $barrier = sys_get_temp_dir().'/task010-controller-barrier-'.bin2hex(random_bytes(6));
    $results = $barrier.'-result-';
    $pids = [];

    foreach ($jobs as $index => $job) {
        $pid = pcntl_fork();
        if ($pid === 0) {
            DB::disconnect();
            DB::reconnect();
            while (! file_exists($barrier)) {
                usleep(1000);
            }
            try {
                $response = $job();
                $result = ['ok' => true, 'status' => $response->getStatusCode()];
            } catch (Throwable $e) {
                $result = ['ok' => false, 'error' => $e::class, 'message' => $e->getMessage()];
            }
            file_put_contents($results.$index, json_encode($result));
            exit(0);
        }
        $pids[] = $pid;
    }

    file_put_contents($barrier, 'go');
    foreach ($pids as $pid) {
        pcntl_waitpid($pid, $status);
    }
    DB::disconnect();
    DB::reconnect();

    $values = [];
    foreach (array_keys($jobs) as $index) {
        $values[] = json_decode(file_get_contents($results.$index), true);
        unlink($results.$index);
    }
    unlink($barrier);

    return $values;
};

$tables = [
    'school_vault_transactions', 'school_vaults', 'student_bill_credit_transactions',
    'bill_payment_allocations', 'bill_pending_payments', 'bill_payment_transactions', 'bill_payment_receipt_sequences',
    'bill_payments', 'student_bills', 'category_bill_setup_items', 'category_bill_setups',
    'billing_items', 'students', 'academic_terms', 'academic_years', 'class_categories',
];

$reset = function () use ($tables): array {
    DB::statement('SET FOREIGN_KEY_CHECKS=0');
    foreach ($tables as $table) {
        DB::table($table)->truncate();
    }
    DB::statement('SET FOREIGN_KEY_CHECKS=1');

    DB::table('users')->insertOrIgnore([
        'id' => 1, 'name' => 'Task 010 Controller Fixture', 'email' => 'task010-controller@example.test',
        'password' => 'not-a-login-credential', 'status' => 'Active', 'user_cat' => '1', 'cat_id' => 1,
        'created_at' => now(), 'updated_at' => now(),
    ]);

    DB::table('class_categories')->insert(['id' => 1, 'name' => 'Test Category', 'status' => 'Active', 'created_by' => 1, 'created_at' => now(), 'updated_at' => now()]);
    DB::table('academic_years')->insert(['id' => 1, 'name' => '2026/2027', 'status' => 'Active', 'created_by' => 1, 'created_at' => now(), 'updated_at' => now()]);
    DB::table('academic_terms')->insert(['id' => 1, 'name' => 'Term 1', 'sort_order' => 1, 'status' => 'Active', 'created_at' => now(), 'updated_at' => now()]);
    DB::table('students')->insert([
        'id' => 1, 'student_id' => 'TASK010-STUDENT', 'academic_year' => '2026/2027', 'class_name' => 'Test Class',
        'firstname' => 'Concurrency', 'surname' => 'Fixture', 'gender' => 'Other', 'dob' => '2010-01-01',
        'phone' => '0200000000', 'status' => 'Active', 'credit_balance' => '0.00', 'created_by' => 1,
        'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('billing_items')->insert([
        'id' => 1, 'name' => 'Task 010 Feeding Fee', 'description' => null, 'status' => 'Active',
        'is_compulsory' => false, 'financial_category' => BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE,
        'created_by' => 1, 'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('category_bill_setups')->insert([
        'id' => 1, 'class_category_id' => 1, 'academic_year_id' => 1, 'academic_term_id' => 1,
        'status' => 'Active', 'created_by' => 1, 'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('category_bill_setup_items')->insert([
        'id' => 1, 'category_bill_setup_id' => 1, 'billing_item_id' => 1, 'amount' => '100.00',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('student_bills')->insert([
        'id' => 1, 'student_id' => 1, 'category_bill_setup_id' => 1, 'billing_item_id' => 1,
        'financial_category' => BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE,
        'amount_due' => '100.00', 'amount_paid' => '0.00', 'balance' => '100.00', 'status' => 'Pending',
        'created_at' => now(), 'updated_at' => now(),
    ]);

    return [['student_bill_id' => 1, 'amount' => '60.00']];
};

$pending = function (string $reference, array $allocations, string $amount = '60.00'): int {
    return DB::table('bill_pending_payments')->insertGetId([
        'student_id' => 1, 'financial_category' => BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE,
        'amount' => $amount, 'credit_applied' => '0.00', 'allocations' => json_encode($allocations),
        'payment_method' => 'Bank', 'bank_instrument' => 'Cheque', 'reference' => $reference,
        'paid_at' => now(), 'status' => BillPendingPayment::STATUS_PENDING, 'created_by' => 1,
        'created_at' => now(), 'updated_at' => now(),
    ]);
};

$transaction = function (string $reference, string $amount, array $allocations): int {
    return DB::table('bill_payment_transactions')->insertGetId([
        'student_id' => 1, 'financial_category' => BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE,
        'reference' => $reference, 'amount' => $amount, 'credit_applied' => '0.00', 'currency' => 'GHS',
        'status' => BillPaymentTransaction::STATUS_PENDING, 'allocations' => json_encode($allocations),
        'created_by' => 1, 'initiated_by' => 'cashier', 'created_at' => now(), 'updated_at' => now(),
    ]);
};

$approve = fn (int $id) => app(BillPaymentController::class)->approvePendingBankPayment(
    $request('/pending-bank-payments/'.$id.'/approve'),
    BillPendingPayment::findOrFail($id),
);
$reject = fn (int $id) => app(BillPaymentController::class)->rejectPendingBankPayment(
    $request('/pending-bank-payments/'.$id.'/reject'),
    BillPendingPayment::findOrFail($id),
);
$verify = fn (string $reference) => app(BillPaymentController::class)->verifyPaystack(
    $request('/paystack/bill-payment/verify', ['reference' => $reference]),
);
$webhook = function (string $reference) use ($request) {
    $payload = json_encode(['event' => 'charge.success', 'data' => ['reference' => $reference]]);
    $signature = hash_hmac('sha512', $payload, 'task010-test-secret');

    return app(BillPaymentController::class)->webhook(
        $request('/paystack/webhook', [], $payload, ['x-paystack-signature' => $signature]),
    );
};

$report = [];

$allocations = $reset();
$id = $pending('BANK-SAME', $allocations);
$report['simultaneous_approval'] = $concurrent([fn () => $approve($id), fn () => $approve($id)]);
$report['simultaneous_approval_state'] = [
    BillPendingPayment::findOrFail($id)->status, DB::table('bill_payments')->count(),
    DB::table('school_vault_transactions')->count(), DB::table('bill_payment_allocations')->count(),
];

$allocations = $reset();
$id = $pending('BANK-DECISION', $allocations);
$report['approval_vs_rejection'] = $concurrent([fn () => $approve($id), fn () => $reject($id)]);
$report['approval_vs_rejection_state'] = [
    BillPendingPayment::findOrFail($id)->status, DB::table('bill_payments')->count(),
    DB::table('school_vault_transactions')->count(),
];

$allocations = $reset();
$transaction('PAYSTACK-WEBHOOK', '60.00', $allocations);
$report['concurrent_callbacks'] = $concurrent([fn () => $webhook('PAYSTACK-WEBHOOK'), fn () => $webhook('PAYSTACK-WEBHOOK')]);
$report['concurrent_callbacks_state'] = [DB::table('bill_payments')->count(), DB::table('school_vault_transactions')->count(), DB::table('bill_payment_allocations')->count()];

$allocations = $reset();
$transaction('PAYSTACK-MIXED', '60.00', $allocations);
$report['callback_vs_verification'] = $concurrent([fn () => $webhook('PAYSTACK-MIXED'), fn () => $verify('PAYSTACK-MIXED')]);
$report['callback_vs_verification_state'] = [DB::table('bill_payments')->count(), DB::table('school_vault_transactions')->count(), DB::table('bill_payment_allocations')->count()];

$allocations = $reset();
$transaction('PAYSTACK-RETRY', '60.00', $allocations);
DB::table('school_vaults')->insert(['account_code' => 'SCHOOL_VAULT', 'name' => 'School Vault', 'currency' => 'GHS', 'status' => 'Inactive', 'created_at' => now(), 'updated_at' => now()]);
$first = $verify('PAYSTACK-RETRY');
$afterFailure = [
    $first->getStatusCode(), BillPaymentTransaction::where('reference', 'PAYSTACK-RETRY')->value('status'),
    DB::table('bill_payments')->count(), DB::table('school_vault_transactions')->count(),
];
DB::table('school_vaults')->update(['status' => 'Active']);
$second = $verify('PAYSTACK-RETRY');
$report['retry_after_posting_failure'] = [$afterFailure, $second->getStatusCode(), DB::table('bill_payments')->count(), DB::table('school_vault_transactions')->count()];

$allocations = $reset();
$firstPending = $pending('BANK-ALLOC-A', $allocations);
$secondPending = $pending('BANK-ALLOC-B', $allocations);
$report['concurrent_allocation'] = $concurrent([fn () => $approve($firstPending), fn () => $approve($secondPending)]);
$report['concurrent_allocation_state'] = [
    DB::table('student_bills')->where('id', 1)->value('balance'),
    DB::table('student_bills')->where('id', 1)->value('amount_paid'),
    DB::table('bill_payments')->count(), DB::table('school_vault_transactions')->count(),
    DB::table('bill_payment_allocations')->sum('amount'),
];

echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;

$terminal = $report['approval_vs_rejection_state'][0];
$passed = $report['simultaneous_approval_state'] === ['approved', 1, 1, 1]
    && in_array($terminal, ['approved', 'rejected'], true)
    && ($terminal === 'approved'
        ? $report['approval_vs_rejection_state'] === ['approved', 1, 1]
        : $report['approval_vs_rejection_state'] === ['rejected', 0, 0])
    && $report['concurrent_callbacks_state'] === [1, 1, 1]
    && $report['callback_vs_verification_state'] === [1, 1, 1]
    && $report['retry_after_posting_failure'] === [[422, 'pending', 0, 0], 200, 1, 1]
    && (string) $report['concurrent_allocation_state'][0] === '40.00'
    && (string) $report['concurrent_allocation_state'][1] === '60.00'
    && $report['concurrent_allocation_state'][2] === 1
    && $report['concurrent_allocation_state'][3] === 1
    && (string) $report['concurrent_allocation_state'][4] === '60.00';

exit($passed ? 0 : 1);
