<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\ParentPortal\ParentAccount;
use App\Models\SchoolSetting;
use App\Services\MNotifyService;
use App\Services\ParentPortal\ParentAccountService;
use App\Services\ParentPortal\ParentCommunicationLogService;
use App\Support\GhanaPhone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ParentAuthController extends Controller
{
    public function __construct(private ParentAccountService $parentAccountService) {}

    public function showLogin()
    {
        if (auth('parent')->check()) {
            return redirect()->route('parent.dashboard');
        }

        return view('parent.auth.login', [
            'school' => SchoolSetting::current(),
        ]);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string|max:30',
            'password' => 'required|string',
        ]);

        $phone = GhanaPhone::normalize($validated['phone']);

        if (! $phone) {
            return back()
                ->withInput($request->only('phone'))
                ->with('login_error_message', 'Enter a valid phone number.');
        }

        $parent = ParentAccount::query()->where('phone', $phone)->first();

        if (! $parent || ! $parent->isActive()) {
            return back()
                ->withInput($request->only('phone'))
                ->with('login_error_message', 'Phone number or password is incorrect.');
        }

        if (! Hash::check($validated['password'], $parent->password)) {
            return back()
                ->withInput($request->only('phone'))
                ->with('login_error_message', 'Phone number or password is incorrect.');
        }

        Auth::guard('parent')->login($parent, $request->boolean('remember'));
        $parent->update(['last_login_at' => now()]);

        if ($parent->must_change_password) {
            return redirect()
                ->route('parent.account')
                ->with('message_success', 'Welcome back. Please set a new password for your account.');
        }

        return redirect()->intended(route('parent.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::guard('parent')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('parent.login')->with('message_success', 'Signed out successfully.');
    }

    public function showForgotPassword()
    {
        if (auth('parent')->check()) {
            return redirect()->route('parent.account');
        }

        return view('parent.auth.forgot-password', [
            'school' => SchoolSetting::current(),
        ]);
    }

    public function processForgotPassword(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string|max:30',
        ]);

        $phone = GhanaPhone::normalize($validated['phone']);

        if (! $phone) {
            return back()
                ->withInput($request->only('phone'))
                ->with('message_error', 'Enter a valid phone number.');
        }

        $account = $this->parentAccountService->resetToDefault($phone);

        if (! $account) {
            return back()
                ->withInput($request->only('phone'))
                ->with('message_error', 'No parent account was found for this phone number.');
        }

        $defaultPassword = (string) config('parent.default_password', 'Parent123');
        $school = SchoolSetting::current();
        $schoolName = $school->name ?? $school->school_name ?? 'EasySchool';
        $smsMessage = "Your {$schoolName} parent portal password was reset. Temporary password: {$defaultPassword}. Please sign in and change it.";

        $mnotify = app(MNotifyService::class);
        if ($mnotify->isConfigured()) {
            try {
                $mnotify->sendQuickSms([$account->phone], $smsMessage);
                app(ParentCommunicationLogService::class)->logForPhone(
                    $account->phone,
                    null,
                    'sms',
                    $smsMessage,
                );
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return redirect()
            ->route('parent.login')
            ->withInput(['phone' => $validated['phone']])
            ->with('message_success', "Password reset. Sign in with your temporary password ({$defaultPassword}), then change it under Account settings.");
    }
}
