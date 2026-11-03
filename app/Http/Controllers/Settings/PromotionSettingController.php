<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PromotionSettingController extends Controller
{
    public function index()
    {
        $classes = SchoolClass::query()
            ->with('category')
            ->where('status', 'Active')
            ->orderBy('name')
            ->get();

        return view('settings.promotion-settings', [
            'classes' => $classes,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'promotion_marks' => 'required|array',
            'promotion_marks.*.school_class_id' => 'required|exists:school_classes,id',
            'promotion_marks.*.promotion_minimum_mark' => 'nullable|integer|min:0|max:65535',
        ]);

        foreach ($request->promotion_marks as $row) {
            $mark = $row['promotion_minimum_mark'];

            SchoolClass::query()
                ->where('id', $row['school_class_id'])
                ->update([
                    'promotion_minimum_mark' => $mark !== null && $mark !== '' ? (int) $mark : null,
                    'updated_by' => Auth::id(),
                ]);
        }

        return back()->with('message_success', 'Promotion settings saved successfully.');
    }
}
