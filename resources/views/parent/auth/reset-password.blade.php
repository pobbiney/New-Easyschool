@extends('parent.auth.layout')

@section('title', 'Set new password')
@section('heading', 'Enter your code')
@section('subheading', 'We sent a 6-digit code to '.$maskedPhone)
@section('side-title', 'Choose a new password')
@section('side-copy', 'Type the SMS code, then set a password you can remember. You will sign in with your phone number and this new password.')

@section('side-features')
    <div class="pl-feature">
        <i class="ri-message-2-line"></i>
        <div>
            <strong>Check your SMS</strong>
            <span>The code was sent to {{ $maskedPhone }}</span>
        </div>
    </div>
    <div class="pl-feature">
        <i class="ri-lock-password-line"></i>
        <div>
            <strong>8 characters or more</strong>
            <span>Use a password only you know</span>
        </div>
    </div>
@endsection

@section('content')
    <form method="POST" action="{{ route('parent.reset-password.process') }}">
        @csrf
        <div class="pl-field">
            <label for="otp">SMS code</label>
            <div class="pl-input-wrap pl-otp">
                <input type="text"
                       id="otp"
                       name="otp"
                       value="{{ old('otp') }}"
                       inputmode="numeric"
                       pattern="[0-9]*"
                       maxlength="6"
                       placeholder="••••••"
                       required
                       autofocus
                       autocomplete="one-time-code">
            </div>
        </div>
        <div class="pl-field">
            <label for="password">New password</label>
            <div class="pl-input-wrap">
                <i class="field-icon ri-lock-line"></i>
                <input type="password"
                       id="password"
                       name="password"
                       placeholder="At least 8 characters"
                       required
                       minlength="8"
                       autocomplete="new-password">
                <button type="button" class="pl-toggle-pw" aria-label="Show password" onclick="togglePassword('password', 'pwIcon')">
                    <i class="ri-eye-line" id="pwIcon"></i>
                </button>
            </div>
        </div>
        <div class="pl-field">
            <label for="password_confirmation">Confirm new password</label>
            <div class="pl-input-wrap">
                <i class="field-icon ri-lock-2-line"></i>
                <input type="password"
                       id="password_confirmation"
                       name="password_confirmation"
                       placeholder="Type it again"
                       required
                       minlength="8"
                       autocomplete="new-password">
            </div>
        </div>
        <button type="submit" class="pl-submit">
            <i class="ri-check-line"></i> Update password
        </button>
    </form>

    <form method="POST" action="{{ route('parent.forgot-password.resend') }}" class="pl-resend">
        @csrf
        <button type="submit">Didn’t get the code? Resend SMS</button>
    </form>

    <p class="pl-footer">
        Wrong number? <a href="{{ route('parent.forgot-password') }}">Start over</a>
        · <a href="{{ route('parent.login') }}">Sign in</a>
    </p>
@endsection

@section('scripts')
    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            icon.className = show ? 'ri-eye-off-line' : 'ri-eye-line';
        }
    </script>
@endsection
