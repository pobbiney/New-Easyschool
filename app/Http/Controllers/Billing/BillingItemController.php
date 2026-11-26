<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\BillingItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\JsonResponse;

class BillingItemController extends Controller
{
    public function index()
    {
        $items = BillingItem::orderBy('name')->get();

        return view('billing.billing-items', [
            'items' => $items,
            'financialCategories' => BillingItem::financialCategoryOptions(),
            'stats' => [
                'total' => $items->count(),
                'active' => $items->where('status', 'Active')->count(),
                'inactive' => $items->where('status', 'Inactive')->count(),
                'compulsory' => $items->where('is_compulsory', true)->count(),
                'optional' => $items->where('is_compulsory', false)->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateItem($request);

        if (BillingItem::where('name', trim($request->name))->exists()) {
            if ($request->expectsJson()) {
                return new JsonResponse(['message' => 'This billing item already exists.'], 422);
            }

            return back()->with('message_error', 'This billing item already exists.');
        }

        BillingItem::create([
            'name' => trim($validated['name']),
            'description' => trim($validated['description'] ?? '') ?: null,
            'status' => $validated['status'],
            'is_compulsory' => (bool) $validated['is_compulsory'],
            'financial_category' => $validated['financial_category'],
            'created_by' => Auth::id(),
        ]);

        if ($request->expectsJson()) {
            return new JsonResponse(['message' => 'Billing item added successfully.'], 201);
        }

        return back()->with('message_success', 'Billing item added successfully.');
    }

    public function show($id)
    {
        return response()->json(BillingItem::findOrFail($id));
    }

    public function update(Request $request)
    {
        $validated = $this->validateItem($request, [
            'billing_item_id' => 'required|exists:billing_items,id',
        ]);

        $item = BillingItem::findOrFail($request->billing_item_id);

        if (BillingItem::where('name', trim($request->name))->where('id', '!=', $item->id)->exists()) {
            if ($request->expectsJson()) {
                return new JsonResponse(['message' => 'This billing item already exists.'], 422);
            }

            return back()->with('message_error', 'This billing item already exists.');
        }

        if (
            $validated['financial_category'] !== $item->financial_category
            && $item->studentBills()->exists()
        ) {
            if ($request->expectsJson()) {
                return new JsonResponse([
                    'message' => 'The financial category cannot be changed because this item already has student bills or payment history.',
                ], 422);
            }

            return back()->with(
                'message_error',
                'The financial category cannot be changed because this item already has student bills or payment history.'
            );
        }

        $item->update([
            'name' => trim($validated['name']),
            'description' => trim($validated['description'] ?? '') ?: null,
            'status' => $validated['status'],
            'is_compulsory' => (bool) $validated['is_compulsory'],
            'financial_category' => $validated['financial_category'],
            'updated_by' => Auth::id(),
        ]);

        if ($request->expectsJson()) {
            return new JsonResponse(['message' => 'Billing item updated successfully.']);
        }

        return back()->with('message_success', 'Billing item updated successfully.');
    }

    private function validateItem(Request $request, array $additionalRules = []): array
    {
        return $request->validate(array_merge([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'status' => 'required|in:Active,Inactive',
            'financial_category' => [
                'required',
                Rule::in(array_keys(BillingItem::financialCategoryOptions())),
            ],
            'is_compulsory' => [
                'required',
                'boolean',
                function (string $attribute, mixed $value, \Closure $fail) use ($request) {
                    if (
                        $request->input('financial_category') === BillingItem::FINANCIAL_CATEGORY_FEEDING_FEE
                        && filter_var($value, FILTER_VALIDATE_BOOLEAN)
                    ) {
                        $fail('Feeding Fee must be configured as an optional billing item.');
                    }
                },
            ],
        ], $additionalRules));
    }
}
