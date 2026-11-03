<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\BillingItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillingItemController extends Controller
{
    public function index()
    {
        $items = BillingItem::orderBy('name')->get();

        return view('billing.billing-items', [
            'items' => $items,
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
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'status' => 'required|in:Active,Inactive',
            'is_compulsory' => 'required|boolean',
        ]);

        if (BillingItem::where('name', trim($request->name))->exists()) {
            return back()->with('message_error', 'This billing item already exists.');
        }

        BillingItem::create([
            'name' => trim($request->name),
            'description' => trim($request->description ?? '') ?: null,
            'status' => trim($request->status),
            'is_compulsory' => $request->boolean('is_compulsory'),
            'created_by' => Auth::id(),
        ]);

        return back()->with('message_success', 'Billing item added successfully.');
    }

    public function show($id)
    {
        return response()->json(BillingItem::findOrFail($id));
    }

    public function update(Request $request)
    {
        $request->validate([
            'billing_item_id' => 'required|exists:billing_items,id',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'status' => 'required|in:Active,Inactive',
            'is_compulsory' => 'required|boolean',
        ]);

        $item = BillingItem::findOrFail($request->billing_item_id);

        if (BillingItem::where('name', trim($request->name))->where('id', '!=', $item->id)->exists()) {
            return back()->with('message_error', 'This billing item already exists.');
        }

        $item->update([
            'name' => trim($request->name),
            'description' => trim($request->description ?? '') ?: null,
            'status' => trim($request->status),
            'is_compulsory' => $request->boolean('is_compulsory'),
            'updated_by' => Auth::id(),
        ]);

        return back()->with('message_success', 'Billing item updated successfully.');
    }
}
