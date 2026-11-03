<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\SchoolSetting;
use App\Services\ParentPortal\ParentPasswordResetService;
use App\Services\ParentPortal\ParentStudentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class ParentPasswordController extends Controller
{
    public function __construct(
        private ParentPasswordResetService $resets,
        private ParentStudentService $parentStudentService,
    ) {}

    public function showForgot()
    {
        if (auth('parent')->check()) {
            return redirect()->route('parent.dashboard');
        }

        return view('parent.auth.forgot-password', [
            'school' => SchoolSetting::current(),
        ]);
    }

    public function sendCode(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string|max:30',
        ]);

        $phone = $this->resets->normalizePhone($validated['phone']);

        if (! $phone) {
            return back()
                ->withInput()
                ->with('login_error_message', 'Enter a valid phone number.');
        }

        try {
            $this->resets->sendCode($phone);
        } catch (RuntimeException $e) {
            return back()
                ->withInput()
                ->with('login_error_message', $e->getMessage());
        }

        $request->session()->put('parent_reset_phone', $phone);

        return redirect()
            ->route('parent.reset-password')
            ->with('message_success', 'If this number is registered, we sent a 6-digit code by SMS.');
    }

    public function showReset(Request $request)
    {
        if (auth('parent')->check()) {
            return redirect()->route('parent.dashboard');
        }

        $phone = $request->session()->get('parent_reset_phone');

        if (! $phone) {
            return redirect()
                ->route('parent.forgot-password')
                ->with('login_error_message', 'Enter your phone number to receive a reset code.');
        }

        return view('parent.auth.reset-password', [
            'school' => SchoolSetting::current(),
            'phone' => $phone,
            'maskedPhone' => $this->resets->maskedPhone($phone),
        ]);
    }

    public function resendCode(Request $request)
    {
        $phone = $request->session()->get('parent_reset_phone');

        if (! $phone) {
            return redirect()->route('parent.forgot-password');
        }

        try {
            $this->resets->sendCode($phone);
        } catch (RuntimeException $e) {
            return back()->with('login_error_message', $e->getMessage());
        }

        return back()->with('message_success', 'If this number is registered, we sent a new code by SMS.');
    }

    public function reset(Request $request)
    {
        $phone = $request->session()->get('parent_reset_phone');

        if (! $phone) {
            return redirect()
                ->route('parent.forgot-password')
                ->with('login_error_message', 'Enter your phone number to receive a reset code.');
        }

        $validated = $request->validate([
            'otp' => 'required|digits:6',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'otp.digits' => 'Enter the 6-digit code from your SMS.',
            'password.min' => 'Choose a password with at least 8 characters.',
            'password.confirmed' => 'The new password confirmation does not match.',
        ]);

        $otp = $validated['otp'];

        try {
            $this->resets->resetPassword($phone, $otp, $validated['password']);
        } catch (RuntimeException $e) {
            return back()->with('login_error_message', $e->getMessage());
        }

        $request->session()->forget('parent_reset_phone');

        return redirect()
            ->route('parent.login')
            ->with('message_success', 'Password updated. Sign in with your new password.');
    }

    public function showChange()
    {
        $parent = Auth::guard('parent')->user();

        return view('parent.password', [
            'parent' => $parent,
            'children' => $this->parentStudentService->childrenFor($parent),
            'school' => SchoolSetting::current(),
        ]);
    }

    public function change(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.min' => 'Choose a password with at least 8 characters.',
            'password.confirmed' => 'The new password confirmation does not match.',
        ]);

        $parent = Auth::guard('parent')->user();

        if (! Hash::check($validated['current_password'], $parent->password)) {
            return back()->with('message_error', 'Your current password is incorrect.');
        }

        $parent->update([
            'password' => $validated['password'],
            'must_change_password' => false,
        ]);

        return back()->with('message_success', 'Your password has been updated.');
    }
}
