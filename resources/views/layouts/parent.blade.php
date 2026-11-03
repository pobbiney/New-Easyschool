@php
    $school = $school ?? \App\Models\SchoolSetting::current();
    $parentUser = auth('parent')->user();
    $activeStudent = $student ?? null;
    $childNav = [
        ['route' => 'parent.child', 'icon' => 'ri-dashboard-line', 'label' => 'Overview'],
        ['route' => 'parent.academics', 'icon' => 'ri-book-read-line', 'label' => 'Academics'],
        ['route' => 'parent.bills', 'icon' => 'ri-bill-line', 'label' => 'Fees & Bills'],
        ['route' => 'parent.payments', 'icon' => 'ri-receipt-line', 'label' => 'Payments'],
        ['route' => 'parent.report-card', 'icon' => 'ri-file-chart-line', 'label' => 'Report Card'],
        ['route' => 'parent.communications.child', 'icon' => 'ri-message-3-line', 'label' => 'Messages'],
    ];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('parent.portal_name', 'Parent Portal'))</title>
    <link rel="stylesheet" href="{{ asset('assets/css/remixicon.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/lib/bootstrap.min.css') }}">
    <style>
        :root {
            --sidebar-w: 272px;
            --parent-teal: #25A194;
            --parent-teal-dark: #0d6b63;
            --parent-teal-light: #e6f7f5;
            --parent-amber: #f59e0b;
            --parent-ink: #0f172a;
            --parent-muted: #64748b;
            --parent-border: #e2e8f0;
            --parent-bg: #f4f7fb;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--parent-bg);
            color: var(--parent-ink);
            margin: 0;
            min-height: 100vh;
        }
        .parent-app {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .parent-sidebar {
            width: var(--sidebar-w);
            flex-shrink: 0;
            background: linear-gradient(180deg, #0f766e 0%, #115e59 100%);
            color: #fff;
            display: flex;
            flex-direction: column;
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 1040;
            transition: transform .25s ease;
        }
        .sidebar-brand {
            padding: 22px 20px 18px;
            border-bottom: 1px solid rgba(255,255,255,0.12);
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #fff;
        }
        .sidebar-brand img {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            object-fit: contain;
            background: rgba(255,255,255,0.95);
            padding: 4px;
        }
        .sidebar-brand-text {
            font-weight: 800;
            font-size: 1rem;
            line-height: 1.2;
        }
        .sidebar-brand-text small {
            display: block;
            font-weight: 500;
            font-size: 0.72rem;
            opacity: 0.75;
            margin-top: 2px;
        }
        .sidebar-scroll {
            flex: 1;
            overflow-y: auto;
            padding: 16px 12px;
        }
        .sidebar-label {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.45);
            padding: 12px 12px 8px;
        }
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            border-radius: 12px;
            color: rgba(255,255,255,0.82);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 4px;
            transition: background .15s, color .15s;
        }
        .sidebar-link i { font-size: 1.15rem; opacity: 0.9; }
        .sidebar-link:hover {
            background: rgba(255,255,255,0.1);
            color: #fff;
        }
        .sidebar-link.active {
            background: #fff;
            color: var(--parent-teal-dark);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }
        .sidebar-link.active i { opacity: 1; }
        .sidebar-child {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 14px;
            border-radius: 12px;
            color: rgba(255,255,255,0.78);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 3px;
        }
        .sidebar-child:hover { background: rgba(255,255,255,0.08); color: #fff; }
        .sidebar-child.active {
            background: rgba(255,255,255,0.15);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .sidebar-child-avatar {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            background: rgba(255,255,255,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.72rem;
            font-weight: 800;
            flex-shrink: 0;
            overflow: hidden;
        }
        .sidebar-child-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .sidebar-footer {
            padding: 16px 12px;
            border-top: 1px solid rgba(255,255,255,0.12);
        }
        .sidebar-user {
            padding: 10px 14px;
            border-radius: 12px;
            background: rgba(0,0,0,0.15);
            margin-bottom: 10px;
        }
        .sidebar-user-name {
            font-size: 0.85rem;
            font-weight: 700;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sidebar-user-phone {
            font-size: 0.75rem;
            opacity: 0.65;
        }
        .sidebar-logout {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 10px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.2);
            background: transparent;
            color: rgba(255,255,255,0.85);
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
        }
        .sidebar-logout:hover { background: rgba(255,255,255,0.08); color: #fff; }

        /* Main */
        .parent-main {
            flex: 1;
            margin-left: var(--sidebar-w);
            min-width: 0;
            display: flex;
            flex-direction: column;
        }
        .parent-topbar {
            background: #fff;
            border-bottom: 1px solid var(--parent-border);
            padding: 14px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .topbar-title {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--parent-ink);
            margin: 0;
        }
        .topbar-sub {
            font-size: 0.82rem;
            color: var(--parent-muted);
            margin: 0;
        }
        .sidebar-toggle {
            display: none;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            border: 1px solid var(--parent-border);
            background: #fff;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            cursor: pointer;
        }
        .parent-content {
            padding: 24px;
            flex: 1;
        }

        /* Cards */
        .parent-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid var(--parent-border);
            box-shadow: 0 4px 16px rgba(15,23,42,0.04);
            overflow: hidden;
        }
        .parent-card-accent {
            height: 3px;
            background: linear-gradient(90deg, var(--parent-teal), var(--parent-amber));
        }
        .stat-pill {
            background: #fef3c7;
            color: #92400e;
            border-radius: 999px;
            padding: 4px 12px;
            font-size: 0.8rem;
            font-weight: 700;
        }
        .stat-card {
            background: #fff;
            border: 1px solid var(--parent-border);
            border-radius: 14px;
            padding: 18px 20px;
        }
        .stat-card-label {
            font-size: 0.78rem;
            color: var(--parent-muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .stat-card-value {
            font-size: 1.5rem;
            font-weight: 800;
            margin-top: 4px;
        }

        /* Mobile */
        .sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15,23,42,0.45);
            z-index: 1035;
        }
        @media (max-width: 991px) {
            .parent-sidebar { transform: translateX(-100%); }
            .parent-sidebar.open { transform: translateX(0); }
            .sidebar-backdrop.show { display: block; }
            .parent-main { margin-left: 0; }
            .sidebar-toggle { display: inline-flex; }
            .parent-content { padding: 16px; }
        }

        .parent-swal.swal2-popup {
            border-radius: 22px !important;
            padding: 28px 24px 24px !important;
            font-family: 'Inter', sans-serif !important;
            box-shadow: 0 24px 60px rgba(15, 23, 42, .18) !important;
        }
        .parent-swal .swal2-title {
            font-size: 1.35rem !important;
            font-weight: 800 !important;
            color: #0f172a !important;
            letter-spacing: -.02em;
        }
        .parent-swal .swal2-html-container {
            font-size: 14px !important;
            font-weight: 600 !important;
            color: #64748b !important;
            margin-top: 6px !important;
        }
        .parent-swal .swal2-success {
            border-color: #25A194 !important;
            color: #25A194 !important;
        }
        .parent-swal .swal2-success [class^=swal2-success-line] {
            background-color: #25A194 !important;
        }
        .parent-swal .swal2-success .swal2-success-ring {
            border-color: rgba(37, 161, 148, .25) !important;
        }
        .parent-swal-btn {
            background: linear-gradient(135deg, #0f766e, #25A194) !important;
            border: 0 !important;
            border-radius: 12px !important;
            padding: 10px 28px !important;
            font-weight: 800 !important;
            box-shadow: 0 10px 22px rgba(37, 161, 148, .28) !important;
        }
        .parent-swal-error-btn {
            background: linear-gradient(135deg, #b91c1c, #ef4444) !important;
            border: 0 !important;
            border-radius: 12px !important;
            padding: 10px 28px !important;
            font-weight: 800 !important;
        }
    </style>
    @yield('css')
</head>
<body>
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <div class="parent-app">
        <aside class="parent-sidebar" id="parentSidebar">
            <a href="{{ route('parent.dashboard') }}" class="sidebar-brand">
                @if(!empty($school?->logo))
                    <img src="{{ asset($school->logo) }}" alt="">
                @else
                    <div class="sidebar-child-avatar" style="width:44px;height:44px;border-radius:12px;font-size:1rem;">
                        <i class="ri-home-heart-line"></i>
                    </div>
                @endif
                <div class="sidebar-brand-text">
                    {{ config('parent.portal_name', 'Parent Portal') }}
                    <small>{{ $school->name ?? 'EasySchool' }}</small>
                </div>
            </a>

            <div class="sidebar-scroll">
                <div class="sidebar-label">Main</div>
                <a href="{{ route('parent.dashboard') }}" class="sidebar-link {{ request()->routeIs('parent.dashboard') ? 'active' : '' }}">
                    <i class="ri-dashboard-3-line"></i> Dashboard
                </a>
                <a href="{{ route('parent.communications') }}" class="sidebar-link {{ request()->routeIs('parent.communications') && !request()->routeIs('parent.communications.child') ? 'active' : '' }}">
                    <i class="ri-mail-send-line"></i> Message School
                </a>
                <a href="{{ route('parent.account') }}" class="sidebar-link {{ request()->routeIs('parent.account') ? 'active' : '' }}">
                    <i class="ri-shield-keyhole-line"></i> Account
                </a>

                @isset($children)
                    @if($children->isNotEmpty())
                        <div class="sidebar-label">My Children</div>
                        @foreach($children as $child)
                            @php
                                $isActiveChild = $activeStudent && $activeStudent->id === $child->id;
                                $initials = strtoupper(substr($child->firstname,0,1).substr($child->surname,0,1));
                            @endphp
                            <a href="{{ route('parent.child', $child) }}" class="sidebar-child {{ $isActiveChild ? 'active' : '' }}">
                                <div class="sidebar-child-avatar">
                                    @if($child->picture)
                                        <img src="{{ asset($child->picture) }}" alt="">
                                    @else
                                        {{ $initials }}
                                    @endif
                                </div>
                                <span>{{ $child->full_name }}</span>
                            </a>
                        @endforeach
                    @endif
                @endisset

                @if($activeStudent)
                    <div class="sidebar-label">{{ $activeStudent->firstname }}'s Pages</div>
                    @foreach($childNav as $nav)
                        <a href="{{ route($nav['route'], $activeStudent) }}"
                           class="sidebar-link {{ request()->routeIs($nav['route']) ? 'active' : '' }}">
                            <i class="{{ $nav['icon'] }}"></i> {{ $nav['label'] }}
                        </a>
                    @endforeach
                @endif
            </div>

            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <div class="sidebar-user-name">{{ $parentUser->guardian_name ?: 'Parent' }}</div>
                    <div class="sidebar-user-phone">{{ $parentUser->phone }}</div>
                </div>
                <form action="{{ route('parent.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="sidebar-logout">
                        <i class="ri-logout-box-r-line"></i> Sign out
                    </button>
                </form>
            </div>
        </aside>

        <div class="parent-main">
            <header class="parent-topbar">
                <div class="d-flex align-items-center gap-3">
                    <button type="button" class="sidebar-toggle" id="sidebarToggle" aria-label="Open menu">
                        <i class="ri-menu-line"></i>
                    </button>
                    <div>
                        <h1 class="topbar-title">@yield('page-title', 'Dashboard')</h1>
                        @hasSection('page-subtitle')
                            <p class="topbar-sub">@yield('page-subtitle')</p>
                        @endif
                    </div>
                </div>
            </header>

            <main class="parent-content">
                @yield('content')
            </main>
        </div>
    </div>

    <script src="{{ asset('assets/js/lib/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib/sweetalert2.all.min.js') }}"></script>
    <script>
        (function () {
            const sidebar = document.getElementById('parentSidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            const toggle = document.getElementById('sidebarToggle');
            function closeSidebar() {
                sidebar?.classList.remove('open');
                backdrop?.classList.remove('show');
            }
            toggle?.addEventListener('click', function () {
                sidebar?.classList.toggle('open');
                backdrop?.classList.toggle('show');
            });
            backdrop?.addEventListener('click', closeSidebar);
            sidebar?.querySelectorAll('a').forEach(function (link) {
                link.addEventListener('click', function () {
                    if (window.innerWidth < 992) closeSidebar();
                });
            });

            window.showParentAlert = function (type, message) {
                if (!message) {
                    return;
                }

                const isSuccess = type === 'success';
                const lower = String(message).toLowerCase();
                let title = isSuccess ? 'Success' : 'Please check';

                if (isSuccess && lower.includes('password')) {
                    title = 'Password updated';
                } else if (!isSuccess && lower.includes('password')) {
                    title = 'Password not updated';
                }

                Swal.fire({
                    icon: isSuccess ? 'success' : 'error',
                    title: title,
                    text: message,
                    confirmButtonText: 'OK',
                    buttonsStyling: false,
                    customClass: {
                        popup: 'parent-swal',
                        confirmButton: isSuccess ? 'parent-swal-btn' : 'parent-swal-error-btn',
                    },
                });
            };

            @if(session('message_success'))
                showParentAlert('success', @json(session('message_success')));
            @endif

            @if(session('message_error'))
                showParentAlert('error', @json(session('message_error')));
            @endif
        })();
    </script>
    @yield('js')
</body>
</html>
