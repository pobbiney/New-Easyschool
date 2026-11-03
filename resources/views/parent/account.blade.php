@extends('layouts.parent')

@section('title', 'Account — Parent Portal')
@section('page-title', 'Account & Password')
@section('page-subtitle', 'Manage your parent portal login')

@section('css')
<style>
    .acc {
        --a-teal: #25A194;
        --a-teal-d: #0f766e;
        --a-ink: #0f172a;
        --a-muted: #64748b;
        --a-border: #e2e8f0;
        --a-green: #10b981;
        --a-amber: #f59e0b;
    }

    .acc-hero {
        position: relative;
        border-radius: 24px;
        padding: 28px;
        margin-bottom: 20px;
        color: #fff;
        overflow: hidden;
        background: linear-gradient(135deg, #0f766e 0%, #25A194 50%, #2dd4bf 100%);
        box-shadow: 0 20px 50px rgba(15, 118, 110, .28);
    }
    .acc-hero::before {
        content: '';
        position: absolute;
        width: 280px;
        height: 280px;
        border-radius: 50%;
        background: rgba(255,255,255,.08);
        top: -100px;
        right: -60px;
    }
    .acc-hero::after {
        content: '';
        position: absolute;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: rgba(255,255,255,.06);
        bottom: -40px;
        left: 20%;
    }
    .acc-hero-inner {
        position: relative;
        z-index: 1;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
    }
    .acc-hero-label {
        font-size: 13px;
        font-weight: 600;
        opacity: .85;
        margin-bottom: 8px;
    }
    .acc-hero-title {
        font-size: clamp(1.6rem, 4vw, 2.2rem);
        font-weight: 800;
        letter-spacing: -.03em;
        line-height: 1.15;
        margin-bottom: 6px;
    }
    .acc-hero-sub {
        font-size: 14px;
        opacity: .9;
        font-weight: 600;
    }
    .acc-hero-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        margin-top: 20px;
    }
    .acc-hero-meta div {
        background: rgba(255,255,255,.15);
        backdrop-filter: blur(4px);
        border-radius: 12px;
        padding: 10px 16px;
        font-size: 13px;
        font-weight: 600;
    }
    .acc-hero-meta strong {
        display: block;
        font-size: 16px;
        font-weight: 800;
        margin-top: 2px;
    }
    .acc-avatar {
        width: 72px;
        height: 72px;
        border-radius: 18px;
        background: rgba(255,255,255,.22);
        border: 3px solid rgba(255,255,255,.35);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        font-weight: 800;
        flex-shrink: 0;
        box-shadow: 0 8px 20px rgba(0,0,0,.12);
    }

    .acc-grid {
        display: grid;
        grid-template-columns: 1fr 1.2fr;
        gap: 20px;
        align-items: start;
    }
    @media (max-width: 960px) {
        .acc-grid { grid-template-columns: 1fr; }
    }

    .acc-card {
        background: #fff;
        border: 1px solid var(--a-border);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(15,23,42,.04);
    }
    .acc-card-head {
        padding: 18px 20px;
        border-bottom: 1px solid var(--a-border);
        background: #fafafa;
    }
    .acc-card-head h3 {
        margin: 0 0 4px;
        font-size: 15px;
        font-weight: 800;
        color: var(--a-ink);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .acc-card-head h3 i { color: var(--a-teal); }
    .acc-card-head p {
        margin: 0;
        font-size: 13px;
        color: var(--a-muted);
    }
    .acc-card-body { padding: 20px; }

    .acc-profile-row {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .acc-profile-row:last-child { border-bottom: none; padding-bottom: 0; }
    .acc-profile-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: #e6f7f5;
        color: var(--a-teal-d);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .acc-profile-icon.amber { background: #fffbeb; color: #d97706; }
    .acc-profile-icon.blue { background: #eff6ff; color: #2563eb; }
    .acc-profile-copy label {
        display: block;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: var(--a-muted);
        margin-bottom: 2px;
    }
    .acc-profile-copy div {
        font-size: 14px;
        font-weight: 700;
        color: var(--a-ink);
    }

    .acc-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        margin-top: 16px;
    }
    .acc-status.active { background: #ecfdf5; color: #047857; }
    .acc-status.warn { background: #fff7ed; color: #b45309; }

    .acc-field { margin-bottom: 16px; }
    .acc-field label {
        display: block;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: var(--a-muted);
        margin-bottom: 8px;
    }
    .acc-input-wrap { position: relative; }
    .acc-input-wrap i.field-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 18px;
        pointer-events: none;
    }
    .acc-input-wrap input {
        width: 100%;
        padding: 13px 44px 13px 44px;
        border: 1.5px solid var(--a-border);
        border-radius: 14px;
        font-size: 14px;
        color: var(--a-ink);
        background: #fff;
        transition: border-color .12s, box-shadow .12s;
    }
    .acc-input-wrap input:focus {
        outline: none;
        border-color: var(--a-teal);
        box-shadow: 0 0 0 3px rgba(37,161,148,.12);
    }
    .acc-input-wrap input.is-invalid { border-color: #ef4444; }
    .acc-toggle-pw {
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
    .acc-toggle-pw:hover { color: #64748b; }
    .acc-error {
        margin-top: 6px;
        font-size: 12px;
        font-weight: 600;
        color: #b91c1c;
    }

    .acc-rules {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 18px;
    }
    .acc-rule {
        font-size: 11px;
        font-weight: 700;
        padding: 6px 10px;
        border-radius: 999px;
        background: #f8fafc;
        border: 1px solid var(--a-border);
        color: var(--a-muted);
    }

    .acc-submit {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 13px 22px;
        border: none;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--a-teal-d), var(--a-teal));
        color: #fff;
        font-size: 14px;
        font-weight: 800;
        cursor: pointer;
        box-shadow: 0 8px 24px rgba(37,161,148,.28);
        transition: transform .12s;
    }
    .acc-submit:hover { transform: translateY(-1px); }

    .acc-note {
        margin-top: 18px;
        padding: 14px 16px;
        border-radius: 14px;
        background: #f8fafc;
        border: 1px solid var(--a-border);
        font-size: 13px;
        color: var(--a-muted);
        line-height: 1.55;
        display: flex;
        gap: 10px;
        align-items: flex-start;
    }
    .acc-note i { color: var(--a-teal); font-size: 18px; margin-top: 1px; }
    .acc-note a { color: var(--a-teal-d); font-weight: 700; text-decoration: none; }
    .acc-note a:hover { text-decoration: underline; }

    .acc-alert {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 16px;
        border-radius: 14px;
        margin-bottom: 20px;
        font-size: 14px;
        font-weight: 600;
        background: #fff7ed;
        border: 1px solid #fed7aa;
        color: #b45309;
    }
</style>
@endsection

@section('content')
@php
    $initials = strtoupper(substr($parent->guardian_name ?? 'P', 0, 1));
    $childCount = $children->count();
@endphp

<div class="acc">
    <div class="acc-hero">
        <div class="acc-hero-inner">
            <div>
                <div class="acc-hero-label">Your account</div>
                <div class="acc-hero-title">{{ $parent->guardian_name ?: 'Parent' }}</div>
                <div class="acc-hero-sub">Keep your login secure with a strong password</div>
                <div class="acc-hero-meta">
                    <div>Phone<strong>{{ $parent->phone }}</strong></div>
                    <div>Linked children<strong>{{ $childCount }}</strong></div>
                    @if($parent->last_login_at)
                        <div>Last sign-in<strong>{{ $parent->last_login_at->format('d M Y') }}</strong></div>
                    @endif
                </div>
            </div>
            <div class="acc-avatar">{{ $initials }}</div>
        </div>
    </div>

    @if($parent->must_change_password)
        <div class="acc-alert">
            <i class="ri-error-warning-fill"></i>
            Please set a new password before continuing to use the portal.
        </div>
    @endif

    <div class="acc-grid">
        <div class="acc-card">
            <div class="acc-card-head">
                <h3><i class="ri-user-settings-line"></i> Profile</h3>
                <p>Details linked to your parent portal login</p>
            </div>
            <div class="acc-card-body">
                <div class="acc-profile-row">
                    <div class="acc-profile-icon"><i class="ri-user-line"></i></div>
                    <div class="acc-profile-copy">
                        <label>Guardian name</label>
                        <div>{{ $parent->guardian_name ?: '—' }}</div>
                    </div>
                </div>
                <div class="acc-profile-row">
                    <div class="acc-profile-icon blue"><i class="ri-phone-line"></i></div>
                    <div class="acc-profile-copy">
                        <label>Phone (login ID)</label>
                        <div>{{ $parent->phone }}</div>
                    </div>
                </div>
                <div class="acc-profile-row">
                    <div class="acc-profile-icon amber"><i class="ri-team-line"></i></div>
                    <div class="acc-profile-copy">
                        <label>Children linked</label>
                        <div>{{ $childCount }} {{ Str::plural('child', $childCount) }}</div>
                    </div>
                </div>

                @if($parent->status === 'Active')
                    <span class="acc-status active"><i class="ri-checkbox-circle-fill"></i> Account active</span>
                @else
                    <span class="acc-status warn"><i class="ri-alert-line"></i> {{ $parent->status }}</span>
                @endif

                <div class="acc-note" style="margin-top:20px;">
                    <i class="ri-information-line"></i>
                    <div>To update your name or phone, contact the school office. Your phone must match the guardian number on your child's record.</div>
                </div>
            </div>
        </div>

        <div class="acc-card">
            <div class="acc-card-head">
                <h3><i class="ri-lock-password-line"></i> Change password</h3>
                <p>You will stay signed in after updating</p>
            </div>
            <div class="acc-card-body">
                <div class="acc-rules">
                    <span class="acc-rule">Minimum 8 characters</span>
                    <span class="acc-rule">Keep it private</span>
                    <span class="acc-rule">Do not share with children</span>
                </div>

                <form method="POST" action="{{ route('parent.account.password') }}">
                    @csrf
                    <div class="acc-field">
                        <label for="current_password">Current password</label>
                        <div class="acc-input-wrap">
                            <i class="field-icon ri-lock-line"></i>
                            <input type="password"
                                   id="current_password"
                                   name="current_password"
                                   class="@error('current_password') is-invalid @enderror"
                                   required
                                   autocomplete="current-password">
                            <button type="button" class="acc-toggle-pw" onclick="togglePw('current_password', this)" aria-label="Show password">
                                <i class="ri-eye-line"></i>
                            </button>
                        </div>
                        @error('current_password')<div class="acc-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="acc-field">
                        <label for="new_password">New password</label>
                        <div class="acc-input-wrap">
                            <i class="field-icon ri-key-line"></i>
                            <input type="password"
                                   id="new_password"
                                   name="new_password"
                                   class="@error('new_password') is-invalid @enderror"
                                   required
                                   minlength="8"
                                   autocomplete="new-password">
                            <button type="button" class="acc-toggle-pw" onclick="togglePw('new_password', this)" aria-label="Show password">
                                <i class="ri-eye-line"></i>
                            </button>
                        </div>
                        @error('new_password')<div class="acc-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="acc-field">
                        <label for="new_password_confirmation">Confirm new password</label>
                        <div class="acc-input-wrap">
                            <i class="field-icon ri-key-2-line"></i>
                            <input type="password"
                                   id="new_password_confirmation"
                                   name="new_password_confirmation"
                                   required
                                   minlength="8"
                                   autocomplete="new-password">
                            <button type="button" class="acc-toggle-pw" onclick="togglePw('new_password_confirmation', this)" aria-label="Show password">
                                <i class="ri-eye-line"></i>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="acc-submit">
                        <i class="ri-shield-check-line"></i> Update password
                    </button>
                </form>

                <div class="acc-note">
                    <i class="ri-question-line"></i>
                    <div>
                        Can't remember your current password?
                        <a href="{{ route('parent.forgot-password') }}">Reset password</a>
                        on the login page (you will be signed out).
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
function togglePw(id, btn) {
    const input = document.getElementById(id);
    const icon = btn.querySelector('i');
    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    icon.className = show ? 'ri-eye-off-line' : 'ri-eye-line';
}
</script>
@endsection
