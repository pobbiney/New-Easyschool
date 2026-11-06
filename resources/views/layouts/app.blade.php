@php
    $staff_query = DB::select('SELECT * FROM staff WHERE id = :id', ['id' => auth()->user()->staff_id]);

    $links = app(\App\Services\UserAccessService::class)->menuLinks();
    $parents = array();
    $child = array();
    foreach ($links as $row_links) {
        if ($row_links->link_parent == 0) {
            $parents[] = $row_links;
        } else {
            $child[] = $row_links;
        }
    }

@endphp

<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="description"
    content="Modern Education Admin Dashboard for schools, colleges, universities, and eLearning platforms. Includes student and course management, attendance, exams, payments, analytics, and a fully responsive clean UI—ideal for LMS, coaching centers, and academic admin systems.">
  <meta name="keywords"
    content="Education Admin Dashboard, School Admin Panel, College Dashboard, University Dashboard, LMS Dashboard, eLearning Admin Template, Student Management System, Course Management, Education Template, Study Dashboard, Online Learning Dashboard, Academic Admin Panel">
  <meta name="robots" content="INDEX,FOLLOW">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Title -->
  <title>EasySchool - School, College & LMS  </title>
 

   <link rel="icon" type="image/png" href="{{asset('assets/images/favicon.png')}}" sizes="16x16">
  <!-- remix icon font css  -->
  <link rel="stylesheet" href="{{asset('assets/css/remixicon.css')}}">
  <!-- BootStrap css -->
  <link rel="stylesheet" href="{{asset('assets/css/lib/bootstrap.min.css')}}">
  <!-- Apex Chart css -->
  <link rel="stylesheet" href="{{asset('assets/css/lib/apexcharts.css')}}">
  <!-- Data Table css -->
  <link rel="stylesheet" href="{{asset('assets/css/lib/dataTables.min.css')}}">
  <!-- Date picker css -->
  <link rel="stylesheet" href="{{asset('assets/css/lib/flatpickr.min.css')}}">
  <!-- Calendar css -->
  <link rel="stylesheet" href="{{asset('assets/css/lib/full-calendar.css')}}">
  <!-- calendar -->
  <link rel="stylesheet" href="{{asset('assets/css/lib/calendar.css')}}">
  <!-- main css -->
  <link rel="stylesheet" href="{{asset('assets/css/style.css')}}">
  @include('partials._page-header-styles')

  <style>
    /* ── Clean light sidebar ── */
    .sidebar {
      background: #f8fafc !important;
      border-right: 1px solid #e2e8f0 !important;
      box-shadow: none !important;
      overflow: visible !important;
    }

    .sidebar::before,
    .sidebar::after {
      display: none;
    }

    .sidebar > * {
      position: relative;
      z-index: 1;
    }

    .sidebar > .sidebar-profile-wrap {
      z-index: 30;
    }

    .sidebar-logo {
      border-bottom: 1px solid #e2e8f0;
      padding-bottom: 8px;
      background: #fff;
      margin: 0;
      padding-left: 1rem;
      padding-right: 1rem;
    }

    .sidebar .sidebar-logo img.light-logo {
      display: inline-block !important;
    }

    .sidebar .sidebar-logo img.dark-logo {
      display: none !important;
    }

    .sidebar .sidebar-toggle {
      color: #94a3b8 !important;
      transition: color 0.2s ease, background 0.2s ease;
      border-radius: 8px;
      padding: 4px;
    }

    .sidebar .sidebar-toggle:hover {
      color: #25A194 !important;
      background: rgba(37, 161, 148, 0.08);
    }

    .sidebar-close-btn {
      border-color: #e2e8f0 !important;
      color: #64748b !important;
      background: #fff;
    }

    .sidebar-profile-wrap {
      position: relative;
      z-index: 30;
      padding-top: 16px !important;
      padding-bottom: 8px !important;
    }

    .sidebar .profile-dropdown__button {
      background: #fff !important;
      border: 1px solid #e2e8f0 !important;
      box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
      transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .sidebar .profile-dropdown__button:hover {
      border-color: #25A194 !important;
      box-shadow: 0 4px 12px rgba(37, 161, 148, 0.12);
    }

    .sidebar .profile-dropdown__contents .h6,
    .sidebar .profile-dropdown__contents .text-primary-light {
      color: #1e293b !important;
    }

    .sidebar .profile-dropdown__contents .text-secondary-light {
      color: #64748b !important;
    }

    .sidebar .profile-dropdown__icon {
      color: #94a3b8 !important;
    }

    .sidebar-menu {
      font-family: 'Inter', 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
      padding-top: 4px;
    }

    .sidebar-menu .menu-name,
    .sidebar-menu > li > a > span {
      font-size: 14px;
      font-weight: 500;
    }

    .sidebar-submenu li a {
      font-size: 13.5px;
      font-weight: 500;
    }

    .sidebar-menu li a {
      color: #475569 !important;
      border-radius: 10px;
      margin-bottom: 2px;
      border-left: 3px solid transparent;
      transition: color 0.15s ease, background 0.15s ease, border-color 0.15s ease;
    }

    .sidebar-menu li a i {
      color: #94a3b8;
      transition: color 0.15s ease;
    }

    .sidebar-menu li a:hover {
      color: #1e293b !important;
      background: #fff !important;
      box-shadow: 0 1px 4px rgba(15, 23, 42, 0.06);
    }

    .sidebar-menu li a:hover i {
      color: #25A194;
    }

    .sidebar-menu li.dropdown.open > a,
    .sidebar-menu li.dropdown.dropdown-open > a {
      background: #fff !important;
      color: #25A194 !important;
      border-left-color: #25A194 !important;
      box-shadow: 0 2px 8px rgba(37, 161, 148, 0.12);
      font-weight: 600;
    }

    .sidebar-menu li.dropdown.open > a i,
    .sidebar-menu li.dropdown.dropdown-open > a i {
      color: #25A194 !important;
    }

    .sidebar-menu li.dropdown.open > a:hover,
    .sidebar-menu li.dropdown.dropdown-open > a:hover {
      color: #1e8578 !important;
      transform: none;
      box-shadow: 0 2px 10px rgba(37, 161, 148, 0.16);
    }

    .sidebar-menu li > a.active-page,
    .sidebar-menu li > a.active {
      background: #25A194 !important;
      color: #fff !important;
      border-left-color: #25A194 !important;
      box-shadow: 0 2px 8px rgba(37, 161, 148, 0.28);
      font-weight: 600;
    }

    .sidebar-menu li > a.active-page i,
    .sidebar-menu li > a.active i {
      color: #fff !important;
    }

    .sidebar-menu li > a.active-page:hover,
    .sidebar-menu li > a.active:hover {
      background: #1e8578 !important;
      color: #fff !important;
      transform: none;
      box-shadow: 0 2px 10px rgba(37, 161, 148, 0.32);
    }

    .sidebar-menu .sidebar-submenu::before {
      border-color: #cbd5e1 !important;
    }

    .sidebar-menu .sidebar-submenu li a {
      color: #64748b !important;
      border-left: none;
      box-shadow: none !important;
    }

    .sidebar-menu .sidebar-submenu li a:hover {
      color: #25A194 !important;
      background: rgba(37, 161, 148, 0.06) !important;
      box-shadow: none !important;
    }

    .sidebar-menu .sidebar-submenu li.active-page a:hover,
    .sidebar-menu .sidebar-submenu li a.active:hover {
      color: #fff !important;
      background: #1e8578 !important;
    }

    .sidebar-menu .sidebar-submenu li.active-page a,
    .sidebar-menu .sidebar-submenu li a.active {
      color: #fff !important;
      background: #25A194 !important;
      font-weight: 600;
      border-radius: 8px;
    }

    .sidebar-menu .sidebar-submenu .circle-icon {
      color: #cbd5e1 !important;
      background: #f8fafc !important;
      box-shadow: 0 0 0 8px #f8fafc !important;
    }

    .sidebar-menu .sidebar-submenu li.active-page a .circle-icon,
    .sidebar-menu .sidebar-submenu li a.active .circle-icon {
      color: #fff !important;
      background: transparent !important;
      box-shadow: none !important;
    }

    .sidebar-menu-area {
      position: relative;
      z-index: 1;
      border-inline-end: none !important;
      padding-top: 8px !important;
    }

    .sidebar-menu-area::-webkit-scrollbar {
      width: 5px;
      background: transparent;
    }

    .sidebar-menu-area::-webkit-scrollbar-thumb {
      background: #cbd5e1;
      border-radius: 4px;
    }

    .sidebar-menu-area:hover::-webkit-scrollbar-thumb {
      background: #94a3b8;
    }

    .sidebar-menu .menu-name.active {
      color: inherit;
    }

    .sidebar.active .profile-dropdown__button {
      background: #fff !important;
      border: 1px solid #e2e8f0 !important;
    }

    .sidebar .profile-dropdown {
      position: relative;
      z-index: 30;
    }

    .sidebar .profile-dropdown .dropdown-menu {
      position: static !important;
      inset: auto !important;
      transform: none !important;
      float: none;
      width: 100%;
      min-width: 100%;
      margin-top: 8px;
      z-index: 40;
      box-shadow: 0 8px 24px rgba(15, 23, 42, 0.1);
      border: 1px solid #e2e8f0 !important;
      border-radius: 12px !important;
    }

    .sidebar .profile-dropdown .dropdown-menu.show {
      display: block;
    }


.app-toast.swal2-popup.swal2-toast {
    width: auto !important;
    min-width: 320px !important;
    max-width: min(560px, calc(100vw - 24px)) !important;
    border-radius: 14px !important;
    padding: 0 !important;
    margin: 0 !important;
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.16) !important;
    border: 1px solid rgba(15, 23, 42, 0.08) !important;
    background: #fff !important;
    overflow: hidden !important;
    display: block !important;
    position: relative !important;
    grid-template-columns: none !important;
    grid-template-rows: none !important;
}

body.swal2-toast-shown .swal2-container {
    width: auto !important;
    max-width: none !important;
    pointer-events: none !important;
}

body.swal2-toast-shown .swal2-container .swal2-popup {
    pointer-events: auto !important;
}

.app-toast .swal2-header,
.app-toast .swal2-icon,
.app-toast .swal2-title,
.app-toast .swal2-actions,
.app-toast .swal2-footer,
.app-toast .swal2-close {
    display: none !important;
}

.app-toast .swal2-html-container {
    margin: 0 !important;
    padding: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
    overflow: hidden !important;
    text-align: left !important;
    font-size: inherit !important;
    line-height: inherit !important;
    display: block !important;
}

.app-toast .swal2-timer-progress-bar-container {
    position: absolute !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    width: 100% !important;
    height: 3px !important;
    border-radius: 0 !important;
    overflow: hidden !important;
}

.app-toast-wrap {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    width: 100%;
    box-sizing: border-box;
    padding: 16px 18px 20px;
    border-left: 4px solid #25A194;
    background: transparent;
    text-align: left;
}

.app-toast-wrap.is-error {
    border-left-color: #E5484D;
}

.app-toast-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
    margin-top: 1px;
}

.app-toast-icon.is-success {
    background: rgba(37, 161, 148, 0.12);
    color: #25A194;
}

.app-toast-icon.is-error {
    background: rgba(229, 72, 77, 0.12);
    color: #E5484D;
}

.app-toast-body {
    flex: 1;
    min-width: 0;
}

.app-toast-heading {
    font-size: 15px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 4px;
    line-height: 1.35;
}

.app-toast-message {
    font-size: 13px;
    color: #4B5563;
    line-height: 1.55;
    word-break: break-word;
    overflow-wrap: anywhere;
    white-space: normal;
}

.swal-timer-success {
    background: #25A194 !important;
    height: 3px !important;
}

.swal-timer-error {
    background: #E5484D !important;
    height: 3px !important;
}

.app-confirm-swal.swal2-popup {
    border-radius: 16px !important;
    padding: 28px 24px 22px !important;
    font-family: inherit !important;
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.16) !important;
}

.app-confirm-swal .swal2-title {
    font-size: 1.2rem !important;
    font-weight: 700 !important;
    color: #111827 !important;
}

.app-confirm-swal .swal2-html-container,
.app-confirm-swal .swal2-html-container.swal2-html-container {
    font-size: 14px !important;
    color: #4B5563 !important;
    margin-top: 8px !important;
}

.app-confirm-swal .swal2-actions {
    gap: 10px !important;
}

.app-confirm-swal .swal2-confirm,
.app-confirm-swal .swal2-cancel {
    border-radius: 10px !important;
    font-weight: 600 !important;
    padding: 8px 18px !important;
}
    </style>
  @yield('css')
</head>

<body>

  <!-- Theme Customization Structure Start -->
<div class="body-overlay"></div>

<button type="button"
    class="theme-customization__button w-48-px h-48-px bg-primary-600 text-white rounded-circle d-flex justify-content-center align-items-center position-fixed end-0 bottom-0 mb-40 me-40 text-2xxl bg-hover-primary-700" aria-label="Theme Customization Button">
    <i class="ri-settings-3-line animate-spin"></i>
</button>
<div class="theme-customization-sidebar w-100 bg-base h-100vh overflow-y-auto position-fixed end-0 top-0">
    <div class="d-flex align-items-center gap-3 py-16 px-24 justify-content-between border-bottom">
        <div>
            <h6 class="text-sm dark:text-white">Theme Settings</h6>
            <p class="text-xs mb-0 text-neutral-500 dark:text-neutral-200">Customize and preview instantly</p>
        </div>
        <button data-slot="button"
            class="theme-customization-sidebar__close text-neutral-900 bg-transparent text-hover-primary-600 d-flex text-xl">
            <i class="ri-close-fill"></i>
        </button>
    </div>

    <div class="d-flex flex-column gap-48 p-24 overflow-y-auto flex-grow-1">

        <div class="theme-setting-item">
            <h6 class="fw-medium text-primary-light text-md mb-3">Theme Mode</h6>
            <div class="d-grid grid-cols-3 gap-3 dark-light-mode">
                <button type="button"
                    class="theme-btn theme-setting-item__btn d-flex align-items-center justify-content-center h-64-px rounded-3 text-xl active"
                    data-theme="light" aria-label="light">
                    <i class="ri-sun-line"></i>
                </button>
                <button type="button"
                    class="theme-btn theme-setting-item__btn d-flex align-items-center justify-content-center h-64-px rounded-3 text-xl"
                    data-theme="dark" aria-label="dark">
                    <i class="ri-moon-line"></i>
                </button>
                <button type="button"
                    class="theme-btn theme-setting-item__btn d-flex align-items-center justify-content-center h-64-px rounded-3 text-xl"
                    data-theme="system" aria-label="system">
                    <i class="ri-computer-line"></i>
                </button>
            </div>
        </div>

        <div class="theme-setting-item">
            <h6 class="fw-medium text-primary-light text-md mb-3">Page Direction</h6>
            <div class="d-grid grid-cols-2 gap-3">
                <button type="button"
                    class="theme-setting-item__btn ltr-mode-btn d-flex align-items-center justify-content-center gap-2 h-56-px rounded-3 text-xl" aria-label="LTR">
                    <span><i class="ri-align-item-left-line"></i></span>
                    <span class="h6 text-sm font-medium mb-0">LTR</span>
                </button>

                <button type="button"
                    class="theme-setting-item__btn rtl-mode-btn d-flex align-items-center justify-content-center gap-2 h-56-px rounded-3 text-xl" aria-label="RTL">
                    <span class="h6 text-sm font-medium mb-0">RTL</span>
                    <span><i class="ri-align-item-right-line"></i></span>
                </button>
            </div>
        </div>

        <div class="theme-setting-item">
            <h6 class="fw-medium text-primary-light text-md mb-3">Color Schema</h6>
            <div class="d-grid grid-cols-3 gap-3">
                <button type="button"
                    class="color-picker-btn d-flex flex-column justify-content-center align-items-center"
                    data-color="base" aria-label="Base">
                    <span class="color-picker-btn__box h-40-px w-100 rounded-3"
                        style="background-color: #25A194;"></span>
                    <span class="fw-medium mt-1" style="color: #25A194;">Base</span>
                </button>
                <button type="button"
                    class="color-picker-btn d-flex flex-column justify-content-center align-items-center"
                    data-color="red" aria-label="Red">
                    <span class="color-picker-btn__box h-40-px w-100 rounded-3"
                        style="background-color: #dc2626;"></span>
                    <span class="fw-medium mt-1" style="color: #dc2626;">Red</span>
                </button>
                <button type="button"
                    class="color-picker-btn d-flex flex-column justify-content-center align-items-center"
                    data-color="blue" aria-label="Blue">
                    <span class="color-picker-btn__box h-40-px w-100 rounded-3"
                        style="background-color: #2563eb;"></span>
                    <span class="fw-medium mt-1" style="color: #2563eb;">Blue</span>
                </button>
                <button type="button"
                    class="color-picker-btn d-flex flex-column justify-content-center align-items-center"
                    data-color="yellow" aria-label="Yellow">
                    <span class="color-picker-btn__box h-40-px w-100 rounded-3"
                        style="background-color: #ff9f29;"></span>
                    <span class="fw-medium mt-1" style="color: #ff9f29;">Yellow</span>
                </button>
                <button type="button"
                    class="color-picker-btn d-flex flex-column justify-content-center align-items-center"
                    data-color="cyan" aria-label="Cyan">
                    <span class="color-picker-btn__box h-40-px w-100 rounded-3"
                        style="background-color: #00b8f2;"></span>
                    <span class="fw-medium mt-1" style="color: #00b8f2;">Cyan</span>
                </button>
                <button type="button"
                    class="color-picker-btn d-flex flex-column justify-content-center align-items-center"
                    data-color="violet" aria-label="Violet">
                    <span class="color-picker-btn__box h-40-px w-100 rounded-3"
                        style="background-color: #7c3aed;"></span>
                    <span class="fw-medium mt-1" style="color: #7c3aed;">Violet</span>
                </button>
            </div>
        </div>

    </div>
</div>
<!-- Theme Customization Structure End -->

  <div class="overlay bg-black bg-opacity-50 w-100 h-100 position-fixed z-9 visibility-hidden opacity-0 duration-300">
  </div>
<aside class="sidebar">
  <button type="button" class="sidebar-close-btn">
    <iconify-icon icon="radix-icons:cross-2"></iconify-icon>
  </button>
  <div class="">
    <div class="sidebar-logo d-flex align-items-center justify-content-between">
      <a href="{{ route('dashboard', [], false) }}" class="">
        <img src="{{asset('assets/images/logo.png')}}" alt="site logo" class="light-logo">
        <img src="{{asset('assets/images/logo-light.png')}}" alt="site logo" class="dark-logo">
        <img src="{{asset('assets/images/logo-icon.png')}}" alt="site logo" class="logo-icon">
      </a>
      <button type="button" class="text-xxl d-xl-flex d-none line-height-1 sidebar-toggle text-neutral-500"
        aria-label="Collapse Sidebar">
        <i class="ri-contract-left-line"></i>
      </button>
    </div>
  </div>
  <!-- User Info start -->
  <div class="mx-16 py-12 sidebar-profile-wrap">
    <div class="dropdown profile-dropdown">
      <button type="button"
        class="profile-dropdown__button d-flex align-items-center justify-content-between p-10 w-100 overflow-hidden bg-neutral-50 radius-12 "
        data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
        <span class="d-flex align-items-start gap-10">
          @php $navPhoto = auth()->user()->profilePhotoUrl(); @endphp
          @if($navPhoto)
            <img src="{{ $navPhoto }}" alt="{{ auth()->user()->name }}"
              class="w-40-px h-40-px rounded-circle object-fit-cover flex-shrink-0">
          @else
            <img src="{{ asset('assets/images/thumbs/leave-request-img2.png') }}" alt="Thumbnail"
              class="w-40-px h-40-px rounded-circle object-fit-cover flex-shrink-0">
          @endif
          <span class="profile-dropdown__contents">
             <span class="h6 mb-0 text-md d-block text-primary-light">{{auth()->user()->name}}</span>
            <span class="text-secondary-light text-sm mb-0 d-block">{{auth()->user()->getUserCategory()}}</span>
          </span>
        </span>
        <span class="profile-dropdown__icon pe-8 text-xl d-flex line-height-1">
          <i class="ri-arrow-right-s-line"></i>
        </span>
      </button>
      <ul class="dropdown-menu w-100 border p-12">
        <li>
          <a href="{{ route('profile', [], false) }}" 
            class="dropdown-item rounded text-secondary-light bg-hover-neutral-200 text-hover-neutral-900 d-flex align-items-center gap-2 py-6">
            <i class="ri-user-3-line"></i>
            My Profile
          </a>
        </li>
        <li>
          <a href="{{ route('profile', [], false) }}"
            class="dropdown-item rounded text-secondary-light bg-hover-neutral-200 text-hover-neutral-900 d-flex align-items-center gap-2 py-6">
            <i class="ri-settings-3-line"></i>
            Setting
          </a>
        </li>
        <li>
            <form action="{{ route('logout-authentication-process', [], false) }}" method="POST">
              @csrf
              <button type="submit"
                  class="dropdown-item rounded text-secondary-light bg-hover-neutral-200 text-hover-neutral-900 d-flex align-items-center gap-2 py-6">
                  <i class="ri-shut-down-line"></i>
                  Log Out
              </button>
            </form>
        </li>
      </ul>
    </div>
  </div>
  <!-- User Info end -->
  <div class="sidebar-menu-area">
    <ul class="sidebar-menu" id="sidebar-menu">
      <li>
        <a href="{{ route('dashboard', [], false) }}" class="@if ($pageName == 'dashboard') active-page active @endif">
          <i class="ri-home-4-line"></i>
          <span class="col menu-name">Dashboard</span>
        </a>
      </li>
       @foreach ($parents as $parent)
          @php
            $submenu = collect($child)->filter(fn ($sub) => $sub->link_parent == $parent->link_id)->values();
          @endphp
          @if ($submenu->isEmpty() && filled($parent->link_url) && $parent->link_url !== '#' && \Illuminate\Support\Facades\Route::has($parent->link_url))
      <li>
        <a href="{{ route($parent->link_url, [], false) }}" class="@if ($pageName == $parent->page_id) active-page active @endif">
          <i class="{{ $parent->link_image ?: 'ri-circle-line' }}"></i>
          <span>{{ $parent->link_name }}</span>
        </a>
      </li>
          @else
      <li class="dropdown @if ($pageName == $parent->page_id) active  @endif">
        <a href="javascript:void(0)">
          <i class="{{$parent->link_image}}"></i>
          <span   >{{$parent->link_name}}</span>
        </a>
        <ul class="sidebar-submenu">
           @foreach ($submenu as $sub)
           @continue(! \Illuminate\Support\Facades\Route::has($sub->link_url))
          <li>
            <a href="{{ route($sub->link_url, [], false) }}" class="@if ($subpageName == $sub->page_id_sub) active @endif">
              <i class="ri-circle-fill circle-icon w-auto"></i>
             {{ $sub->link_name}}
            </a>
          </li>
           @endforeach
        </ul>
      </li>
          @endif
       @endforeach
  
    </ul>
  </div>
</aside>

<main class="dashboard-main">
  <div class="navbar-header shadow-1">
  <div class="row align-items-center justify-content-between">
    <div class="col-auto">
      <div class="d-flex flex-wrap align-items-center gap-4">
        <button type="button" class="sidebar-mobile-toggle" aria-label="Sidebar Mobile Toggler Button">
          <iconify-icon icon="heroicons:bars-3-solid" class="icon"></iconify-icon>
        </button>
        <form class="navbar-search">
          <input type="text" class="bg-transparent" name="search" placeholder="Search">
          <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
        </form>
      </div>
    </div>
    <div class="col-auto">
      <div class="d-flex flex-wrap align-items-center gap-3">
        <button type="button" data-theme-toggle
          class="w-40-px h-40-px bg-neutral-200 rounded-circle d-flex justify-content-center align-items-center" aria-label="Dark & Light Mode Button"></button>
        <div class="dropdown d-inline-block">
          <button
            class="has-indicator w-40-px h-40-px bg-neutral-200 rounded-circle d-flex justify-content-center align-items-center"
            type="button" data-bs-toggle="dropdown" aria-label="Language Change Button">
            <img src="{{asset('assets/images/flags/ghana.png')}}" alt="image" class="w-24 h-24 object-fit-cover rounded-circle">
          </button>
          
        </div><!-- Language dropdown end -->

        <div class="dropdown">
          <button
            class="has-indicator w-40-px h-40-px bg-neutral-200 rounded-circle d-flex justify-content-center align-items-center position-relative"
            type="button" data-bs-toggle="dropdown" aria-label="Notification Button">
            <iconify-icon icon="iconoir:bell" class="text-primary-light text-xl"></iconify-icon>
            <span class="w-8-px h-8-px bg-danger-600 position-absolute end-0 top-0 rounded-circle mt-2 me-2"></span>
          </button>
          <div class="dropdown-menu to-top dropdown-menu-lg p-0">
            <div
              class="m-16 py-12 px-16 radius-8 bg-primary-50 mb-16 d-flex align-items-center justify-content-between gap-2">
              <div>
                <h6 class="text-lg text-primary-light fw-semibold mb-0">Notifications</h6>
              </div>
              <span
                class="text-primary-600 fw-semibold text-lg w-40-px h-40-px rounded-circle bg-base d-flex justify-content-center align-items-center">05</span>
            </div>

            <div class="max-h-400-px overflow-y-auto scroll-sm pe-4">
              <a href="javascript:void(0)"
                class="px-24 py-12 d-flex align-items-start gap-3 mb-2 justify-content-between">
                <div class="text-black hover-bg-transparent hover-text-primary d-flex align-items-center gap-3">
                  <span
                    class="w-44-px h-44-px bg-success-subtle text-success-main rounded-circle d-flex justify-content-center align-items-center flex-shrink-0">
                    <iconify-icon icon="bitcoin-icons:verify-outline" class="icon text-xxl"></iconify-icon>
                  </span>
                  <div>
                    <h6 class="text-md fw-semibold mb-4">Congratulations</h6>
                    <p class="mb-0 text-sm text-secondary-light text-w-200-px">Your profile has been Verified. Your
                      profile has been Verified</p>
                  </div>
                </div>
                <span class="text-sm text-secondary-light flex-shrink-0">23 Mins ago</span>
              </a>

              <a href="javascript:void(0)"
                class="px-24 py-12 d-flex align-items-start gap-3 mb-2 justify-content-between bg-neutral-50">
                <div class="text-black hover-bg-transparent hover-text-primary d-flex align-items-center gap-3">
                  <span
                    class="w-44-px h-44-px bg-success-subtle text-success-main rounded-circle d-flex justify-content-center align-items-center flex-shrink-0">
                    <img src="assets/images/notification/profile-1.png" alt="Image">
                  </span>
                  <div>
                    <h6 class="text-md fw-semibold mb-4">Ronald Richards</h6>
                    <p class="mb-0 text-sm text-secondary-light text-w-200-px">You can stitch between artboards</p>
                  </div>
                </div>
                <span class="text-sm text-secondary-light flex-shrink-0">23 Mins ago</span>
              </a>

              <a href="javascript:void(0)"
                class="px-24 py-12 d-flex align-items-start gap-3 mb-2 justify-content-between">
                <div class="text-black hover-bg-transparent hover-text-primary d-flex align-items-center gap-3">
                  <span
                    class="w-44-px h-44-px bg-info-subtle text-info-main rounded-circle d-flex justify-content-center align-items-center flex-shrink-0">
                    AM
                  </span>
                  <div>
                    <h6 class="text-md fw-semibold mb-4">Arlene McCoy</h6>
                    <p class="mb-0 text-sm text-secondary-light text-w-200-px">Invite you to prototyping</p>
                  </div>
                </div>
                <span class="text-sm text-secondary-light flex-shrink-0">23 Mins ago</span>
              </a>

              <a href="javascript:void(0)"
                class="px-24 py-12 d-flex align-items-start gap-3 mb-2 justify-content-between bg-neutral-50">
                <div class="text-black hover-bg-transparent hover-text-primary d-flex align-items-center gap-3">
                  <span
                    class="w-44-px h-44-px bg-success-subtle text-success-main rounded-circle d-flex justify-content-center align-items-center flex-shrink-0">
                    <img src="assets/images/notification/profile-2.png" alt="Image">
                  </span>
                  <div>
                    <h6 class="text-md fw-semibold mb-4">Robiul Hasan</h6>
                    <p class="mb-0 text-sm text-secondary-light text-w-200-px">Invite you to prototyping</p>
                  </div>
                </div>
                <span class="text-sm text-secondary-light flex-shrink-0">23 Mins ago</span>
              </a>

              <a href="javascript:void(0)"
                class="px-24 py-12 d-flex align-items-start gap-3 mb-2 justify-content-between">
                <div class="text-black hover-bg-transparent hover-text-primary d-flex align-items-center gap-3">
                  <span
                    class="w-44-px h-44-px bg-info-subtle text-info-main rounded-circle d-flex justify-content-center align-items-center flex-shrink-0">
                    DR
                  </span>
                  <div>
                    <h6 class="text-md fw-semibold mb-4">Darlene Robertson</h6>
                    <p class="mb-0 text-sm text-secondary-light text-w-200-px">Invite you to prototyping</p>
                  </div>
                </div>
                <span class="text-sm text-secondary-light flex-shrink-0">23 Mins ago</span>
              </a>
            </div>

            <div class="text-center py-12 px-16">
              <a href="javascript:void(0)" class="text-primary-600 fw-semibold text-md hover-underline">See All Notification</a>
            </div>

          </div>
        </div><!-- Notification dropdown end -->

      </div>
    </div>
  </div>
</div>



    @yield('content')
  

  <footer class="d-footer">
  <div class="">
    <p class="mb-0 text-center"> &copy; <span class="current-year"></span> Developed By Speedlines Technology.</p>
  </div>
</footer>
</main>

 
  <!-- jQuery library js -->
  <script src="{{asset('assets/js/lib/jquery-3.7.1.min.js')}}"></script>
  <!-- Bootstrap js -->
  <script src="{{asset('assets/js/lib/bootstrap.bundle.min.js')}}"></script>
  <!-- Apex Chart js -->
  <script src="{{asset('assets/js/lib/apexcharts.min.js')}}"></script>
  <!-- Iconify Font js -->
  <script src="{{asset('assets/js/lib/iconify-icon.min.js')}}"></script>
  <!-- Data Table js -->
  <script src="{{asset('assets/js/lib/dataTables.min.js')}}"></script>
  
  <!-- jQuery UI js -->
  <script src="{{asset('assets/js/lib/jquery-ui.min.js')}}"></script>
  
  <!-- main js -->
  <script src="{{asset('assets/js/app.js')}}"></script>
  <script src="{{asset('assets/js/lib/sweetalert2.all.min.js')}}"></script>
  <script src="{{asset('assets/js/flatpickr.js')}}"></script>

  <script>
        $(document).ready(function () {
            if (typeof flatpickr !== 'undefined') {
                $('.datepicker').flatpickr({
                    dateFormat: 'Y-m-d',
                    allowInput: true
                });
            }
        });
</script>

<script>
  // ============================ Revenue Statistics Chart start ===============================
  var options = {
    series: [{
      name: 'Total Fee',
      data: [25, 35, 50, 60, 26, 20, 40, 20, 50, 16, 10, 40]
    }, {
      name: 'Collected Fee',
      data: [15, 16, 24, 30, 20, 15, 20, 10, 25, 10, 6, 20]
    }],
    chart: {
      type: 'bar',
      height: 250,
      stacked: true,
      toolbar: {
        show: false
      },
      zoom: {
        enabled: true
      }
    },
    colors: ["#25A194", "#FF7A2C"],
    plotOptions: {
      bar: {
        horizontal: false,
        columnWidth: "50%",
        shape: "pyramid",
      },
    },
    xaxis: {
      categories: ['Jan', 'Feb', 'Mar', 'Apr',
        'May', 'June', 'July', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
      ],
    },
    yaxis: {
      labels: {
        formatter: function (value) {
          return "$" + value + "k";
        },
        style: {
          fontSize: "14px"
        }
      },
    },
    legend: {
      show: false,
    },
    fill: {
      opacity: 1
    }
  };

  var revenueEl = document.querySelector("#revenueStatistic");
  if (revenueEl) {
    var chart = new ApexCharts(revenueEl, options);
    chart.render();
  }
  // ============================ Revenue Statistics Chart End ===============================

  // ===================== Income Vs Expense Start =============================== 
  function createChartThree(chartId, color1, color2) {
    var options = {
      series: [{
        name: 'Income',
        data: [48, 35, 55, 32, 48, 30, 15, 50, 57]
      }, {
        name: 'Expense',
        data: [12, 20, 15, 26, 22, 60, 40, 32, 25]
      }],
      legend: {
        show: false
      },
      chart: {
        type: 'area',
        width: '100%',
        height: 260,
        toolbar: {
          show: false
        },
        padding: {
          left: 0,
          right: 0,
          top: 0,
          bottom: 0
        }
      },
      dataLabels: {
        enabled: false
      },
      stroke: {
        curve: 'stepline',
        width: 2,
        colors: [color1, color2],
        lineCap: 'round'
      },
      grid: {
        show: true,
        borderColor: '#D1D5DB',
        strokeDashArray: 1,
        position: 'back',
        xaxis: {
          lines: {
            show: false
          }
        },
        yaxis: {
          lines: {
            show: true
          }
        },
        row: {
          colors: undefined,
          opacity: 0.2
        },
        column: {
          colors: undefined,
          opacity: 0.2
        },
        padding: {
          top: -20,
          right: 0,
          bottom: -10,
          left: 0
        },
      },
      colors: [color1, color2],
      markers: {
        colors: [color1, color2],
        strokeWidth: 1,
        size: 0,
        hover: {
          size: 10
        }
      },
      xaxis: {
        labels: {
          show: false
        },
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        tooltip: {
          enabled: false
        },
        labels: {
          formatter: function (value) {
            return value;
          },
          style: {
            fontSize: "14px"
          }
        }
      },
      yaxis: {
        labels: {
          formatter: function (value) {
            return "$" + value + "k";
          },
          style: {
            fontSize: "14px"
          }
        },
      },
      tooltip: {
        x: {
          format: 'dd/MM/yy HH:mm'
        }
      },
      fill: {
        type: "gradient",
        gradient: {
          shade: "light",
          type: "vertical",
          opacityFrom: 0.4,
          opacityTo: 0.05,
          stops: [0, 100]
        }
      }
    };

    const incomeEl = document.querySelector(`#${chartId}`);
    if (!incomeEl) {
      return;
    }
    var chart = new ApexCharts(incomeEl, options);
    chart.render();
  }

  createChartThree('incomeExpense', '#16a34a', '#FF9F29');
  // ===================== Income Vs Expense End =============================== 

  // ================================ New Admissions Chart Start ================================ 
  var options = {
    series: [40, 87, 87, 30],
    colors: ['#0A51CE', '#25A194', '#FF7A2C', '#009F5E'],
    labels: ['Health', 'Business', 'Lifestyle', 'Entertainment'],
    legend: {
      show: false
    },
    chart: {
      type: 'donut',
      height: 270,
      sparkline: {
        enabled: true // Remove whitespace
      },
      margin: {
        top: 0,
        right: 0,
        bottom: 0,
        left: 0
      },
      padding: {
        top: 0,
        right: 0,
        bottom: 0,
        left: 0
      }
    },
    stroke: {
      width: 2,
    },
    dataLabels: {
      enabled: false
    },
    responsive: [{
      breakpoint: 480,
      options: {
        chart: {
          width: 200
        },
        legend: {
          position: 'bottom'
        }
      }
    }],
  };

  var admissionsEl = document.querySelector("#newAdmissions");
  if (admissionsEl) {
    var chart = new ApexCharts(admissionsEl, options);
    chart.render();
  }
  // ================================ New Admissions Chart End ================================ 

  // ================================ Animated Radial Progress Bar Start ================================ 
  $('svg.radial-progress').each(function (index, value) {
    $(this).find($('circle.complete')).removeAttr('style');
  });

  // Activate progress animation on scroll
  $(window).scroll(function () {
    $('svg.radial-progress').each(function (index, value) {
      // Trigger when the element is fully in the viewport
      if (
        $(window).scrollTop() >= $(this).offset().top - $(window).height() &&
        $(window).scrollTop() <= $(this).offset().top + $(this).height()
      ) {
        // Get percentage of progress
        const percent = $(value).data('percentage');
        // Get radius of the svg's circle.complete
        const radius = $(this).find($('circle.complete')).attr('r');
        // Get circumference (2πr)
        const circumference = 2 * Math.PI * radius;
        // Get stroke-dashoffset value based on the percentage of the circumference
        const strokeDashOffset = circumference - ((percent * circumference) / 100);
        // Transition progress for 1.25 seconds
        $(this).find($('circle.complete')).animate({ 'stroke-dashoffset': strokeDashOffset }, 1250);
      }
    });
  }).trigger('scroll');
  // ================================ Animated Radial Progress Bar End ================================

  // ============================= Calendar Js Start =================================
  let display = document.querySelector(".display");
  let days = document.querySelector(".days");
  let previous = document.querySelector(".left");
  let next = document.querySelector(".right");

  let date = new Date();

  let year = date.getFullYear();
  let month = date.getMonth();

  function displayCalendar() {
    const firstDay = new Date(year, month, 1);

    const lastDay = new Date(year, month + 1, 0);

    const firstDayIndex = firstDay.getDay(); //4

    const numberOfDays = lastDay.getDate(); //31

    let formattedDate = date.toLocaleString("en-US", {
      month: "long",
      year: "numeric"
    });

    display.innerHTML = `${formattedDate}`;

    for (let x = 1; x <= firstDayIndex; x++) {
      const div = document.createElement("div");
      div.innerHTML += "";

      days.appendChild(div);
    }

    for (let i = 1; i <= numberOfDays; i++) {
      let div = document.createElement("div");
      let currentDate = new Date(year, month, i);

      div.dataset.date = currentDate.toDateString();

      div.innerHTML += i;
      days.appendChild(div);
      if (
        currentDate.getFullYear() === new Date().getFullYear() &&
        currentDate.getMonth() === new Date().getMonth() &&
        currentDate.getDate() === new Date().getDate()
      ) {
        div.classList.add("current-date");
      }
    }
  }

  // Call the function to display the calendar
  displayCalendar();

  previous.addEventListener("click", () => {
    days.innerHTML = "";

    if (month < 0) {
      month = 11;
      year = year - 1;
    }
    month = month - 1;
    date.setMonth(month);
    displayCalendar();
  });

  next.addEventListener("click", () => {
    days.innerHTML = "";

    if (month > 11) {
      month = 0;
      year = year + 1;
    }

    month = month + 1;
    date.setMonth(month);

    displayCalendar();
  });
  // ============================= Calendar Js End =================================

</script>

 <script>
    const AppToast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 4500,
        timerProgressBar: true,
        width: 'auto',
        customClass: {
            popup: 'app-toast',
        },
        didOpen: function (toast) {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    window.showAppToast = function (type, message) {
        if (!message) {
            return;
        }

        Swal.close();

        let isSuccess = type === 'success';
        let heading = isSuccess ? 'Success' : 'Error';
        let iconClass = isSuccess ? 'ri-checkbox-circle-fill' : 'ri-error-warning-fill';
        let safeMessage = $('<div>').text(message).html();

        AppToast.fire({
            customClass: {
                popup: 'app-toast',
                timerProgressBar: isSuccess ? 'swal-timer-success' : 'swal-timer-error',
            },
            html: `
                <div class="app-toast-wrap ${isSuccess ? '' : 'is-error'}">
                    <div class="app-toast-icon ${isSuccess ? 'is-success' : 'is-error'}">
                        <i class="${iconClass}"></i>
                    </div>
                    <div class="app-toast-body">
                        <div class="app-toast-heading">${heading}</div>
                        <div class="app-toast-message">${safeMessage}</div>
                    </div>
                </div>
            `
        });
    };

    window.confirmAppAction = function (options) {
        const opts = typeof options === 'string' ? { text: options } : (options || {});

        if (typeof Swal === 'undefined') {
            return Promise.resolve(window.confirm([opts.title, opts.text].filter(Boolean).join('\n')));
        }

        return Swal.fire({
            title: opts.title || 'Are you sure?',
            text: opts.text || '',
            icon: opts.icon || 'warning',
            showCancelButton: true,
            focusCancel: true,
            reverseButtons: true,
            confirmButtonColor: opts.confirmButtonColor || '#E5484D',
            cancelButtonColor: '#64748b',
            confirmButtonText: opts.confirmButtonText || 'Yes, delete',
            cancelButtonText: opts.cancelButtonText || 'Cancel',
            customClass: {
                popup: 'app-confirm-swal',
            },
        }).then(function (result) {
            return result.isConfirmed;
        });
    };

    document.addEventListener('submit', function (event) {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-confirm')) {
            return;
        }
        if (form.dataset.confirmed === '1') {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        window.confirmAppAction({
            title: form.getAttribute('data-confirm-title') || 'Are you sure?',
            text: form.getAttribute('data-confirm') || 'This cannot be undone.',
            confirmButtonText: form.getAttribute('data-confirm-button') || 'Yes, delete',
        }).then(function (ok) {
            if (!ok) {
                return;
            }
            form.dataset.confirmed = '1';
            HTMLFormElement.prototype.submit.call(form);
        });
    }, true);

    @if(session('message_success'))
        showAppToast('success', @json(session('message_success')));
    @endif

    @if(session('message_error'))
        showAppToast('error', @json(session('message_error')));
    @endif
</script>
<script>
    $('.data-table').each(function () {
        const $table = $(this);
        const options = {
            layout: {
                topStart: null,
                topEnd: null,
            },
        };

        if ($table.hasClass('data-table-scroll-x')) {
            options.scrollX = true;
            options.autoWidth = false;
        }

        const pageLength = parseInt($table.attr('data-page-length'), 10);
        if (pageLength) {
            options.pageLength = pageLength;
        }

        const tableInstance = new DataTable(this, options);
        const $wrapper = $table.closest('.dataTable-wrapper');
        const $scope = $wrapper.add($wrapper.parent());
        const $searchInputs = $scope.find('.dt-search .dt-input').not('.dt-container .dt-search .dt-input');
        const $lengthInputs = $scope.find('.dt-length .dt-input, .dt-length select').not('.dt-container .dt-length select');

        $searchInputs.on('keyup input', function () {
            tableInstance.search(this.value).draw();
        });

        $searchInputs.closest('form').on('submit', function (e) {
            e.preventDefault();
            tableInstance.search($searchInputs.first().val()).draw();
        });

        $lengthInputs.on('change', function () {
            tableInstance.page.len($(this).val()).draw();
        });
    });
    // ✅ Data Table end
</script>

@yield('scripts')
</body>

</html>