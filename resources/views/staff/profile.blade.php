@php
    $pageName = 'hr';
    $subpageName = 'profile';
    $displayName = $datas?->full_name ?: ($user->name ?? 'My account');
    $nameParts = preg_split('/\s+/', trim($displayName)) ?: [];
    $initials = strtoupper(mb_substr($nameParts[0] ?? 'U', 0, 1).mb_substr($nameParts[count($nameParts) - 1] ?? '', 0, 1));
    $photoUrl = $datas && ! empty($datas->picture)
        ? asset($datas->picture)
        : ($user?->profilePhotoUrl());
    $roleName = $user?->getUserCategory() ?: 'Staff';
    $positionName = $datas?->hrPosition?->name ?: ($datas?->position ?: '—');
    $facts = $datas ? [
        ['label' => 'Email', 'value' => $datas->email ?: ($user->email ?? '—'), 'icon' => 'ri-mail-line', 'grad' => 'gradient-bg-end-4', 'tone' => 'bg-primary-600'],
        ['label' => 'Mobile', 'value' => $datas->mobile ?: '—', 'icon' => 'ri-phone-line', 'grad' => 'gradient-bg-end-5', 'tone' => 'bg-success-600'],
        ['label' => 'Position', 'value' => $positionName, 'icon' => 'ri-briefcase-4-line', 'grad' => 'gradient-bg-end-2', 'tone' => 'bg-blue-600'],
        ['label' => 'Department', 'value' => $datas->department?->name ?: '—', 'icon' => 'ri-building-4-line', 'grad' => 'gradient-bg-end-3', 'tone' => 'bg-purple-600'],
        ['label' => 'Gender', 'value' => $datas->gender ?: '—', 'icon' => 'ri-user-smile-line', 'grad' => 'gradient-bg-end-1', 'tone' => 'bg-warning-600'],
        ['label' => 'Nationality', 'value' => $datas->country?->name ?: '—', 'icon' => 'ri-earth-line', 'grad' => 'gradient-bg-end-6', 'tone' => 'bg-cyan-600'],
    ] : [
        ['label' => 'Name', 'value' => $user->name, 'icon' => 'ri-user-3-line', 'grad' => 'gradient-bg-end-4', 'tone' => 'bg-primary-600'],
        ['label' => 'Email', 'value' => $user->email, 'icon' => 'ri-mail-line', 'grad' => 'gradient-bg-end-5', 'tone' => 'bg-success-600'],
        ['label' => 'Role', 'value' => $roleName, 'icon' => 'ri-shield-user-line', 'grad' => 'gradient-bg-end-2', 'tone' => 'bg-blue-600'],
    ];
@endphp
@extends('layouts.app')

@section('css')
@include('partials._academic-ui-styles')
<style>
    .pf-hero {
        position: relative; overflow: hidden; border-radius: 22px; padding: 28px 32px; margin-bottom: 24px;
        background: linear-gradient(120deg, #ecfeff 0%, #e0e7ff 45%, #fce7f3 100%);
        border: 1px solid #c7d2fe; box-shadow: 0 18px 40px rgba(99, 102, 241, .12);
    }
    .pf-photo {
        width: 112px; height: 112px; border-radius: 50%; overflow: hidden; flex-shrink: 0;
        border: 4px solid #fff; box-shadow: 0 12px 28px rgba(37, 161, 148, .28);
        background: linear-gradient(135deg, #25A194, #6366f1); color: #fff;
        display: flex; align-items: center; justify-content: center; font-size: 34px; font-weight: 800;
    }
    .pf-photo img { width: 100%; height: 100%; object-fit: cover; }
    .pf-name { font-size: 1.45rem; font-weight: 800; letter-spacing: -.03em; color: #0f172a; margin: 0 0 6px; }
    .pf-meta { display: flex; flex-wrap: wrap; gap: 8px; }
    .pf-card {
        border: 1px solid #e5e7eb; border-radius: 20px; background: #fff;
        box-shadow: 0 10px 30px rgba(15, 23, 42, .05); overflow: hidden; height: 100%;
    }
    .pf-card-head {
        padding: 18px 22px; border-bottom: 1px solid #eef2f6;
        background: linear-gradient(90deg, #f0fdfa, #eef2ff);
        display: flex; align-items: center; gap: 12px;
    }
    .pf-card-icon {
        width: 40px; height: 40px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center;
        color: #fff; font-size: 18px; flex-shrink: 0;
    }
    .pf-card-body { padding: 22px; }
    .pf-kpi-icon {
        width: 40px; height: 40px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;
        color: #fff; font-size: 18px; flex-shrink: 0;
    }
    .pf-preview {
        width: 148px; height: 148px; border-radius: 50%; margin: 0 auto 18px; overflow: hidden;
        border: 4px solid #ccfbf1; background: #f8fafc; display: flex; align-items: center; justify-content: center;
        font-size: 42px; font-weight: 800; color: #25A194;
        box-shadow: 0 10px 24px rgba(37, 161, 148, .16);
    }
    .pf-preview img { width: 100%; height: 100%; object-fit: cover; }
    .pf-file {
        position: relative; display: block; border: 1px dashed #99f6e4; border-radius: 14px;
        padding: 16px; text-align: center; background: #f0fdfa; cursor: pointer; color: #0f766e; font-weight: 700;
    }
    .pf-file input { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
    .pf-note { font-size: 12px; color: #64748b; }
    @media (max-width: 767px) { .pf-hero { padding: 22px 18px; } }
</style>
@endsection

@section('content')
<div class="dashboard-main-body">
    @include('partials._page-header', [
        'section' => 'Account',
        'crumbs' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'My profile', 'active' => true],
        ],
        'title' => 'My profile',
        'subtitle' => 'Your photo, contact details, and password.',
    ])

    <div class="pf-hero d-flex flex-wrap align-items-center gap-20">
        <div class="pf-photo">
            @if($photoUrl)
                <img src="{{ $photoUrl }}" alt="{{ $displayName }}">
            @else
                {{ $initials }}
            @endif
        </div>
        <div class="flex-grow-1">
            <div class="pf-name">{{ $displayName }}</div>
            <div class="pf-meta">
                <span class="ac-pill ac-pill-indigo"><i class="ri-shield-user-line"></i> {{ $roleName }}</span>
                @if($datas)
                    <span class="ac-pill ac-pill-teal"><i class="ri-id-card-line"></i> {{ $datas->employee_id ?: 'No staff ID' }}</span>
                    @if($datas->status === 'Active')
                        <span class="ac-pill ac-pill-emerald">Active</span>
                    @elseif($datas->status)
                        <span class="ac-pill ac-pill-rose">{{ $datas->status }}</span>
                    @endif
                @else
                    <span class="ac-pill ac-pill-amber">Login only</span>
                @endif
            </div>
            @if(! $datas)
                <p class="text-sm text-secondary-light mb-0 mt-10" style="max-width:560px;">
                    This login is not linked to an employee record. You can still change your password. Ask an administrator to attach a staff profile for photo and HR details.
                </p>
            @elseif($datas->residential_address)
                <p class="text-sm text-secondary-light mb-0 mt-10"><i class="ri-map-pin-line"></i> {{ $datas->residential_address }}</p>
            @endif
        </div>
    </div>

    <div class="row gy-4 mb-24">
        @foreach($facts as $fact)
            <div class="col-sm-6 col-xl-4">
                <div class="card shadow-1 radius-8 {{ $fact['grad'] }} h-100">
                    <div class="card-body p-20">
                        <div class="d-flex flex-wrap align-items-center gap-3 mb-10">
                            <div class="pf-kpi-icon {{ $fact['tone'] }}"><i class="{{ $fact['icon'] }}"></i></div>
                            <p class="fw-medium text-primary-light mb-0">{{ $fact['label'] }}</p>
                        </div>
                        <h6 class="mb-0 fw-bold">{{ $fact['value'] }}</h6>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row gy-4">
        <div class="col-lg-6">
            <div class="pf-card">
                <div class="pf-card-head">
                    <span class="pf-card-icon bg-primary-600"><i class="ri-camera-line"></i></span>
                    <div>
                        <h6 class="mb-0 fw-semibold">Profile photo</h6>
                        <div class="pf-note">JPG, PNG or WebP · max 2 MB</div>
                    </div>
                </div>
                <div class="pf-card-body">
                    <form enctype="multipart/form-data" method="POST" action="{{ route('update-photo-process') }}">
                        @csrf
                        @if($datas)
                            <input type="hidden" name="staff_id" value="{{ $datas->id }}">
                        @endif
                        <div class="pf-preview" id="profilePreview">
                            @if($photoUrl)
                                <img src="{{ $photoUrl }}" alt="Current photo">
                            @else
                                {{ $initials }}
                            @endif
                        </div>
                        <label class="pf-file mb-12" for="imageUpload">
                            <input type="file" name="image" id="imageUpload" accept="image/jpeg,image/png,image/webp" required>
                            <span class="d-block"><i class="ri-upload-2-line"></i> Choose a photo</span>
                            <span class="pf-note d-block mt-4" id="photoFileName">Click here or drop an image</span>
                        </label>
                        @error('image') <small class="text-danger-600 d-block mb-12">{{ $message }}</small> @enderror
                        <button type="submit" class="btn btn-primary-600 w-100"><i class="ri-save-line"></i> Save photo</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="pf-card">
                <div class="pf-card-head">
                    <span class="pf-card-icon bg-purple-600"><i class="ri-lock-password-line"></i></span>
                    <div>
                        <h6 class="mb-0 fw-semibold">Password</h6>
                        <div class="pf-note">Use at least 8 characters</div>
                    </div>
                </div>
                <div class="pf-card-body">
                    <form method="POST" action="{{ route('update-password-process') }}">
                        @csrf
                        <div class="mb-16">
                            <label class="form-label fw-semibold text-secondary-light text-md mb-8" for="current_password">Current password</label>
                            <input type="password" name="current_password" id="current_password" class="form-control" placeholder="Enter current password" autocomplete="current-password">
                            @error('current_password') <small class="text-danger-600">{{ $message }}</small> @enderror
                        </div>
                        <div class="mb-16">
                            <label class="form-label fw-semibold text-secondary-light text-md mb-8" for="new_password">New password</label>
                            <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Enter new password" autocomplete="new-password">
                            @error('new_password') <small class="text-danger-600">{{ $message }}</small> @enderror
                        </div>
                        <div class="mb-20">
                            <label class="form-label fw-semibold text-secondary-light text-md mb-8" for="confirm_password">Confirm new password</label>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Re-enter new password" autocomplete="new-password">
                            @error('confirm_password') <small class="text-danger-600">{{ $message }}</small> @enderror
                        </div>
                        <button type="submit" class="btn btn-primary-600 w-100"><i class="ri-shield-keyhole-line"></i> Update password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('imageUpload')?.addEventListener('change', function () {
        const file = this.files && this.files[0];
        const preview = document.getElementById('profilePreview');
        if (! file || ! preview) return;
        const reader = new FileReader();
        reader.onload = function (e) {
            preview.innerHTML = '<img src="' + e.target.result + '" alt="New photo">';
        };
        reader.readAsDataURL(file);
        const nameLabel = document.getElementById('photoFileName');
        if (nameLabel) nameLabel.textContent = file.name;
    });
</script>
@endsection
