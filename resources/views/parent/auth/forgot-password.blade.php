<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — {{ $school->name ?? $school->school_name ?? 'EasySchool' }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/remixicon.css') }}">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            background: #f4f7fb;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .fp-card {
            width: 100%;
            max-width: 420px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            box-shadow: 0 24px 60px rgba(15,23,42,.1);
            overflow: hidden;
        }
        .fp-accent { height: 5px; background: linear-gradient(90deg, #0f766e, #25A194); }
        .fp-body { padding: 32px 28px; }
        .fp-head { text-align: center; margin-bottom: 24px; }
        .fp-icon {
            width: 56px; height: 56px; border-radius: 16px;
            background: #e6f7f5; color: #0f766e;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 26px; margin-bottom: 14px;
        }
        .fp-head h1 { font-size: 22px; font-weight: 800; margin-bottom: 8px; color: #0f172a; }
        .fp-head p { font-size: 14px; color: #64748b; line-height: 1.55; }
        .fp-alert {
            padding: 12px 14px; border-radius: 12px; font-size: 13px; font-weight: 600;
            margin-bottom: 18px; line-height: 1.45;
        }
        .fp-alert.error { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; }
        .fp-field { margin-bottom: 18px; }
        .fp-field label {
            display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 8px;
        }
        .fp-input-wrap { position: relative; }
        .fp-input-wrap i {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            color: #94a3b8; font-size: 18px;
        }
        .fp-input-wrap input {
            width: 100%; padding: 13px 14px 13px 44px;
            border: 1.5px solid #e2e8f0; border-radius: 14px; font-size: 15px;
        }
        .fp-input-wrap input:focus {
            outline: none; border-color: #25A194; box-shadow: 0 0 0 3px rgba(37,161,148,.12);
        }
        .fp-submit {
            width: 100%; padding: 14px; border: none; border-radius: 14px;
            background: linear-gradient(135deg, #0f766e, #25A194); color: #fff;
            font-size: 16px; font-weight: 800; cursor: pointer;
            box-shadow: 0 10px 28px rgba(37,161,148,.32);
        }
        .fp-footer {
            text-align: center; margin-top: 20px; font-size: 13px; color: #64748b;
        }
        .fp-footer a { color: #0f766e; font-weight: 700; text-decoration: none; }
        .fp-note {
            margin-top: 18px; padding: 14px; border-radius: 14px;
            background: #fff7ed; border: 1px solid #fed7aa;
            font-size: 12px; color: #92400e; line-height: 1.55;
        }
    </style>
</head>
<body>
    <div class="fp-card">
        <div class="fp-accent"></div>
        <div class="fp-body">
            <div class="fp-head">
                <div class="fp-icon"><i class="ri-lock-password-line"></i></div>
                <h1>Reset password</h1>
                <p>Enter your guardian phone number. We will reset your password to the school default so you can sign in again.</p>
            </div>

            @if(session('message_error'))
                <div class="fp-alert error">{{ session('message_error') }}</div>
            @endif

            <form method="POST" action="{{ route('parent.forgot-password.process') }}">
                @csrf
                <div class="fp-field">
                    <label for="phone">Guardian phone</label>
                    <div class="fp-input-wrap">
                        <i class="ri-phone-line"></i>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" placeholder="024xxxxxxx" required autofocus>
                    </div>
                </div>
                <button type="submit" class="fp-submit">Reset my password</button>
            </form>

            <div class="fp-note">
                <i class="ri-information-line"></i>
                After reset, sign in with the temporary password shown on screen (and sent by SMS if configured). Then open <strong>Account</strong> to choose a new password.
            </div>

            <p class="fp-footer">
                <a href="{{ route('parent.login') }}"><i class="ri-arrow-left-line"></i> Back to sign in</a>
            </p>
        </div>
    </div>
</body>
</html>
