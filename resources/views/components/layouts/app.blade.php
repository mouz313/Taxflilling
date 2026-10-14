<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $header ?? 'Practice Desk' }} - TaxFiler.pk Admin</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,600,700&display=fallback">

    <!-- Font Awesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Application Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Official AdminLTE 3.2 CSS Theme -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        .nav-sidebar .nav-link p {
            margin-bottom: 0 !important;
        }
        .card-outline-tabs .card-header .nav-link.active {
            border-top: 3px solid #007bff !important;
        }
    </style>

    @stack('styles')
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed text-sm">
    <div class="wrapper">
        <!-- Global Toast Component -->
        <x-ui.toast />

        @php
            $isCompany = auth()->check() && auth()->user()->role === 'company';
            $pendingLeads = \App\Models\CallbackRequest::where('status', 'pending')->count();
            $inReviewReturns = \App\Models\TaxReturn::where('status', 'in_review')->count();
            $pendingOrders = \App\Models\ServiceRequest::where('status', 'submitted')->count();
        @endphp

        <!-- ===================================================================== -->
        <!-- 1. FIXED TOP NAVBAR (AdminLTE Official)                               -->
        <!-- ===================================================================== -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light border-bottom">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button" title="Toggle Navigation Sidebar">
                        <i class="fas fa-bars"></i>
                    </a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'font-weight-bold text-primary' : '' }}">
                        Dashboard
                    </a>
                </li>
                @if($isCompany)
                    <li class="nav-item d-none d-sm-inline-block">
                        <a href="{{ route('returns.index') }}" class="nav-link {{ request()->routeIs('returns.*') ? 'font-weight-bold text-primary' : '' }}">
                            Tax Returns
                        </a>
                    </li>
                    <li class="nav-item d-none d-md-inline-block">
                        <a href="{{ route('clients.index') }}" class="nav-link {{ request()->routeIs('clients.*') ? 'font-weight-bold text-primary' : '' }}">
                            Clients
                        </a>
                    </li>
                    <li class="nav-item d-none d-lg-inline-block">
                        <a href="{{ route('admin.fbr.index') }}" class="nav-link {{ request()->routeIs('admin.fbr.*') ? 'font-weight-bold text-primary' : '' }}">
                            FBR Verification
                        </a>
                    </li>
                @endif
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto align-items-center">
                <!-- WhatsApp Help Helpline -->
                <li class="nav-item d-none d-md-inline-block mr-2">
                    <a
                        href="https://wa.me/923001234567?text=Assalam%20o%20Alaikum,%20mujhe%20TaxFiler%20dashboard%20ke%20hawaale%20se%20support%20chahiye."
                        target="_blank"
                        class="btn btn-sm btn-outline-success font-weight-bold"
                    >
                        <i class="fab fa-whatsapp mr-1 text-success"></i> WhatsApp Help
                    </a>
                </li>

                <!-- Notifications Dropdown Menu -->
                <li class="nav-item mr-2">
                    <a class="nav-link position-relative" href="{{ route('admin.callbacks.index') }}" title="Call Requests">
                        <i class="far fa-bell"></i>
                        @if($pendingLeads > 0)
                            <span class="badge badge-danger navbar-badge">{{ $pendingLeads }}</span>
                        @endif
                    </a>
                </li>

                <!-- User Dropdown Menu -->
                @auth
                    <li class="nav-item dropdown user-menu" x-data="{ open: false }" @click.outside="open = false">
                        <a href="#" class="nav-link d-flex align-items-center" @click.prevent="open = !open">
                            <span class="img-circle bg-primary text-white font-weight-bold d-inline-flex align-items-center justify-content-center mr-2" style="width: 28px; height: 28px; font-size: 12px;">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </span>
                            <span class="d-none d-md-inline font-weight-bold text-dark">{{ auth()->user()->name }}</span>
                            <i class="fas fa-caret-down ml-1 text-muted" style="font-size: 10px;"></i>
                        </a>
                        <div
                            x-show="open"
                            x-cloak
                            class="dropdown-menu dropdown-menu-lg dropdown-menu-right show shadow-sm"
                            style="display: none;"
                        >
                            <!-- User image header -->
                            <div class="p-3 bg-light border-bottom">
                                <p class="font-weight-bold mb-0 text-dark">{{ auth()->user()->name }}</p>
                                <small class="text-muted font-monospace">{{ auth()->user()->email }}</small>
                            </div>
                            <!-- Menu Body -->
                            <a href="{{ route('settings.company') }}" class="dropdown-item py-2">
                                <i class="fas fa-sliders-h mr-2 text-primary"></i> Practice &amp; Profile Settings
                            </a>
                            <a href="{{ route('admin.cms.seo.index') }}" class="dropdown-item py-2">
                                <i class="fas fa-chart-line mr-2 text-info"></i> SEO &amp; Ad Tracking
                            </a>
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item py-2 text-danger font-weight-bold">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Sign Out
                                </button>
                            </form>
                        </div>
                    </li>
                @endauth
            </ul>
        </nav>

        <!-- ===================================================================== -->
        <!-- 2. MAIN SIDEBAR CONTAINER (AdminLTE Official #343a40)                 -->
        <!-- ===================================================================== -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="{{ route('dashboard') }}" class="brand-link">
                <span class="brand-image img-circle elevation-2 bg-primary d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                    <i class="fas fa-file-invoice text-white text-sm"></i>
                </span>
                <span class="brand-text font-weight-bold ml-2">TaxFiler<span class="text-info">.pk</span></span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- User Panel -->
                @auth
                    <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
                        <div class="image">
                            <span class="img-circle elevation-1 bg-info d-inline-flex align-items-center justify-content-center text-white font-weight-bold" style="width: 34px; height: 34px; font-size: 13px;">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </span>
                        </div>
                        <div class="info">
                            <a href="{{ route('settings.company') }}" class="d-block font-weight-bold text-white text-truncate" style="max-width: 160px;">
                                {{ auth()->user()->name }}
                            </a>
                            <small class="badge badge-success" style="font-size: 9px;">
                                {{ auth()->user()->isCompany() ? 'Tax Consultant' : 'Client Filer' }}
                            </small>
                        </div>
                    </div>
                @endauth

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent" data-widget="treeview" role="menu">
                        <!-- Header -->
                        <li class="nav-header">MAIN NAVIGATION</li>

                        <!-- Dashboard -->
                        <li class="nav-item">
                            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        @if($isCompany)
                            <!-- 1. Tax Returns -->
                            <li class="nav-item">
                                <a href="{{ route('returns.index') }}" class="nav-link {{ request()->routeIs('returns.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-file-invoice-dollar"></i>
                                    <p>
                                        Tax Returns
                                        @if($inReviewReturns > 0)
                                            <span class="badge badge-warning right">{{ $inReviewReturns }}</span>
                                        @endif
                                    </p>
                                </a>
                            </li>

                            <!-- 2. Clients Directory -->
                            <li class="nav-item">
                                <a href="{{ route('clients.index') }}" class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-users"></i>
                                    <p>Clients Directory</p>
                                </a>
                            </li>

                            <!-- Operations Header -->
                            <li class="nav-header">DESKS &amp; FULFILLMENT</li>

                            <!-- 3. Corporate Orders -->
                            <li class="nav-item">
                                <a href="{{ route('admin.orders.index') }}" class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-shopping-cart"></i>
                                    <p>
                                        Corporate Orders
                                        @if($pendingOrders > 0)
                                            <span class="badge badge-info right">{{ $pendingOrders }}</span>
                                        @endif
                                    </p>
                                </a>
                            </li>

                            <!-- 4. Call Requests Desk -->
                            <li class="nav-item">
                                <a href="{{ route('admin.callbacks.index') }}" class="nav-link {{ request()->routeIs('admin.callbacks.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-headset"></i>
                                    <p>
                                        Call Requests
                                        @if($pendingLeads > 0)
                                            <span class="badge badge-danger right">{{ $pendingLeads }}</span>
                                        @endif
                                    </p>
                                </a>
                            </li>

                            <!-- 5. FBR Live Verification Bridge -->
                            <li class="nav-item">
                                <a href="{{ route('admin.fbr.index') }}" class="nav-link {{ request()->routeIs('admin.fbr.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-shield-alt"></i>
                                    <p>FBR Verification</p>
                                </a>
                            </li>

                            <!-- CMS Header -->
                            <li class="nav-header">SYSTEM &amp; CMS</li>

                            <!-- 6. SEO & Digital Marketing -->
                            <li class="nav-item">
                                <a href="{{ route('admin.cms.seo.index') }}" class="nav-link {{ request()->routeIs('admin.cms.seo.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-chart-line"></i>
                                    <p>SEO &amp; Marketing</p>
                                </a>
                            </li>

                            <!-- 7. Services Marketplace -->
                            <li class="nav-item">
                                <a href="{{ route('admin.cms.services.index') }}" class="nav-link {{ request()->routeIs('admin.cms.services.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-boxes-stacked"></i>
                                    <p>Services Catalog</p>
                                </a>
                            </li>

                            <!-- 8. Firm Settings -->
                            <li class="nav-item">
                                <a href="{{ route('settings.company') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-cog"></i>
                                    <p>Firm Settings</p>
                                </a>
                            </li>
                        @else
                            <li class="nav-item">
                                <a href="{{ route('client.returns') }}" class="nav-link {{ request()->routeIs('client.returns*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-file-invoice"></i>
                                    <p>My Tax Returns</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('client.applications') }}" class="nav-link {{ request()->routeIs('client.applications*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-briefcase"></i>
                                    <p>My Applications</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('services.index') }}" class="nav-link {{ request()->routeIs('services.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-store"></i>
                                    <p>Services Marketplace</p>
                                </a>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- ===================================================================== -->
        <!-- 3. CONTENT WRAPPER (AdminLTE Official)                                -->
        <!-- ===================================================================== -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2 align-items-center">
                        <div class="col-sm-6">
                            <h1 class="m-0 font-weight-bold text-dark">{{ $header ?? 'Practice Desk' }}</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fas fa-home mr-1"></i>Home</a></li>
                                <li class="breadcrumb-item active">{{ $header ?? 'Dashboard' }}</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <section class="content pb-4">
                <div class="container-fluid">
                    {{ $slot }}
                </div>
            </section>
        </div>

        <!-- ===================================================================== -->
        <!-- 4. MAIN FOOTER                                                        -->
        <!-- ===================================================================== -->
        <footer class="main-footer text-xs">
            <strong>Copyright &copy; {{ date('Y') }} <a href="{{ route('home') }}">TaxFiler.pk</a>.</strong>
            Pakistan Tax &amp; Corporate Advisory Platform.
            <div class="float-right d-none d-sm-inline-block">
                <span class="text-success"><i class="fas fa-circle mr-1"></i></span>
                <b>FBR IRIS 2.0 Live Sync</b> · Version 3.4.0
            </div>
        </footer>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE 3.2 App -->
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

    @stack('scripts')
</body>
</html>
