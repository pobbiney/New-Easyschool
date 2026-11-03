<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') — {{ $school->name ?? 'EasySchool' }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/remixicon.css') }}">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            color: #0f172a;
            background: #f4f7fb;
            display: flex;
            align-items: stretch;
        }

        .pl-side {
            flex: 1.1;
            position: relative;
            overflow: hidden;
            background: linear-gradient(160deg, #0f766e 0%, #25A194 45%, #2dd4bf 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 56px;
        }
        .pl-side::before {
            content: '';
            position: absolute;
            width: 420px;
            height: 420px;
            border-radius: 50%;
            background: rgba(255,255,255,.08);
            top: -120px;
            right: -80px;
        }
        .pl-side::after {
            content: '';
            position: absolute;
            width: 280px;
            height: 280px;
            border-radius: 50%;
            background: rgba(255,255,255,.06);
            bottom: -80px;
            left: -40px;
        }
        .pl-side-inner { position: relative; z-index: 1; max-width: 480px; }
        .pl-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(255,255,255,.15);
            border: 1px solid rgba(255,255,255,.2);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            margin-bottom: 24px;
        }
        .pl-side h1 {
            font-size: clamp(2rem, 4vw, 2.75rem);
            font-weight: 800;
            letter-spacing: -.03em;
            line-height: 1.15;
            margin-bottom: 16px;
        }
        .pl-side p {
            font-size: 16px;
            line-height: 1.65;
            opacity: .92;
            margin-bottom: 32px;
        }
        .pl-features { display: flex; flex-direction: column; gap: 14px; }
        .pl-feature {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            border-radius: 16px;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.15);
            backdrop-filter: blur(4px);
        }
        .pl-feature i {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: rgba(255,255,255,.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        .pl-feature strong { display: block; font-size: 14px; font-weight: 800; margin-bottom: 2px; }
        .pl-feature span { font-size: 13px; opacity: .85; }

        .pl-main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 24px;
            background:
                radial-gradient(circle at 90% 10%, rgba(37,161,148,.08), transparent 40%),
                radial-gradient(circle at 10% 90%, rgba(245,158,11,.06), transparent 35%),
                #f4f7fb;
        }
        .pl-card { width: 100%; max-width: 420px; }
        .pl-card-head { text-align: center; margin-bottom: 28px; }
        .pl-logo {
            width: 72px;
            height: 72px;
            border-radius: 18px;
            object-fit: contain;
            background: #fff;
            border: 1px solid #e2e8f0;
            padding: 6px;
            margin-bottom: 16px;
            box-shadow: 0 8px 24px rgba(15,23,42,.08);
        }
        .pl-logo-fallback {
            width: 72px;
            height: 72px;
            border-radius: 18px;
            background: linear-gradient(135deg, #0f766e, #25A194);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 16px;
            box-shadow: 0 8px 24px rgba(37,161,148,.3);
        }
        .pl-card-head h2 {
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -.02em;
            margin-bottom: 6px;
        }
        .pl-card-head p { font-size: 14px; color: #64748b; font-weight: 500; }

        .pl-alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
            line-height: 1.45;
        }
        .pl-alert i { font-size: 18px; margin-top: 1px; }
        .pl-alert.error { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; }
        .pl-alert.success { background: #ecfdf5; border: 1px solid #bbf7d0; color: #047857; }

        .pl-field { margin-bottom: 18px; }
        .pl-field label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 8px;
        }
        .pl-input-wrap { position: relative; }
        .pl-input-wrap i.field-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 18px;
            pointer-events: none;
        }
        .pl-input-wrap input {
            width: 100%;
            padding: 13px 14px 13px 44px;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            font-size: 15px;
            font-family: inherit;
            color: #0f172a;
            background: #fff;
            transition: border-color .12s, box-shadow .12s;
        }
        .pl-input-wrap input:focus {
            outline: none;
            border-color: #25A194;
            box-shadow: 0 0 0 3px rgba(37,161,148,.12);
        }
        .pl-input-wrap input.is-invalid { border-color: #f87171; }
        .pl-error { font-size: 12px; color: #b91c1c; margin-top: 6px; font-weight: 600; }
        .pl-toggle-pw {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: none;
            color: #94a3b8;
            cursor: pointer;
            font-size: 18px;
            padding: 4px;
        }
        .pl-toggle-pw:hover { color: #64748b; }

        .pl-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 22px;
            font-size: 13px;
        }
        .pl-check {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #64748b;
            font-weight: 600;
            cursor: pointer;
        }
        .pl-check input { width: 16px; height: 16px; accent-color: #25A194; }
        .pl-forgot {
            color: #0f766e;
            font-weight: 700;
            text-decoration: none;
        }
        .pl-forgot:hover { text-decoration: underline; }

        .pl-submit {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 14px;
            background: linear-gradient(135deg, #0f766e, #25A194);
            color: #fff;
            font-size: 16px;
            font-weight: 800;
            font-family: inherit;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 10px 28px rgba(37,161,148,.32);
            transition: transform .12s, box-shadow .12s;
        }
        .pl-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 32px rgba(37,161,148,.38);
        }

        .pl-footer {
            text-align: center;
            margin-top: 24px;
            font-size: 13px;
            color: #64748b;
            font-weight: 500;
        }
        .pl-footer a { color: #0f766e; font-weight: 700; text-decoration: none; }
        .pl-footer a:hover { text-decoration: underline; }
        .pl-resend {
            margin-top: 14px;
            text-align: center;
        }
        .pl-resend button {
            border: none;
            background: none;
            color: #0f766e;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            font-family: inherit;
        }
        .pl-resend button:hover { text-decoration: underline; }

        .pl-hint {
            margin-top: 20px;
            padding: 14px 16px;
            border-radius: 14px;
            background: #fff;
            border: 1px solid #e2e8f0;
            font-size: 12px;
            color: #64748b;
            line-height: 1.55;
        }
        .pl-hint i { color: #25A194; margin-right: 4px; }

        .pl-otp input {
            padding-left: 14px;
            letter-spacing: .35em;
            text-align: center;
            font-size: 22px;
            font-weight: 800;
        }

        @media (max-width: 960px) {
            body { flex-direction: column; }
            .pl-side { flex: none; padding: 36px 28px; }
            .pl-features { display: none; }
            .pl-side p { margin-bottom: 0; font-size: 15px; }
        }
    </style>
</head>
<body>
    <aside class="pl-side">
        <div class="pl-side-inner">
            <div class="pl-badge"><i class="ri-parent-line"></i> Parent portal</div>
            <h1>@yield('side-title', "Stay connected with your child's school")</h1>
            <p>@yield('side-copy', 'View fees, pay online, check grades, download report cards, and message the school — all in one place.')</p>
            <div class="pl-features">
                @yield('side-features')
            </div>
        </div>
    </aside>

    <main class="pl-main">
        <div class="pl-card">
            <div class="pl-card-head">
                @php $schoolName = $school->name ?? 'EasySchool'; @endphp
                @if(method_exists($school, 'logoUrl') && $school->logoUrl())
                    <img src="{{ $school->logoUrl() }}" alt="{{ $schoolName }}" class="pl-logo">
                @else
                    <div class="pl-logo-fallback">{{ strtoupper(substr($schoolName, 0, 1)) }}</div>
                @endif
                <h2>@yield('heading')</h2>
                <p>@yield('subheading')</p>
            </div>

            @if($errors->any())
                <div class="pl-alert error">
                    <i class="ri-error-warning-fill"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @elseif(session('login_error_message') || session('message_error'))
                <div class="pl-alert error">
                    <i class="ri-error-warning-fill"></i>
                    <span>{{ session('login_error_message') ?: session('message_error') }}</span>
                </div>
            @endif
            @if(session('message_success'))
                <div class="pl-alert success">
                    <i class="ri-checkbox-circle-fill"></i>
                    <span>{{ session('message_success') }}</span>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    @yield('scripts')
</body>
</html>
