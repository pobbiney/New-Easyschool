<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\BillingItem;
use App\Models\CategoryBillSetup;
use App\Models\CategoryBillSetupItem;
use App\Models\ClassCategory;
use App\Services\Billing\StudentBillSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CategoryBillSetupController extends Controller
{
    public function index()
    {
        $billingItems = BillingItem::where('status', 'Active')->orderBy('name')->get();

        return view('billing.category-bill-setup', [
            'classCategories' => ClassCategory::where('status', 'Active')->orderBy('name')->get(),
            'academicTerms' => AcademicTerm::where('status', 'Active')->orderBy('sort_order')->get(),
            'academicYears' => AcademicYear::where('status', 'Active')->orderBy('name', 'desc')->get(),
            'billingItems' => $billingItems,
            'stats' => [
                'billing_items' => $billingItems->count(),
                'setups' => CategoryBillSetup::count(),
                'categories' => ClassCategory::where('status', 'Active')->count(),
            ],
        ]);
    }

    public function load(Request $request)
    {
        $validated = $request->validate([
            'class_category_id' => 'required|exists:class_categories,id',
            'academic_term_id' => 'required|exists:academic_terms,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        $setup = CategoryBillSetup::query()
            ->where('class_category_id', $validated['class_category_id'])
            ->where('academic_term_id', $validated['academic_term_id'])
            ->where('academic_year_id', $validated['academic_year_id'])
            ->with('items')
            ->first();

        $amounts = $setup
            ? $setup->items->pluck('amount', 'billing_item_id')
            : collect();

        $items = BillingItem::where('status', 'Active')
            ->orderBy('name')
            ->get()
            ->map(function (BillingItem $item) use ($amounts) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'is_compulsory' => $item->is_compulsory,
                    'amount' => $amounts->get($item->id, ''),
                ];
            })
            ->values();

        return response()->json([
            'setup_id' => $setup?->id,
            'items' => $items,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'class_category_id' => 'required|exists:class_categories,id',
            'academic_term_id' => 'required|exists:academic_terms,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'amounts' => 'required|array',
            'amounts.*.billing_item_id' => 'required|exists:billing_items,id',
            'amounts.*.amount' => 'nullable|numeric|min:0',
        ]);

        try {
            $syncStats = DB::transaction(function () use ($validated) {
            $amountsByItem = collect($validated['amounts'])->keyBy('billing_item_id');

            if ($amountsByItem->keys()->count() !== $amountsByItem->keys()->unique()->count()) {
                throw new \InvalidArgumentException('Each billing item can only be added once per category bill setup.');
            }

            $compulsoryItems = BillingItem::query()
                ->whereIn('id', $amountsByItem->keys())
                ->where('is_compulsory', true)
                ->get();

            foreach ($compulsoryItems as $compulsoryItem) {
                $amount = (float) ($amountsByItem->get($compulsoryItem->id)['amount'] ?? 0);

                if ($amount <= 0) {
                    throw new \InvalidArgumentException(
                        'Compulsory billing item "'.$compulsoryItem->name.'" must have an amount greater than zero.'
                    );
                }
            }

            $existingSetup = CategoryBillSetup::query()
                ->where('class_category_id', $validated['class_category_id'])
                ->where('academic_term_id', $validated['academic_term_id'])
                ->where('academic_year_id', $validated['academic_year_id'])
                ->first();

            $setup = $existingSetup ?? new CategoryBillSetup([
                'class_category_id' => $validated['class_category_id'],
                'academic_term_id' => $validated['academic_term_id'],
                'academic_year_id' => $validated['academic_year_id'],
            ]);

            if (! $setup->exists) {
                $setup->created_by = Auth::id();
            }

            $setup->status = 'Active';
            $setup->updated_by = Auth::id();
            $setup->save();

            $savedItemIds = [];

            foreach ($validated['amounts'] as $row) {
                $amount = (float) ($row['amount'] ?? 0);

                if ($amount <= 0) {
                    continue;
                }

                CategoryBillSetupItem::updateOrCreate(
                    [
                        'category_bill_setup_id' => $setup->id,
                        'billing_item_id' => $row['billing_item_id'],
                    ],
                    ['amount' => $amount]
                );

                $savedItemIds[] = $row['billing_item_id'];
            }

            CategoryBillSetupItem::query()
                ->where('category_bill_setup_id', $setup->id)
                ->whereNotIn('billing_item_id', $savedItemIds)
                ->delete();

            return app(StudentBillSyncService::class)->syncForSetup($setup->fresh('items'));
        });
        } catch (\InvalidArgumentException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->with('message_error', $e->getMessage());
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Category bill setup saved and student bills synced.',
                'stats' => $syncStats,
            ]);
        }

        return back()->with('message_success', sprintf(
            'Bill setup saved. %d students matched, %d bills created, %d updated.',
            $syncStats['students_matched'],
            $syncStats['bills_created'],
            $syncStats['bills_updated']
        ));
    }
}
