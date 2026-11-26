<?php

use App\Models\BillingItem;
use App\Models\BillPayment;
use App\Models\Expense\Expense;
use App\Models\SchoolVault;
use App\Services\Finance\ExpenseVaultService;
use App\Services\Finance\SchoolVaultService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

if (DB::connection()->getDriverName() !== 'mysql' || DB::connection()->getDatabaseName() !== 'new_easyschool_task009_test') {
    fwrite(STDERR, "Refusing to run outside the dedicated Task 009 MySQL database.\n");
    exit(2);
}

foreach (['school_vault_transactions', 'expense_vault_postings', 'expenses', 'expense_categories', 'bill_payment_allocations', 'student_bills', 'bill_payments', 'school_vaults'] as $table) {
    Schema::dropIfExists($table);
}

Schema::create('school_vaults', function (Blueprint $table) {
    $table->id();
    $table->string('account_code')->unique();
    $table->string('name');
    $table->char('currency', 3);
    $table->string('status');
    $table->timestamps();
});
Schema::create('bill_payments', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('student_id');
    $table->string('financial_category');
    $table->string('receipt_no')->unique();
    $table->decimal('amount', 12, 2);
    $table->decimal('credit_applied', 12, 2)->default(0);
    $table->decimal('credit_generated', 12, 2)->default(0);
    $table->string('payment_method');
    $table->string('reference')->nullable();
    $table->string('payment_channel')->nullable();
    $table->string('gateway_transaction_id')->nullable();
    $table->dateTime('paid_at');
    $table->text('notes')->nullable();
    $table->unsignedBigInteger('created_by')->nullable();
    $table->unsignedBigInteger('updated_by')->nullable();
    $table->timestamps();
});
Schema::create('student_bills', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('student_id');
    $table->string('financial_category');
    $table->decimal('amount_due', 12, 2);
    $table->decimal('amount_paid', 12, 2);
    $table->decimal('balance', 12, 2);
    $table->timestamps();
});
Schema::create('bill_payment_allocations', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('bill_payment_id');
    $table->unsignedBigInteger('student_bill_id');
    $table->decimal('amount', 12, 2);
    $table->timestamps();
});
Schema::create('expense_categories', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('status');
    $table->timestamps();
});
Schema::create('expenses', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('expense_category_id');
    $table->date('expense_date');
    $table->decimal('amount', 12, 2);
    $table->string('payee');
    $table->string('payment_method');
    $table->string('reference')->nullable();
    $table->text('notes')->nullable();
    $table->unsignedBigInteger('academic_year_id')->nullable();
    $table->string('status');
    $table->timestamp('cancelled_at')->nullable();
    $table->unsignedBigInteger('cancelled_by')->nullable();
    $table->text('cancellation_reason')->nullable();
    $table->unsignedBigInteger('created_by')->nullable();
    $table->unsignedBigInteger('updated_by')->nullable();
    $table->timestamps();
});
Schema::create('expense_vault_postings', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('expense_id');
    $table->unsignedInteger('version');
    $table->decimal('amount', 12, 2);
    $table->date('expense_date');
    $table->unsignedBigInteger('expense_category_id');
    $table->string('payee');
    $table->string('payment_method');
    $table->string('reference')->nullable();
    $table->unsignedBigInteger('academic_year_id')->nullable();
    $table->string('status');
    $table->uuid('request_token')->unique();
    $table->text('change_reason')->nullable();
    $table->timestamp('ended_at')->nullable();
    $table->unsignedBigInteger('ended_by')->nullable();
    $table->unsignedBigInteger('created_by')->nullable();
    $table->timestamp('created_at')->nullable();
    $table->unique(['expense_id', 'version']);
});
Schema::create('school_vault_transactions', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('school_vault_id');
    $table->string('transaction_number')->unique();
    $table->string('direction');
    $table->string('transaction_type');
    $table->decimal('amount', 12, 2);
    $table->string('source_type');
    $table->unsignedBigInteger('source_id');
    $table->string('description');
    $table->dateTime('transaction_date');
    $table->unsignedBigInteger('recorded_by')->nullable();
    $table->unsignedBigInteger('reversal_of_id')->nullable()->unique();
    $table->timestamp('created_at')->nullable();
    $table->unique(['school_vault_id', 'transaction_type', 'source_type', 'source_id'], 'vault_source_unique');
});

$reset = function (string $credit = '100.00'): void {
    foreach (['school_vault_transactions', 'expense_vault_postings', 'expenses', 'bill_payment_allocations', 'student_bills', 'bill_payments'] as $table) {
        DB::table($table)->delete();
    }
    DB::table('school_vaults')->delete();
    DB::table('expense_categories')->delete();
    DB::table('school_vaults')->insert(['id' => 1, 'account_code' => SchoolVault::ACCOUNT_CODE, 'name' => 'School Vault', 'currency' => 'GHS', 'status' => 'Active', 'created_at' => now(), 'updated_at' => now()]);
    DB::table('expense_categories')->insert(['id' => 1, 'name' => 'Operations', 'status' => 'Active', 'created_at' => now(), 'updated_at' => now()]);
    if ($credit !== '0.00') {
        DB::table('school_vault_transactions')->insert([
            'school_vault_id' => 1, 'transaction_number' => 'SEED-'.bin2hex(random_bytes(4)), 'direction' => 'credit',
            'transaction_type' => 'authorized_adjustment', 'amount' => $credit, 'source_type' => SchoolVault::class,
            'source_id' => 1, 'description' => 'Isolated concurrency seed', 'transaction_date' => now(), 'created_at' => now(),
        ]);
    }
};

$expenseData = fn (string $amount, string $reference) => [
    'expense_category_id' => 1, 'expense_date' => now()->toDateString(), 'amount' => $amount,
    'payee' => 'Concurrency fixture', 'payment_method' => 'Cash', 'reference' => $reference,
    'notes' => 'Isolated Task 009 test', 'academic_year_id' => null,
];

$concurrent = function (array $jobs): array {
    $barrier = sys_get_temp_dir().'/task009-barrier-'.bin2hex(random_bytes(5));
    $resultPrefix = $barrier.'-result-';
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
                $result = ['ok' => true, 'value' => $job()];
            } catch (Throwable $e) {
                $result = ['ok' => false, 'error' => get_class($e), 'message' => $e->getMessage()];
            }
            file_put_contents($resultPrefix.$index, json_encode($result));
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
    $results = [];
    foreach (array_keys($jobs) as $index) {
        $results[] = json_decode(file_get_contents($resultPrefix.$index), true);
        unlink($resultPrefix.$index);
    }
    unlink($barrier);

    return $results;
};

$report = [];
$reset('100.00');
$report['competing_expenses'] = $concurrent([
    fn () => app(ExpenseVaultService::class)->create($expenseData('80.00', 'COMPETE-A'), 1, '10000000-0000-4000-8000-000000000001')->id,
    fn () => app(ExpenseVaultService::class)->create($expenseData('80.00', 'COMPETE-B'), 1, '10000000-0000-4000-8000-000000000002')->id,
]);
$report['competing_expenses_balance'] = app(SchoolVaultService::class)->availableBalance();

$reset('100.00');
$token = '20000000-0000-4000-8000-000000000001';
$report['duplicate_expense'] = $concurrent([
    fn () => app(ExpenseVaultService::class)->create($expenseData('25.00', 'DUPLICATE'), 1, $token)->id,
    fn () => app(ExpenseVaultService::class)->create($expenseData('25.00', 'DUPLICATE'), 1, $token)->id,
]);
$report['duplicate_expense_rows'] = [DB::table('expenses')->count(), DB::table('expense_vault_postings')->count()];

$reset('0.00');
DB::table('student_bills')->insert(['id' => 1, 'student_id' => 1, 'financial_category' => BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE, 'amount_due' => 40, 'amount_paid' => 40, 'balance' => 0, 'created_at' => now(), 'updated_at' => now()]);
DB::table('bill_payments')->insert(['id' => 1, 'student_id' => 1, 'financial_category' => BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE, 'receipt_no' => 'CONCURRENT-RCP', 'amount' => 40, 'payment_method' => 'Cash', 'paid_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
DB::table('bill_payment_allocations')->insert(['bill_payment_id' => 1, 'student_bill_id' => 1, 'amount' => 40, 'created_at' => now(), 'updated_at' => now()]);
$postCollection = fn () => app(SchoolVaultService::class)->postCredit('feeding_fee_collection', '40.00', BillPayment::class, 1, 'Feeding Fee collection CONCURRENT-RCP', now(), null)->id;
$report['duplicate_collection'] = $concurrent([$postCollection, $postCollection]);
$report['duplicate_collection_rows'] = DB::table('school_vault_transactions')->count();
$report['duplicate_collection_balance'] = app(SchoolVaultService::class)->availableBalance();

$reset('100.00');
$correctable = app(ExpenseVaultService::class)->create(
    $expenseData('40.00', 'CORRECT-CANCEL'),
    1,
    '30000000-0000-4000-8000-000000000001'
);
$report['correction_vs_cancellation'] = $concurrent([
    fn () => app(ExpenseVaultService::class)->update(
        Expense::findOrFail($correctable->id),
        $expenseData('30.00', 'CORRECT-CANCEL'),
        1,
        '30000000-0000-4000-8000-000000000002',
        'Concurrent correction fixture.'
    )->id,
    fn () => app(ExpenseVaultService::class)->cancel(
        Expense::findOrFail($correctable->id),
        'Concurrent cancellation fixture.',
        1
    )->id,
]);
$report['correction_vs_cancellation_state'] = [
    Expense::findOrFail($correctable->id)->status,
    app(SchoolVaultService::class)->availableBalance(),
];

$reset('0.00');
DB::table('student_bills')->insert(['id' => 2, 'student_id' => 2, 'financial_category' => BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE, 'amount_due' => 100, 'amount_paid' => 100, 'balance' => 0, 'created_at' => now(), 'updated_at' => now()]);
DB::table('bill_payments')->insert(['id' => 2, 'student_id' => 2, 'financial_category' => BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE, 'receipt_no' => 'SIMULTANEOUS-RCP', 'amount' => 100, 'payment_method' => 'Cash', 'paid_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
DB::table('bill_payment_allocations')->insert(['bill_payment_id' => 2, 'student_bill_id' => 2, 'amount' => 100, 'created_at' => now(), 'updated_at' => now()]);
$report['collection_vs_expense'] = $concurrent([
    fn () => app(SchoolVaultService::class)->postCredit('feeding_fee_collection', '100.00', BillPayment::class, 2, 'Feeding Fee collection SIMULTANEOUS-RCP', now(), null)->id,
    fn () => app(ExpenseVaultService::class)->create($expenseData('80.00', 'SIMULTANEOUS-EXPENSE'), 1, '40000000-0000-4000-8000-000000000001')->id,
]);
$report['collection_vs_expense_balance'] = app(SchoolVaultService::class)->availableBalance();

echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;

$expenseSuccesses = count(array_filter($report['competing_expenses'], fn ($r) => $r['ok']));
$duplicateExpenseIds = array_unique(array_column($report['duplicate_expense'], 'value'));
$duplicateCollectionIds = array_unique(array_column($report['duplicate_collection'], 'value'));
$passed = $expenseSuccesses === 1
    && $report['competing_expenses_balance'] === '20.00'
    && count($duplicateExpenseIds) === 1
    && $report['duplicate_expense_rows'] === [1, 1]
    && count($duplicateCollectionIds) === 1
    && $report['duplicate_collection_rows'] === 1
    && $report['duplicate_collection_balance'] === '40.00'
    && $report['correction_vs_cancellation_state'] === [Expense::STATUS_CANCELLED, '100.00']
    && in_array($report['collection_vs_expense_balance'], ['20.00', '100.00'], true);

exit($passed ? 0 : 1);
