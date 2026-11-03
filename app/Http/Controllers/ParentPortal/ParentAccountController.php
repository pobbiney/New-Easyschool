<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\SchoolSetting;
use App\Services\ParentPortal\ParentAccountService;
use App\Services\ParentPortal\ParentStudentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ParentAccountController extends Controller
{
    public function __construct(
        private ParentStudentService $parentStudentService,
        private ParentAccountService $parentAccountService,
    ) {}

    public function show()
    {
        $parent = Auth::guard('parent')->user();

        return view('parent.account', [
            'parent' => $parent,
            'children' => $this->parentStudentService->childrenFor($parent),
            'school' => SchoolSetting::current(),
        ]);
    }

    public function updatePassword(Request $request)
    {
        $parent = Auth::guard('parent')->user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => ['required', 'string', 'confirmed', Password::min(8)],
        ], [
            'new_password.confirmed' => 'The new password confirmation does not match.',
        ]);

        if (! Hash::check($validated['current_password'], $parent->password)) {
            return back()->with('message_error', 'Current password is incorrect.');
        }

        $parent->update([
            'password' => $validated['new_password'],
            'must_change_password' => false,
        ]);

        return back()->with('message_success', 'Your password has been updated successfully.');
    }
}
