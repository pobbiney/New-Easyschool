<?php

namespace App\Http\Controllers\Expense;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Expense\Expense;
use App\Models\Expense\ExpenseCategory;
use App\Support\AcademicPeriodDefaults;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
            'expense_category_id' => $request->input('expense_category_id'),
            'payment_method' => $request->input('payment_method'),
        ];

        $query = $this->filteredQuery($filters);

        $expenses = (clone $query)
            ->with(['category', 'recorder', 'academicYear'])
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $filteredTotal = (clone $query)->sum('amount');
        $filteredCount = (clone $query)->count();

        $totals = (clone $query)
            ->select('expense_category_id')
            ->selectRaw('SUM(amount) as total')
            ->groupBy('expense_category_id')
            ->get();

        $categoryNames = ExpenseCategory::query()
            ->whereIn('id', $totals->pluck('expense_category_id'))
            ->pluck('name', 'id');

        $categoryTotals = $totals
            ->map(fn ($row) => [
                'name' => $categoryNames[$row->expense_category_id] ?? 'Uncategorised',
                'total' => (float) $row->total,
            ])
            ->sortByDesc('total')
            ->values();

        $today = now()->toDateString();

        return view('expenses.index', [
            'expenses' => $expenses,
            'filters' => $filters,
            'categories' => ExpenseCategory::orderBy('name')->get(),
            'activeCategories' => ExpenseCategory::active()->orderBy('name')->get(),
            'paymentMethods' => Expense::PAYMENT_METHODS,
            'academicYears' => AcademicYear::where('status', 'Active')->orderByDesc('id')->get(),
            'defaultYearId' => AcademicPeriodDefaults::yearId(),
            'filteredTotal' => $filteredTotal,
            'filteredCount' => $filteredCount,
            'categoryTotals' => $categoryTotals,
            'stats' => [
                'today' => (float) Expense::whereDate('expense_date', $today)->sum('amount'),
                'month' => (float) Expense::where('expense_date', '>=', now()->copy()->startOfMonth()->toDateString())->sum('amount'),
                'year' => (float) Expense::where('expense_date', '>=', now()->copy()->startOfYear()->toDateString())->sum('amount'),
                'records' => $filteredCount,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $category = ExpenseCategory::findOrFail($data['expense_category_id']);
        if ($category->status !== 'Active') {
            return back()->withInput()->with('message_error', 'Choose an active expense category.');
        }

        Expense::create([
            ...$data,
            'academic_year_id' => $data['academic_year_id'] ?? AcademicPeriodDefaults::yearId(),
            'created_by' => Auth::id(),
        ]);

        return back()->with('message_success', 'Expense recorded successfully.');
    }

    public function show($id)
    {
        $expense = Expense::with('category')->findOrFail($id);

        return response()->json([
            'id' => $expense->id,
            'expense_category_id' => $expense->expense_category_id,
            'expense_date' => $expense->expense_date?->format('Y-m-d'),
            'amount' => $expense->amount,
            'payee' => $expense->payee,
            'payment_method' => $expense->payment_method,
            'reference' => $expense->reference,
            'notes' => $expense->notes,
            'academic_year_id' => $expense->academic_year_id,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'expense_id' => 'required|exists:expenses,id',
        ]);

        $data = $this->validated($request);
        $expense = Expense::findOrFail($request->input('expense_id'));

        $category = ExpenseCategory::findOrFail($data['expense_category_id']);
        if ($category->status !== 'Active' && (int) $category->id !== (int) $expense->expense_category_id) {
            return back()->withInput()->with('message_error', 'Choose an active expense category.');
        }

        $expense->update([
            ...$data,
            'updated_by' => Auth::id(),
        ]);

        return back()->with('message_success', 'Expense updated successfully.');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'expense_id' => 'required|exists:expenses,id',
        ]);

        Expense::findOrFail($request->input('expense_id'))->delete();

        return back()->with('message_success', 'Expense deleted.');
    }

    /**
     * @param  array{from_date:?string,to_date:?string,expense_category_id:?string,payment_method:?string}  $filters
     */
    private function filteredQuery(array $filters)
    {
        return Expense::query()
            ->when($filters['from_date'], fn ($query, $date) => $query->whereDate('expense_date', '>=', $date))
            ->when($filters['to_date'], fn ($query, $date) => $query->whereDate('expense_date', '<=', $date))
            ->when($filters['expense_category_id'], fn ($query, $id) => $query->where('expense_category_id', $id))
            ->when($filters['payment_method'], fn ($query, $method) => $query->where('payment_method', $method));
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'expense_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payee' => 'required|string|max:150',
            'payment_method' => 'required|in:'.implode(',', Expense::PAYMENT_METHODS),
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'academic_year_id' => 'nullable|exists:academic_years,id',
        ]);

        $data['payee'] = trim($data['payee']);
        $data['reference'] = trim($data['reference'] ?? '') ?: null;
        $data['notes'] = trim($data['notes'] ?? '') ?: null;
        $data['academic_year_id'] = $data['academic_year_id'] ?? null;

        return $data;
    }
}
