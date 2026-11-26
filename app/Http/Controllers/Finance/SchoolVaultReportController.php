<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\SchoolSetting;
use App\Models\VaultCashReconciliation;
use App\Services\Finance\SchoolVaultReportingService;
use App\Services\Finance\SchoolVaultService;
use App\Services\Reports\ReportExcelExporter;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SchoolVaultReportController extends Controller
{
    public function __construct(
        private SchoolVaultReportingService $reports,
        private SchoolVaultService $vaults,
        private ReportExcelExporter $excel,
    ) {}

    public function collections(Request $request)
    {
        $this->validateCollections($request);
        return view('finance.school-vault-collections', $this->reports->collections($request));
    }

    public function transactions(Request $request)
    {
        $this->validateTransactions($request);
        return view('finance.school-vault-transactions', $this->reports->transactions($request));
    }

    public function reconciliation(Request $request)
    {
        $request->validate(['date' => ['nullable', 'date']]);
        return view('finance.school-vault-reconciliation', $this->reports->reconciliation($request));
    }

    public function storeReconciliation(Request $request)
    {
        $validated = $request->validate([
            'reconciliation_date' => ['required', 'date', 'before_or_equal:today'],
            'actual_cash_counted' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $vault = $this->vaults->vault();

        if (VaultCashReconciliation::query()->where('school_vault_id', $vault->id)->whereDate('reconciliation_date', $validated['reconciliation_date'])->exists()) {
            throw ValidationException::withMessages(['reconciliation_date' => 'A completed cash count already exists for this date and cannot be overwritten.']);
        }

        $daily = $this->reports->dailyMovements(Carbon::parse($validated['reconciliation_date'])->startOfDay());
        DB::transaction(fn () => VaultCashReconciliation::create([
            'school_vault_id' => $vault->id,
            'reconciliation_date' => $validated['reconciliation_date'],
            'cash_collections' => $daily['cash_collections'],
            'cash_expenditure' => $daily['cash_expenditure'],
            'actual_cash_counted' => $validated['actual_cash_counted'],
            'notes' => $validated['notes'] ?? null,
            'recorded_by' => auth()->id(),
            'reconciled_at' => now(),
        ]));

        return redirect()->route('school-vault-reconciliation', ['date' => $validated['reconciliation_date']])
            ->with('success', 'Daily cash count recorded. The Vault ledger was not changed.');
    }

    public function statement(Request $request)
    {
        $this->validateStatement($request);
        return view('finance.school-vault-statement', $this->reports->statement($request) + ['school' => SchoolSetting::current()]);
    }

    public function print(Request $request, string $report)
    {
        return view('reports.print', ['report' => $this->exportReport($request, $report), 'school' => SchoolSetting::current(), 'title' => 'School Vault report']);
    }

    public function pdf(Request $request, string $report)
    {
        $data = $this->exportReport($request, $report);
        return Pdf::loadView('reports.pdf', ['report' => $data, 'school' => SchoolSetting::current(), 'title' => $data['title']])
            ->setPaper('a4', 'landscape')->download($data['url'].'-'.now()->format('Ymd-His').'.pdf');
    }

    public function excel(Request $request, string $report)
    {
        return $this->excel->download($this->exportReport($request, $report));
    }

    /** @return array<string,mixed> */
    private function exportReport(Request $request, string $report): array
    {
        abort_unless(in_array($report, ['collections', 'transactions', 'reconciliation', 'statement'], true), 404);

        if ($report === 'collections') {
            $this->validateCollections($request);
            $data = $this->reports->collections($request, false);
            return $this->pack('school-vault-collections', 'Feeding Fee Collections',
                ($data['academicPeriod']['year_name'] ?: 'All years').' · '.($data['academicPeriod']['term_name'] ?: 'All terms'),
                [['key'=>'student_id','label'=>'Student ID'],['key'=>'student_name','label'=>'Student'],['key'=>'class','label'=>'Class'],['key'=>'academic_period','label'=>'Academic period'],['key'=>'billed_display','label'=>'Billed'],['key'=>'paid_display','label'=>'Paid'],['key'=>'outstanding_display','label'=>'Outstanding'],['key'=>'status','label'=>'Status']],
                $data['students']->map(fn ($row) => $row + ['billed_display'=>'GHS '.number_format((float)$row['billed'],2),'paid_display'=>'GHS '.number_format((float)$row['paid'],2),'outstanding_display'=>'GHS '.number_format((float)$row['outstanding'],2)])->all(),
                [['label'=>'Billed','value'=>'GHS '.number_format((float)$data['summary']['billed'],2)],['label'=>'Confirmed paid','value'=>'GHS '.number_format((float)$data['summary']['confirmed'],2)],['label'=>'Posted to Vault','value'=>'GHS '.number_format((float)$data['summary']['posted'],2)],['label'=>'Outstanding optional bills','value'=>'GHS '.number_format((float)$data['summary']['outstanding'],2)]]);
        }

        if ($report === 'transactions') {
            $this->validateTransactions($request);
            $data = $this->reports->transactions($request, false);
            return $this->pack('school-vault-transactions', 'School Vault Transaction History', 'Authoritative ledger ordered by transaction date, then transaction ID.',
                [['key'=>'reference','label'=>'Transaction'],['key'=>'date_display','label'=>'Transaction date'],['key'=>'type','label'=>'Type'],['key'=>'description','label'=>'Description'],['key'=>'source_reference','label'=>'Source'],['key'=>'credit_display','label'=>'Credit'],['key'=>'debit_display','label'=>'Debit'],['key'=>'running_display','label'=>'Running balance'],['key'=>'recorded_by','label'=>'Recorded by']],
                $data['transactions']->map(fn ($row) => $row + ['date_display'=>$row['date']->format('d M Y H:i'),'credit_display'=>$row['credit'] ? 'GHS '.number_format((float)$row['credit'],2):'—','debit_display'=>$row['debit'] ? 'GHS '.number_format((float)$row['debit'],2):'—','running_display'=>'GHS '.number_format((float)$row['running_balance'],2)])->all(),
                [['label'=>'Filtered credits','value'=>'GHS '.number_format((float)$data['totals']['credits'],2)],['label'=>'Filtered debits','value'=>'GHS '.number_format((float)$data['totals']['debits'],2)],['label'=>'Current full balance','value'=>'GHS '.number_format((float)$data['currentBalance'],2)]]);
        }

        if ($report === 'reconciliation') {
            $request->validate(['date' => ['nullable','date']]);
            $data = $this->reports->reconciliation($request);
            $labels = $this->reconciliationLabels();
            $rows = collect($labels)->flatMap(function ($label, $key) use ($data) {
                return collect($data['integrity'][$key] ?? [])->map(function ($issue) use ($label) {
                    $issue = (array) $issue;
                    $reference = $issue['receipt_no'] ?? $issue['transaction_number'] ?? $issue['expense_reference'] ?? ('Record #'.($issue['payment_id'] ?? $issue['expense_id'] ?? $issue['source_id'] ?? '—'));
                    $amount = $issue['amount'] ?? $issue['payment_amount'] ?? $issue['expense_amount'] ?? $issue['ledger_amount'] ?? null;
                    return ['issue'=>$label,'reference'=>$reference,'amount_display'=>$amount !== null ? 'GHS '.number_format((float)$amount,2):'—','details'=>collect($issue)->map(fn($v,$k)=>$k.'='.$v)->implode('; ')];
                });
            })->values()->all();
            return $this->pack('school-vault-reconciliation', 'School Vault Reconciliation', 'Ledger integrity findings. No automatic repairs are performed.',
                [['key'=>'issue','label'=>'Issue'],['key'=>'reference','label'=>'Source reference'],['key'=>'amount_display','label'=>'Amount'],['key'=>'details','label'=>'Details']], $rows,
                [['label'=>'Discrepancies','value'=>number_format($data['integrity']['issue_count'])],['label'=>'Cash collections '.$data['date']->format('d M Y'),'value'=>'GHS '.number_format((float)$data['daily']['cash_collections'],2)],['label'=>'Cash expenditure','value'=>'GHS '.number_format((float)$data['daily']['cash_expenditure'],2)]]);
        }

        $this->validateStatement($request);
        $data = $this->reports->statement($request);
        $rows = $data['income']->map(fn ($row) => ['section'=>'Income','item'=>'Feeding Fee · '.$row['label'],'amount_display'=>'GHS '.number_format((float)$row['amount'],2)])
            ->concat($data['expenses']->map(fn ($row) => ['section'=>'Expenditure','item'=>$row['label'],'amount_display'=>'GHS '.number_format((float)$row['amount'],2)]))
            ->concat($data['otherMovements']->map(fn ($row) => ['section'=>'Other ledger movement','item'=>$row['label'],'amount_display'=>'GHS '.number_format((float)$row['amount'],2)]))->all();
        return $this->pack('school-vault-statement', 'School Vault Statement', $data['range']['label'].' · Currency GHS',
            [['key'=>'section','label'=>'Section'],['key'=>'item','label'=>'Account'],['key'=>'amount_display','label'=>'Amount']], $rows,
            [['label'=>'Opening balance','value'=>'GHS '.number_format((float)$data['summary']['opening'],2)],['label'=>'Feeding Fee collections','value'=>'GHS '.number_format((float)$data['summary']['collections'],2)],['label'=>'Net operational expenditure','value'=>'GHS '.number_format((float)$data['summary']['expenses'],2)],['label'=>'Other ledger movements','value'=>'GHS '.number_format((float)$data['summary']['other_movements'],2)],['label'=>'Net movement','value'=>'GHS '.number_format((float)$data['summary']['net_movement'],2)],['label'=>'Closing balance','value'=>'GHS '.number_format((float)$data['summary']['closing'],2)]]);
    }

    private function validateCollections(Request $request): void
    {
        $request->validate([
            'academic_year_id'=>['nullable','integer','exists:academic_years,id'],'academic_term_id'=>['nullable','integer','exists:academic_terms,id'],
            'class_category_id'=>['nullable','integer','exists:class_categories,id'],'school_class_id'=>['nullable','integer','exists:school_classes,id'],
            'student'=>['nullable','string','max:120'],'payment_status'=>['nullable',Rule::in(['Paid','Partial','Pending'])],
            'payment_method'=>['nullable','string','max:40'],'payment_from'=>['nullable','date'],'payment_to'=>['nullable','date','after_or_equal:payment_from'],
        ]);
    }

    private function validateTransactions(Request $request): void
    {
        $request->validate([
            'from_date'=>['nullable','date'],'to_date'=>['nullable','date','after_or_equal:from_date'],
            'transaction_type'=>['nullable',Rule::in(['feeding_fee_collection','school_operational_expense','opening_balance','authorized_adjustment','reversal'])],
            'direction'=>['nullable',Rule::in(['credit','debit'])],'payment_method'=>['nullable','string','max:40'],
            'expense_category_id'=>['nullable','integer','exists:expense_categories,id'],'reference'=>['nullable','string','max:100'],'source_reference'=>['nullable','string','max:100'],
        ]);
    }

    private function validateStatement(Request $request): void
    {
        $request->validate([
            'period'=>['nullable',Rule::in(['today','week','month','term','custom'])],
            'from_date'=>['required_if:period,custom','nullable','date'],'to_date'=>['required_if:period,custom','nullable','date','after_or_equal:from_date'],
            'academic_year_id'=>['nullable','integer','exists:academic_years,id'],'academic_term_id'=>['nullable','integer','exists:academic_terms,id'],
        ]);
    }

    /** @return array<string,string> */
    private function reconciliationLabels(): array
    {
        return [
            'missing_payment_credits'=>'Feeding payments missing Vault credits','orphan_collection_entries'=>'Invalid or orphaned payment sources',
            'amount_mismatches'=>'Payment and ledger amount mismatches','duplicate_sources'=>'Duplicate payment postings','school_fees_links'=>'School Fees incorrectly linked',
            'active_expenses_without_debits'=>'Active expenses missing Vault debits','orphan_expense_debits'=>'Orphaned expense debits',
            'expense_amount_mismatches'=>'Expense and ledger amount mismatches','cancelled_expenses_without_reversals'=>'Cancelled expenses missing reversals',
            'duplicate_expense_postings'=>'Duplicate expense postings','incorrect_expense_net_expenditure'=>'Incorrect expense correction totals',
        ];
    }

    private function pack(string $url, string $title, string $subtitle, array $columns, array $rows, array $totals): array
    {
        return compact('url','title','subtitle','columns','rows','totals') + ['printed_at'=>now()->format('d M Y, g:i A')];
    }
}
