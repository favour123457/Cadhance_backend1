<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
    <title>@yield('title', 'Admin') | {{ config('app.name') }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('') }}img/logo.png">

    <!-- Loader CSS -->
    <link href="{{ asset('') }}layouts/vertical-dark-menu/css/light/loader.css" rel="stylesheet" type="text/css">
    <link href="{{ asset('') }}layouts/vertical-dark-menu/css/dark/loader.css" rel="stylesheet" type="text/css">
    <script src="{{ asset('') }}layouts/vertical-dark-menu/loader.js"></script>

    <!-- Global Mandatory Styles -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:400,600,700" rel="stylesheet">
    <link href="{{ asset('') }}src/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="{{ asset('') }}layouts/vertical-dark-menu/css/light/plugins.css" rel="stylesheet" type="text/css">
    <link href="{{ asset('') }}layouts/vertical-dark-menu/css/dark/plugins.css" rel="stylesheet" type="text/css">

    <!-- ApexCharts -->
    <link href="{{ asset('') }}src/plugins/src/apex/apexcharts.css" rel="stylesheet" type="text/css">

    <!-- DataTables -->
    <link href="{{ asset('') }}src/plugins/src/table/datatable/datatables.css" rel="stylesheet" type="text/css">
    <link href="{{ asset('') }}src/plugins/src/table/datatable/dt-global_style.css" rel="stylesheet"
        type="text/css">

    @yield('head')

    <style>
        /* --- Sidebar scroll fix ---
           .sidebar-wrapper is top:107px with height:100vh, so it extends 107px beyond
           the viewport bottom. The .ps scroll container inherits the wrong height,
           pushing the scrollbar and bottom items out of view.
        */
        .sidebar-wrapper,
        body.dark .sidebar-wrapper {
            height: calc(100vh - 107px) !important;
        }

        #sidebar ul.menu-categories,
        body.dark #sidebar ul.menu-categories {
            /* 107px header + 71px brand/logo area inside sidebar */
            /* Must target WITHOUT .ps so the height is set BEFORE PerfectScrollbar
               initializes — PS reads clientHeight on init, then adds .ps class.
               If we targeted .menu-categories.ps the height would change AFTER PS
               already measured "no scrolling needed" and items past OTPs would be
               clipped and unreachable. */
            height: calc(100vh - 178px) !important;
            overflow: hidden !important;
        }

        /* --- Flash notification fix ---
           php-flasher uses z-index:1000 but the fixed header is z-index:1032,
           so notifications appear under the header. Raise z-index and add
           a top offset so top-positioned toasts clear the navbar.
        */
        .fl-wrapper {
            z-index: 1033 !important;
        }

        .fl-wrapper[data-position^="top-"] {
            top: 115px !important;
        }

        /* --- Header/content gap fix ---
           .page-meta has margin-top:25px in main.css, creating visible gap
           between the fixed header and the first content in every view.
        */
        .page-meta {
            margin-top: 8px !important;
        }
    </style>
</head>

<body>
    <!-- BEGIN LOADER -->
    <div id="load_screen">
        <div class="loader">
            <div class="loader-content">
                <div class="spinner-grow align-self-center"></div>
            </div>
        </div>
    </div>
    <!-- END LOADER -->

    <!-- BEGIN NAVBAR -->
    <div class="header-container container-xxl">
        <header class="header navbar navbar-expand-sm expand-header">

            <ul class="navbar-item theme-brand flex-row text-center">
                <li class="nav-item theme-logo">
                    <a href="{{ route('dashboard') }}">
                        <img  class="navbar-logo" alt="logo" src="{{ asset('img/logo-white.png') }}"
                            style="height:32px;">
                    </a>
                </li>
                <li class="nav-item theme-text">
                    <a href="{{ route('dashboard') }}" class="nav-link"> {{ config('app.name') }}</a>
                </li>
            </ul>

            {{-- <div class="search-animated toggle-search">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="feather feather-search">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <form class="form-inline search-full form-inline search" role="search">
                    <div class="search-bar">
                        <input type="text" class="form-control search-form-control ml-lg-auto"
                            placeholder="Search...">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="feather feather-x search-close">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </div>
                </form>
            </div> --}}

            <ul class="navbar-item flex-row ms-lg-auto ms-0 action-area">

                <li class="nav-item theme-toggle-item">
                    <a href="javascript:void(0);" class="nav-link theme-toggle">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="feather feather-moon dark-mode">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="feather feather-sun light-mode">
                            <circle cx="12" cy="12" r="5"></circle>
                            <line x1="12" y1="1" x2="12" y2="3"></line>
                            <line x1="12" y1="21" x2="12" y2="23"></line>
                            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                            <line x1="1" y1="12" x2="3" y2="12"></line>
                            <line x1="21" y1="12" x2="23" y2="12"></line>
                            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                        </svg>
                    </a>
                </li>

                <li class="nav-item dropdown notification-dropdown">
                    <a href="javascript:void(0);" class="nav-link dropdown-toggle" id="notificationDropdown"
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="feather feather-bell">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                        <span class="badge badge-success"></span>
                    </a>
                    <div class="dropdown-menu position-absolute" aria-labelledby="notificationDropdown">
                        <div class="drodpown-title notification">
                            <h6 class="d-flex justify-content-between">
                                <span class="align-self-center">Notifications</span>
                            </h6>
                        </div>
                        <div class="notification-scroll">
                            <div class="dropdown-item">
                                <div class="media server-log">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-check-circle" style="color:#4CAF50">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                    </svg>
                                    <div class="media-body">
                                        <div class="data-info">
                                            <h6>Admin Panel</h6>
                                            <p>Welcome back!</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>

                <li class="nav-item dropdown user-profile-dropdown order-lg-0 order-1">
                    <a href="javascript:void(0);" class="nav-link dropdown-toggle user" id="userProfileDropdown"
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <div class="avatar-container">
                            <div class="avatar avatar-sm avatar-indicators avatar-online">
                                <img alt="avatar" src="{{showImage(auth()->user()->profile_picture)}}"
                                    class="rounded-circle">
                            </div>
                        </div>
                    </a>
                    <div class="dropdown-menu position-absolute" aria-labelledby="userProfileDropdown">
                        <div class="user-profile-section">
                            <div class="media mx-auto">
                                <div class="emoji me-2">&#x1F44B;</div>
                                <div class="media-body">
                                    <h5>{{ auth()->user()->first_name }}</h5>
                                    <p>{{ auth()->user()->role->name ?? 'Admin' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="dropdown-item">
                            <a href="{{ route('users.edit', auth()->user()->id) }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" class="feather feather-user">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                <span>My Profile</span>
                            </a>
                        </div>
                        <div class="dropdown-item">
                            <a href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" class="feather feather-log-out">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                    <polyline points="16 17 21 12 16 7"></polyline>
                                    <line x1="21" y1="12" x2="9" y2="12"></line>
                                </svg>
                                <span>Log Out</span>
                            </a>
                        </div>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST"
                            style="display: none;">@csrf</form>
                    </div>
                </li>
            </ul>
        </header>
    </div>
    <!-- END NAVBAR -->

    <!-- BEGIN MAIN CONTAINER -->
    <div class="main-container" id="container">

        <div class="overlay"></div>
        <div class="search-overlay"></div>

        <!-- BEGIN SIDEBAR -->
        <div class="sidebar-wrapper sidebar-theme">
            <nav id="sidebar">
                <div class="navbar-nav theme-brand flex-row text-center">
                    <div class="nav-logo">
                        <div class="nav-item theme-logo">
                            <a href="{{ route('dashboard') }}">
                                <img src="{{ asset('') }}img/logo.png" class="navbar-logo" alt="logo"
                                    style="height:28px;">
                            </a>
                        </div>
                        <div class="nav-item theme-text">
                            <a href="{{ route('dashboard') }}" class="nav-link"> {{ config('app.name') }} </a>
                        </div>
                    </div>
                    <div class="nav-item sidebar-toggle">
                        <div class="btn-toggle sidebarCollapse">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevrons-left">
                                <polyline points="11 17 6 12 11 7"></polyline>
                                <polyline points="18 17 13 12 18 7"></polyline>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="shadow-bottom"></div>

                <ul class="list-unstyled menu-categories" id="accordionExample">

                    <!-- MAIN -->
                    <li class="menu {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <a href="{{ route('dashboard') }}" aria-expanded="false" class="dropdown-toggle">
                            <div class="">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" class="feather feather-home">
                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                                </svg>
                                <span>Dashboard</span>
                            </div>
                        </a>
                    </li>

                    <!-- USER MANAGEMENT -->
                    <li class="menu menu-heading">
                        <div class="heading">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" class="feather feather-minus">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            <span>USER MANAGEMENT</span>
                        </div>
                    </li>

                    @if (checkPermission('users'))
                        <li class="menu {{ request()->routeIs('users.*') ? 'active' : '' }}">
                            <a href="{{ route('users.index') }}" aria-expanded="false" class="dropdown-toggle">
                                <div class="">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" class="feather feather-users">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="9" cy="7" r="4"></circle>
                                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                    </svg>
                                    <span>Users</span>
                                </div>
                            </a>
                        </li>
                    @endif

                    @if (checkPermission('deleted-users'))
                        <li class="menu {{ request()->routeIs('deleted-users.*') ? 'active' : '' }}">
                            <a href="{{ route('deleted-users.index') }}" aria-expanded="false"
                                class="dropdown-toggle">
                                <div class="">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-user-x">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="8.5" cy="7" r="4"></circle>
                                        <line x1="18" y1="8" x2="23" y2="13"></line>
                                        <line x1="23" y1="8" x2="18" y2="13"></line>
                                    </svg>
                                    <span>Deleted Users</span>
                                </div>
                            </a>
                        </li>
                    @endif

                    <!-- ASSETS & TEMPLATES -->
                    <li class="menu menu-heading">
                        <div class="heading">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" class="feather feather-minus">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            <span>ASSETS & TEMPLATES</span>
                        </div>
                    </li>

                    @if (checkPermission('assets'))
                        <li class="menu {{ request()->routeIs('assets.*') ? 'active' : '' }}">
                            <a href="{{ route('assets.index') }}" aria-expanded="false" class="dropdown-toggle">
                                <div class="">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-package">
                                        <line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line>
                                        <path
                                            d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z">
                                        </path>
                                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                        <line x1="12" y1="22.08" x2="12" y2="12"></line>
                                    </svg>
                                    <span>Assets</span>
                                </div>
                            </a>
                        </li>
                    @endif

                    @if (checkPermission('templates'))
                        <li class="menu {{ request()->routeIs('templates.*') ? 'active' : '' }}">
                            <a href="{{ route('templates.index') }}" aria-expanded="false" class="dropdown-toggle">
                                <div class="">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-layout">
                                        <rect x="3" y="3" width="18" height="18" rx="2"
                                            ry="2"></rect>
                                        <line x1="3" y1="9" x2="21" y2="9"></line>
                                        <line x1="9" y1="21" x2="9" y2="9"></line>
                                    </svg>
                                    <span>Templates</span>
                                </div>
                            </a>
                        </li>
                    @endif

                    <!-- CUSTOMIZATIONS -->
                    <li class="menu menu-heading">
                        <div class="heading">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" class="feather feather-minus">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            <span>CUSTOMIZATIONS</span>
                        </div>
                    </li>

                    @if (checkPermission('customization-requests'))
                        <li class="menu {{ request()->routeIs('customization-requests.*') ? 'active' : '' }}">
                            <a href="{{ route('customization-requests.index') }}" aria-expanded="false"
                                class="dropdown-toggle">
                                <div class="">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-edit-3">
                                        <path d="M12 20h9"></path>
                                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                    </svg>
                                    <span>Customization Requests</span>
                                </div>
                            </a>
                        </li>
                    @endif

                    @if (checkPermission('escrows'))
                        <li class="menu {{ request()->routeIs('escrows.*') ? 'active' : '' }}">
                            <a href="{{ route('escrows.index') }}" aria-expanded="false" class="dropdown-toggle">
                                <div class="">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-shield">
                                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                    </svg>
                                    <span>Escrows</span>
                                </div>
                            </a>
                        </li>
                    @endif

                    <!-- SUBSCRIPTIONS & GROUPS -->
                    <li class="menu menu-heading">
                        <div class="heading">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" class="feather feather-minus">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            <span>SUBSCRIPTIONS & GROUPS</span>
                        </div>
                    </li>

                    @if (checkPermission('subscription-plans'))
                        <li class="menu {{ request()->routeIs('subscription-plans.*') ? 'active' : '' }}">
                            <a href="{{ route('subscription-plans.index') }}" aria-expanded="false"
                                class="dropdown-toggle">
                                <div class="">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-credit-card">
                                        <rect x="1" y="4" width="22" height="16" rx="2"
                                            ry="2"></rect>
                                        <line x1="1" y1="10" x2="23" y2="10"></line>
                                    </svg>
                                    <span>Subscription Plans</span>
                                </div>
                            </a>
                        </li>
                    @endif

                    @if (checkPermission('groups'))
                        <li class="menu {{ request()->routeIs('groups.*') ? 'active' : '' }}">
                            <a href="{{ route('groups.index') }}" aria-expanded="false" class="dropdown-toggle">
                                <div class="">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" class="feather feather-grid">
                                        <rect x="3" y="3" width="7" height="7"></rect>
                                        <rect x="14" y="3" width="7" height="7"></rect>
                                        <rect x="14" y="14" width="7" height="7"></rect>
                                        <rect x="3" y="14" width="7" height="7"></rect>
                                    </svg>
                                    <span>Groups</span>
                                </div>
                            </a>
                        </li>
                    @endif

                    <!-- JOBS -->
                    <li class="menu menu-heading">
                        <div class="heading">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" class="feather feather-minus">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            <span>JOBS</span>
                        </div>
                    </li>

                    @if (checkPermission('site-jobs'))
                        <li class="menu {{ request()->routeIs('site-jobs.*') ? 'active' : '' }}">
                            <a href="{{ route('site-jobs.index') }}" aria-expanded="false" class="dropdown-toggle">
                                <div class="">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-briefcase">
                                        <rect x="2" y="7" width="20" height="14" rx="2"
                                            ry="2"></rect>
                                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                    </svg>
                                    <span>Site Jobs</span>
                                </div>
                            </a>
                        </li>
                    @endif

                    <!-- FINANCE -->
                    <li class="menu menu-heading">
                        <div class="heading">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" class="feather feather-minus">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            <span>FINANCE</span>
                        </div>
                    </li>

                    @if (checkPermission('wallets'))
                        <li class="menu {{ request()->routeIs('wallets.*') ? 'active' : '' }}">
                            <a href="{{ route('wallets.index') }}" aria-expanded="false" class="dropdown-toggle">
                                <div class="">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-dollar-sign">
                                        <line x1="12" y1="1" x2="12" y2="23"></line>
                                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                    </svg>
                                    <span>Wallets</span>
                                </div>
                            </a>
                        </li>
                    @endif

                    @if (checkPermission('wallet-histories'))
                        <li class="menu {{ request()->routeIs('wallet-histories.*') ? 'active' : '' }}">
                            <a href="{{ route('wallet-histories.index') }}" aria-expanded="false"
                                class="dropdown-toggle">
                                <div class="">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-activity">
                                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                                    </svg>
                                    <span>Wallet Histories</span>
                                </div>
                            </a>
                        </li>
                    @endif

                    <li class="menu {{ request()->routeIs('withdrawals.*') ? 'active' : '' }}">
                        <a href="{{ route('withdrawals.index') }}" aria-expanded="false" class="dropdown-toggle">
                            <div class="">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="feather feather-credit-card">
                                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                    <line x1="1" y1="10" x2="23" y2="10"></line>
                                </svg>
                                <span>Withdrawals</span>
                            </div>
                        </a>
                    </li>

                    <!-- OTHERS -->
                    <li class="menu menu-heading">
                        <div class="heading">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" class="feather feather-minus">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            <span>OTHERS</span>
                        </div>
                    </li>

                    @if (checkPermission('contacts'))
                        <li class="menu {{ request()->routeIs('contacts.*') ? 'active' : '' }}">
                            <a href="{{ route('contacts.index') }}" aria-expanded="false" class="dropdown-toggle">
                                <div class="">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-message-square">
                                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                    </svg>
                                    <span>Contact Messages</span>
                                </div>
                            </a>
                        </li>
                    @endif

                    @if (checkPermission('faqs'))
                        <li class="menu {{ request()->routeIs('faqs.*') ? 'active' : '' }}">
                            <a href="{{ route('faqs.index') }}" aria-expanded="false" class="dropdown-toggle">
                                <div class="">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-help-circle">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                    </svg>
                                    <span>FAQs</span>
                                </div>
                            </a>
                        </li>
                    @endif

                    @if (checkPermission('notes'))
                        <li class="menu {{ request()->routeIs('notes.*') ? 'active' : '' }}">
                            <a href="{{ route('notes.index') }}" aria-expanded="false" class="dropdown-toggle">
                                <div class="">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-file-text">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <line x1="16" y1="13" x2="8" y2="13"></line>
                                        <line x1="16" y1="17" x2="8" y2="17"></line>
                                        <polyline points="10 9 9 9 8 9"></polyline>
                                    </svg>
                                    <span>Notes</span>
                                </div>
                            </a>
                        </li>
                    @endif

                    @if (checkPermission('notifications'))
                        <li class="menu {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                            <a href="{{ route('notifications.index') }}" aria-expanded="false"
                                class="dropdown-toggle">
                                <div class="">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" class="feather feather-bell">
                                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                                    </svg>
                                    <span>Notifications</span>
                                </div>
                            </a>
                        </li>
                    @endif

                    @if (checkPermission('otps'))
                        <li class="menu {{ request()->routeIs('otps.*') ? 'active' : '' }}">
                            <a href="{{ route('otps.index') }}" aria-expanded="false" class="dropdown-toggle">
                                <div class="">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" class="feather feather-key">
                                        <path
                                            d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4">
                                        </path>
                                    </svg>
                                    <span>OTPs</span>
                                </div>
                            </a>
                        </li>
                    @endif

                    @if (checkPermission('countries'))
                        <li class="menu {{ request()->routeIs('countries.*') ? 'active' : '' }}">
                            <a href="{{ route('countries.index') }}" aria-expanded="false" class="dropdown-toggle">
                                <div class="">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" class="feather feather-globe">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="2" y1="12" x2="22" y2="12"></line>
                                        <path
                                            d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z">
                                        </path>
                                    </svg>
                                    <span>Countries</span>
                                </div>
                            </a>
                        </li>
                    @endif
                    {{-- currency --}}
                    @if (checkPermission('currencies'))
                        <li class="menu {{ request()->routeIs('currencies.*') ? 'active' : '' }}">
                            <a href="{{ route('currencies.index') }}" aria-expanded="false" class="dropdown-toggle">
                                <div class="">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-dollar-sign">
                                        <line x1="12" y1="1" x2="12" y2="23"></line>
                                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                    </svg>
                                    <span>Currencies</span>
                                </div>
                            </a>
                        </li>
                    @endif

                    <!-- SETTINGS -->
                    <li class="menu menu-heading">
                        <div class="heading">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" class="feather feather-minus">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            <span>SETTINGS</span>
                        </div>
                    </li>

                    @if (checkPermission('roles'))
                        <li class="menu {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                            <a href="{{ route('roles.index') }}" aria-expanded="false" class="dropdown-toggle">
                                <div class="">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-settings">
                                        <circle cx="12" cy="12" r="3"></circle>
                                        <path
                                            d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z">
                                        </path>
                                    </svg>
                                    <span>Roles</span>
                                </div>
                            </a>
                        </li>
                    @endif

                    @if (checkPermission('all-general-settings'))
                        <li class="menu {{ request()->routeIs('all-general-settings.*') ? 'active' : '' }}">
                            <a href="{{ route('all-general-settings.index') }}" aria-expanded="false"
                                class="dropdown-toggle">
                                <div class="">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-sliders">
                                        <line x1="4" y1="21" x2="4" y2="14"></line>
                                        <line x1="4" y1="6" x2="4" y2="3"></line>
                                        <line x1="12" y1="21" x2="12" y2="12"></line>
                                        <line x1="12" y1="6" x2="12" y2="3"></line>
                                        <line x1="20" y1="21" x2="20" y2="16"></line>
                                        <line x1="20" y1="10" x2="20" y2="3"></line>
                                        <line x1="1" y1="14" x2="7" y2="14"></line>
                                        <line x1="9" y1="12" x2="15" y2="12"></line>
                                        <line x1="17" y1="16" x2="23" y2="16"></line>
                                    </svg>
                                    <span>All General Settings</span>
                                </div>
                            </a>
                        </li>
                    @endif

                    @if (checkPermission('settings'))
                        <li class="menu {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                            <a href="#systemSettings" data-bs-toggle="collapse" aria-expanded="false"
                                class="dropdown-toggle">
                                <div class="">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-sliders">
                                        <line x1="4" y1="21" x2="4" y2="14"></line>
                                        <line x1="4" y1="6" x2="4" y2="3"></line>
                                        <line x1="12" y1="21" x2="12" y2="12"></line>
                                        <line x1="12" y1="6" x2="12" y2="3"></line>
                                        <line x1="20" y1="21" x2="20" y2="16"></line>
                                        <line x1="20" y1="10" x2="20" y2="3"></line>
                                        <line x1="1" y1="14" x2="7" y2="14"></line>
                                        <line x1="9" y1="12" x2="15" y2="12"></line>
                                        <line x1="17" y1="16" x2="23" y2="16"></line>
                                    </svg>
                                    <span>System Settings</span>
                                </div>
                                <div>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-chevron-right">
                                        <polyline points="9 18 15 12 9 6"></polyline>
                                    </svg>
                                </div>
                            </a>
                            <ul class="collapse submenu list-unstyled" id="systemSettings"
                                data-bs-parent="#accordionExample">
                                @if (allSettings()->count() > 0)
                                    @foreach (allSettings() as $setting)
                                        <li>
                                            <a
                                                href="{{ route('settings.index', $setting->slug) }}">{{ $setting->name }}</a>
                                        </li>
                                    @endforeach
                                @endif
                            </ul>
                        </li>
                    @endif

                    @if (checkPermission('admin-datas'))
                        <li class="menu {{ request()->routeIs('admin-datas.*') ? 'active' : '' }}">
                            <a href="{{ route('admin-datas.index') }}" aria-expanded="false"
                                class="dropdown-toggle">
                                <div class="">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" class="feather feather-tool">
                                        <path
                                            d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z">
                                        </path>
                                    </svg>
                                    <span>Admin Data</span>
                                </div>
                            </a>
                        </li>
                    @endif

                    <li class="menu">
                        <a href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('sidebar-logout-form').submit();"
                            aria-expanded="false" class="dropdown-toggle">
                            <div class="">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" class="feather feather-log-out">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                    <polyline points="16 17 21 12 16 7"></polyline>
                                    <line x1="21" y1="12" x2="9" y2="12"></line>
                                </svg>
                                <span>Logout</span>
                            </div>
                        </a>
                        <form id="sidebar-logout-form" action="{{ route('logout') }}" method="POST"
                            style="display: none;">@csrf</form>
                    </li>

                </ul>
            </nav>
        </div>
        <!-- END SIDEBAR -->

        <!-- BEGIN CONTENT AREA -->
        <div id="content" class="main-content">
            <div class="layout-px-spacing">
                <div class="middle-content container-xxl p-0">

                    <!--  BEGIN BREADCRUMBS  -->
                    <div class="secondary-nav">
                        <div class="breadcrumbs-container" data-page-heading="Analytics">
                            <header class="header navbar navbar-expand-sm">
                                <a href="javascript:void(0);" class="btn-toggle sidebarCollapse"
                                    data-placement="bottom">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" class="feather feather-menu">
                                        <line x1="3" y1="12" x2="21" y2="12"></line>
                                        <line x1="3" y1="6" x2="21" y2="6"></line>
                                        <line x1="3" y1="18" x2="21" y2="18"></line>
                                    </svg>
                                </a>
                                <div class="d-flex breadcrumb-content">
                                    <div class="page-header">

                                        <div class="page-title">
                                        </div>

                                        <nav class="breadcrumb-style-one" aria-label="breadcrumb">
                                            <ol class="breadcrumb">
                                                <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
                                                {{-- <li class="breadcrumb-item active" aria-current="page">Analytics</li> --}}
                                            </ol>
                                        </nav>

                                    </div>
                                </div>
                                
                            </header>
                        </div>
                    </div>
                    <!--  END BREADCRUMBS  -->
                    @yield('main-content')

                    <!-- BEGIN FOOTER -->
                    <div class="footer-wrapper">
                        <div class="footer-section f-section-1">
                            <p>Copyright &copy; <span class="dynamic-year">{{ date('Y') }}</span>
                                <strong>{{ config('app.name') }}</strong>. All rights reserved.</p>
                        </div>
                        <div class="footer-section f-section-2">
                            <p>Admin Panel</p>
                        </div>
                    </div>
                    <!-- END FOOTER -->

                </div>
            </div>
            <!-- END CONTENT AREA -->

        </div>
        <!-- END MAIN CONTAINER -->

        <!-- BEGIN GLOBAL MANDATORY SCRIPTS -->
        <script src="{{ asset('') }}src/plugins/src/global/vendors.min.js"></script>
        <script src="{{ asset('') }}src/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="{{ asset('') }}src/plugins/src/perfect-scrollbar/perfect-scrollbar.min.js"></script>
        <script src="{{ asset('') }}src/plugins/src/mousetrap/mousetrap.min.js"></script>
        <script src="{{ asset('') }}src/plugins/src/waves/waves.min.js"></script>
        <script src="{{ asset('') }}layouts/vertical-dark-menu/app.js?v={{ filemtime(public_path('layouts/vertical-dark-menu/app.js')) }}"></script>
        <script src="{{ asset('') }}src/assets/js/custom.js"></script>
        <!-- END GLOBAL MANDATORY SCRIPTS -->

        <!-- ApexCharts -->
        <script src="{{ asset('') }}src/plugins/src/apex/apexcharts.min.js"></script>

        @yield('scripts')

</body>

</html>
