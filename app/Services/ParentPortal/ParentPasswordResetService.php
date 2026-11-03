<?php

namespace App\Services\ParentPortal;

use App\Models\ParentPortal\ParentAccount;
use App\Models\ParentPortal\ParentPasswordReset;
use App\Models\SchoolSetting;
use App\Services\MNotifyService;
use App\Support\GhanaPhone;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use RuntimeException;

class ParentPasswordResetService
{
    public function __construct(private MNotifyService $mnotify) {}

    public function normalizePhone(?string $phone): ?string
    {
        return GhanaPhone::normalize($phone);
    }

    public function maskedPhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone) ?: $phone;
        $len = strlen($digits);

        if ($len < 7) {
            return $phone;
        }

        return substr($digits, 0, 3).str_repeat('*', max(0, $len - 6)).substr($digits, -3);
    }

    public function sendCode(string $phone): void
    {
        $sendKey = 'parent-reset-send:'.$phone;
        $hourKey = 'parent-reset-hour:'.$phone;

        if (RateLimiter::tooManyAttempts($hourKey, 5)) {
            throw new RuntimeException('Too many reset requests. Try again later or contact the school.');
        }

        if (RateLimiter::tooManyAttempts($sendKey, 1)) {
            $seconds = RateLimiter::availableIn($sendKey);

            throw new RuntimeException('Please wait '.$seconds.' second(s) before requesting another code.');
        }

        if (! $this->mnotify->isConfigured() && ! app()->environment('local')) {
            throw new RuntimeException('We cannot send a reset code right now. Please contact the school office.');
        }

        $parent = ParentAccount::query()
            ->where('phone', $phone)
            ->first();

        RateLimiter::hit($sendKey, (int) config('parent.otp_resend_seconds', 60));
        RateLimiter::hit($hourKey, 3600);

        if (! $parent || ! $parent->isActive()) {
            return;
        }

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $minutes = (int) config('parent.otp_expire_minutes', 10);

        ParentPasswordReset::query()->where('phone', $phone)->delete();

        $reset = ParentPasswordReset::create([
            'phone' => $phone,
            'otp_hash' => Hash::make($otp),
            'attempts' => 0,
            'expires_at' => now()->addMinutes($minutes),
        ]);

        if (app()->environment('local')) {
            Log::info('Parent portal password reset OTP', [
                'phone' => $phone,
                'otp' => $otp,
            ]);
        }

        if (! $this->mnotify->isConfigured()) {
            return;
        }

        $school = SchoolSetting::current()->name ?: 'EasySchool';
        $message = $school.': Your parent portal reset code is '.$otp.'. It expires in '.$minutes.' minutes. Do not share this code.';

        $response = $this->mnotify->sendQuickSms([$phone], $message);

        if ($response === null) {
            $reset->delete();

            throw new RuntimeException('We could not send the SMS. Please try again or contact the school.');
        }
    }

    public function resetPassword(string $phone, string $otp, string $password): void
    {
        $reset = ParentPasswordReset::query()
            ->where('phone', $phone)
            ->latest('id')
            ->first();

        if (! $reset || $reset->isExpired()) {
            $reset?->delete();

            throw new RuntimeException('That code is invalid or has expired. Request a new one.');
        }

        $maxAttempts = (int) config('parent.otp_max_attempts', 5);
        $reset->increment('attempts');

        if ($reset->attempts > $maxAttempts) {
            $reset->delete();

            throw new RuntimeException('Too many incorrect codes. Request a new one.');
        }

        if (! Hash::check($otp, $reset->otp_hash)) {
            throw new RuntimeException('That code is incorrect. Check the SMS and try again.');
        }

        $parent = ParentAccount::query()->where('phone', $phone)->first();

        if (! $parent || ! $parent->isActive()) {
            $reset->delete();

            throw new RuntimeException('That code is invalid or has expired. Request a new one.');
        }

        $parent->update([
            'password' => $password,
            'must_change_password' => false,
        ]);

        ParentPasswordReset::query()->where('phone', $phone)->delete();
        RateLimiter::clear('parent-reset-send:'.$phone);
    }
}
