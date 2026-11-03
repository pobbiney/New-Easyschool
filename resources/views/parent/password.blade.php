@extends('layouts.parent')

@section('title', 'Change password — Parent Portal')
@section('page-title', 'Change password')
@section('page-subtitle', 'Keep your parent portal account secure')

@section('css')
<style>
    .pw {
        --teal: #25A194;
        --teal-d: #0f766e;
        --ink: #0f172a;
        --muted: #64748b;
        --border: #e2e8f0;
    }
    .pw-hero {
        position: relative;
        overflow: hidden;
        border-radius: 24px;
        padding: 28px;
        margin-bottom: 22px;
        color: #fff;
        background: linear-gradient(135deg, #0f766e 0%, #25A194 52%, #2dd4bf 100%);
        box-shadow: 0 20px 50px rgba(15, 118, 110, .28);
    }
    .pw-hero::before,
    .pw-hero::after {
        content: '';
        position: absolute;
        border-radius: 50%;
        background: rgba(255,255,255,.08);
    }
    .pw-hero::before { width: 260px; height: 260px; top: -90px; right: -50px; }
    .pw-hero::after { width: 140px; height: 140px; bottom: -50px; left: 18%; }
    .pw-hero-inner {
        position: relative;
        z-index: 1;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
    }
    .pw-hero-icon {
        width: 64px;
        height: 64px;
        border-radius: 18px;
        background: rgba(255,255,255,.18);
        border: 1px solid rgba(255,255,255,.25);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
        flex-shrink: 0;
    }
    .pw-hero h2 {
        font-size: clamp(1.45rem, 3vw, 1.9rem);
        font-weight: 800;
        letter-spacing: -.03em;
        margin: 0 0 6px;
    }
    .pw-hero p { margin: 0; opacity: .92; font-weight: 600; font-size: 14px; max-width: 420px; }
    .pw-chips { display: flex; flex-wrap: wrap; gap: 10px; }
    .pw-chip {
        display: flex;
        align-items: center;
        gap: 8px;
        background: rgba(255,255,255,.16);
        border: 1px solid rgba(255,255,255,.22);
        border-radius: 999px;
        padding: 8px 14px;
        font-size: 13px;
        font-weight: 700;
        backdrop-filter: blur(6px);
    }

    .pw-facts {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
        margin-bottom: 22px;
    }
    .pw-fact {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 18px;
        padding: 16px 18px;
        display: flex;
        align-items: center;
        gap: 14px;
        box-shadow: 0 4px 16px rgba(15,23,42,.04);
    }
    .pw-fact-icon {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 20px;
        flex-shrink: 0;
    }
    .pw-fact-icon.teal { background: linear-gradient(135deg, #0f766e, #25A194); }
    .pw-fact-icon.amber { background: linear-gradient(135deg, #d97706, #f59e0b); }
    .pw-fact-icon.indigo { background: linear-gradient(135deg, #4f46e5, #818cf8); }
    .pw-fact-label {
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--muted);
        margin-bottom: 2px;
    }
    .pw-fact-value { font-size: 15px; font-weight: 800; color: var(--ink); }

    .pw-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(280px, .9fr);
        gap: 18px;
        align-items: start;
    }
    .pw-card {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 22px;
        overflow: hidden;
        box-shadow: 0 8px 28px rgba(15,23,42,.05);
    }
    .pw-card-head {
        padding: 18px 22px;
        border-bottom: 1px solid #eef2f6;
        background: linear-gradient(90deg, #f0fdfa, #ecfeff);
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .pw-card-head-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: linear-gradient(135deg, #0f766e, #25A194);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    .pw-card-head h3 { margin: 0; font-size: 16px; font-weight: 800; color: var(--ink); }
    .pw-card-head span { display: block; font-size: 12px; color: var(--muted); font-weight: 600; }
    .pw-card-body { padding: 22px; }

    .pw-field { margin-bottom: 18px; }
    .pw-field label {
        display: block;
        font-size: 13px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 8px;
    }
    .pw-input {
        position: relative;
    }
    .pw-input i.lead {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 18px;
        pointer-events: none;
    }
    .pw-input input {
        width: 100%;
        padding: 13px 46px 13px 44px;
        border: 1.5px solid var(--border);
        border-radius: 14px;
        font-size: 15px;
        font-family: inherit;
        color: var(--ink);
        background: #fff;
        transition: border-color .12s, box-shadow .12s;
    }
    .pw-input input:focus {
        outline: none;
        border-color: var(--teal);
        box-shadow: 0 0 0 4px rgba(37,161,148,.14);
    }
    .pw-input input.is-invalid {
        border-color: #f87171;
        box-shadow: 0 0 0 4px rgba(248,113,113,.12);
    }
    .pw-toggle {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        border: none;
        background: none;
        color: #94a3b8;
        cursor: pointer;
        font-size: 18px;
        padding: 6px;
        border-radius: 8px;
    }
    .pw-toggle:hover { color: var(--teal-d); background: #f0fdfa; }
    .pw-error { margin-top: 6px; font-size: 12px; font-weight: 700; color: #b91c1c; }

    .pw-meter {
        display: flex;
        gap: 6px;
        margin-top: 10px;
    }
    .pw-meter span {
        height: 6px;
        flex: 1;
        border-radius: 99px;
        background: #e2e8f0;
    }
    .pw-meter span.on-1 { background: #f87171; }
    .pw-meter span.on-2 { background: #f59e0b; }
    .pw-meter span.on-3 { background: #25A194; }
    .pw-meter-label { margin-top: 8px; font-size: 12px; font-weight: 700; color: var(--muted); }

    .pw-submit {
        width: 100%;
        margin-top: 6px;
        padding: 14px;
        border: none;
        border-radius: 14px;
        background: linear-gradient(135deg, #0f766e, #25A194);
        color: #fff;
        font-size: 15px;
        font-weight: 800;
        font-family: inherit;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 12px 28px rgba(37,161,148,.32);
    }
    .pw-submit:hover { filter: brightness(1.04); }

    .pw-tip {
        display: flex;
        gap: 12px;
        padding: 14px 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .pw-tip:last-child { border-bottom: 0; padding-bottom: 0; }
    .pw-tip:first-child { padding-top: 0; }
    .pw-tip i {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: #f0fdfa;
        color: var(--teal-d);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .pw-tip strong { display: block; font-size: 13px; font-weight: 800; color: var(--ink); margin-bottom: 2px; }
    .pw-tip span { font-size: 12px; color: var(--muted); font-weight: 600; line-height: 1.45; }

    .pw-side-note {
        margin-top: 14px;
        border-radius: 18px;
        padding: 16px 18px;
        background: linear-gradient(135deg, #fff7ed, #fef3c7);
        border: 1px solid #fde68a;
        color: #92400e;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.5;
        display: flex;
        gap: 10px;
    }
    .pw-side-note i { font-size: 18px; flex-shrink: 0; margin-top: 1px; }

    @media (max-width: 991px) {
        .pw-facts, .pw-grid { grid-template-columns: 1fr; }
    }
</style>
@endsection

@section('content')
@php
    $name = $parent->guardian_name ?: 'Parent';
    $childCount = $children->count();
    $lastLogin = $parent->last_login_at?->timezone(config('app.timezone'))->format('d M Y, g:i A');
@endphp

<div class="pw">
    <div class="pw-hero">
        <div class="pw-hero-inner">
            <div class="d-flex align-items-center gap-3">
                <div class="pw-hero-icon"><i class="ri-shield-keyhole-line"></i></div>
                <div>
                    <h2>Secure your account</h2>
                    <p>Hello {{ $name }}. Update the password you use with your guardian phone number.</p>
                </div>
            </div>
            <div class="pw-chips">
                <div class="pw-chip"><i class="ri-phone-line"></i> {{ $parent->phone }}</div>
                @if($parent->isActive())
                    <div class="pw-chip"><i class="ri-checkbox-circle-line"></i> Active</div>
                @endif
            </div>
        </div>
    </div>

    <div class="pw-facts">
        <div class="pw-fact">
            <div class="pw-fact-icon teal"><i class="ri-smartphone-line"></i></div>
            <div>
                <div class="pw-fact-label">Sign-in number</div>
                <div class="pw-fact-value">{{ $parent->phone }}</div>
            </div>
        </div>
        <div class="pw-fact">
            <div class="pw-fact-icon amber"><i class="ri-parent-line"></i></div>
            <div>
                <div class="pw-fact-label">Linked children</div>
                <div class="pw-fact-value">{{ $childCount }} {{ Str::plural('child', $childCount) }}</div>
            </div>
        </div>
        <div class="pw-fact">
            <div class="pw-fact-icon indigo"><i class="ri-time-line"></i></div>
            <div>
                <div class="pw-fact-label">Last sign-in</div>
                <div class="pw-fact-value">{{ $lastLogin ?: 'Just now' }}</div>
            </div>
        </div>
    </div>

    <div class="pw-grid">
        <div class="pw-card">
            <div class="pw-card-head">
                <div class="pw-card-head-icon"><i class="ri-lock-password-line"></i></div>
                <div>
                    <h3>New password</h3>
                    <span>Use at least 8 characters. You will need this the next time you sign in.</span>
                </div>
            </div>
            <div class="pw-card-body">
                <form method="POST" action="{{ route('parent.password.update') }}" id="parentPasswordForm">
                    @csrf
                    <div class="pw-field">
                        <label for="current_password">Current password</label>
                        <div class="pw-input">
                            <i class="lead ri-lock-line"></i>
                            <input type="password"
                                   id="current_password"
                                   name="current_password"
                                   class="@error('current_password') is-invalid @enderror"
                                   required
                                   autocomplete="current-password">
                            <button type="button" class="pw-toggle" data-target="current_password" aria-label="Show password">
                                <i class="ri-eye-line"></i>
                            </button>
                        </div>
                        @error('current_password')
                            <div class="pw-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="pw-field">
                        <label for="password">New password</label>
                        <div class="pw-input">
                            <i class="lead ri-key-2-line"></i>
                            <input type="password"
                                   id="password"
                                   name="password"
                                   class="@error('password') is-invalid @enderror"
                                   required
                                   minlength="8"
                                   autocomplete="new-password">
                            <button type="button" class="pw-toggle" data-target="password" aria-label="Show password">
                                <i class="ri-eye-line"></i>
                            </button>
                        </div>
                        <div class="pw-meter" aria-hidden="true">
                            <span></span><span></span><span></span>
                        </div>
                        <div class="pw-meter-label" id="pwStrengthLabel">Use 8 or more characters</div>
                        @error('password')
                            <div class="pw-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="pw-field">
                        <label for="password_confirmation">Confirm new password</label>
                        <div class="pw-input">
                            <i class="lead ri-lock-2-line"></i>
                            <input type="password"
                                   id="password_confirmation"
                                   name="password_confirmation"
                                   required
                                   minlength="8"
                                   autocomplete="new-password">
                            <button type="button" class="pw-toggle" data-target="password_confirmation" aria-label="Show password">
                                <i class="ri-eye-line"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="pw-submit">
                        <i class="ri-shield-check-line"></i> Update password
                    </button>
                </form>
            </div>
        </div>

        <div>
            <div class="pw-card">
                <div class="pw-card-head">
                    <div class="pw-card-head-icon" style="background:linear-gradient(135deg,#d97706,#f59e0b);">
                        <i class="ri-lightbulb-flash-line"></i>
                    </div>
                    <div>
                        <h3>Password tips</h3>
                        <span>A stronger password protects your child’s records.</span>
                    </div>
                </div>
                <div class="pw-card-body">
                    <div class="pw-tip">
                        <i class="ri-text"></i>
                        <div>
                            <strong>Make it long</strong>
                            <span>At least 8 characters. Mix letters and numbers if you can.</span>
                        </div>
                    </div>
                    <div class="pw-tip">
                        <i class="ri-user-unfollow-line"></i>
                        <div>
                            <strong>Avoid easy guesses</strong>
                            <span>Do not use your child’s name, date of birth, or 12345678.</span>
                        </div>
                    </div>
                    <div class="pw-tip">
                        <i class="ri-share-forward-line"></i>
                        <div>
                            <strong>Keep it private</strong>
                            <span>The school will never ask you to share this password.</span>
                        </div>
                    </div>
                    <div class="pw-tip">
                        <i class="ri-smartphone-line"></i>
                        <div>
                            <strong>Forgot it later?</strong>
                            <span>Use Forgot password on the sign-in page. We will SMS a code to {{ $parent->phone }}.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pw-side-note">
                <i class="ri-information-line"></i>
                <span>After you update it, use this new password the next time you open the parent portal.</span>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    (function () {
        document.querySelectorAll('.pw-toggle').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const input = document.getElementById(btn.getAttribute('data-target'));
                const icon = btn.querySelector('i');
                if (!input) return;
                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                icon.className = show ? 'ri-eye-off-line' : 'ri-eye-line';
            });
        });

        const input = document.getElementById('password');
        const bars = document.querySelectorAll('.pw-meter span');
        const label = document.getElementById('pwStrengthLabel');

        function score(value) {
            let n = 0;
            if (value.length >= 8) n++;
            if (/[A-Za-z]/.test(value) && /\d/.test(value)) n++;
            if (value.length >= 12 || /[^A-Za-z0-9]/.test(value)) n++;
            return n;
        }

        input?.addEventListener('input', function () {
            const n = score(input.value);
            bars.forEach(function (bar, i) {
                bar.className = '';
                if (n > i) bar.classList.add('on-' + n);
            });
            label.textContent = n === 0
                ? 'Use 8 or more characters'
                : n === 1
                    ? 'Fair — add letters and numbers'
                    : n === 2
                        ? 'Good — a bit longer is even better'
                        : 'Strong password';
        });
    })();
</script>
@endsection
