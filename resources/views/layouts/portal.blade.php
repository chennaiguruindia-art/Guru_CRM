<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Horticulture CRM') }} - @yield('title', 'Employee HRM Portal')</title>

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom Horticulture CRM Styles -->
    <link rel="stylesheet" href="{{ asset('css/app-custom.css') }}">

    <style>
        .portal-header {
            background: linear-gradient(135deg, #198754 0%, #157347 100%);
            color: #fff;
        }
        .punch-btn-in {
            background: linear-gradient(135deg, #20c997 0%, #0f5132 100%);
            border: none;
            color: white;
            transition: all 0.2s ease-in-out;
        }
        .punch-btn-in:hover {
            transform: scale(1.02);
            color: white;
            box-shadow: 0 4px 15px rgba(25, 135, 84, 0.3);
        }
        .punch-btn-out {
            background: linear-gradient(135deg, #fd7e14 0%, #d63384 100%);
            border: none;
            color: white;
            transition: all 0.2s ease-in-out;
        }
        .punch-btn-out:hover {
            transform: scale(1.02);
            color: white;
            box-shadow: 0 4px 15px rgba(253, 126, 20, 0.3);
        }
    </style>
    @stack('styles')
</head>
<body class="bg-light min-vh-100 d-flex flex-column">

    <!-- Top Navigation Bar for Employee Portal -->
    <nav class="navbar navbar-expand-lg portal-header shadow-sm py-2">
        <div class="container">
            <a class="navbar-brand text-white fw-bold d-flex align-items-center gap-2" href="{{ route('portal.dashboard') }}">
                <i class="bi bi-shield-lock-fill fs-4"></i>
                <span>HRM Employee Portal</span>
            </a>

            <button class="navbar-toggler text-white border-0" type="button" data-bs-toggle="collapse" data-bs-target="#portalNav">
                <i class="bi bi-list fs-2 text-white"></i>
            </button>

            <div class="collapse navbar-collapse" id="portalNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->routeIs('portal.punch') ? 'fw-bold active text-decoration-underline' : 'opacity-75' }}" href="{{ route('portal.punch') }}">
                            <i class="bi bi-fingerprint me-1"></i> Punch In/Out
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->routeIs('portal.dashboard') ? 'fw-bold active text-decoration-underline' : 'opacity-75' }}" href="{{ route('portal.dashboard') }}">
                            <i class="bi bi-speedometer2 me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->routeIs('portal.attendance') ? 'fw-bold active text-decoration-underline' : 'opacity-75' }}" href="{{ route('portal.attendance') }}">
                            <i class="bi bi-calendar2-check me-1"></i> My Attendance
                        </a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-3">
                    @if(auth()->user()->hasAnyRole(['super-admin', 'admin', 'fieldmarketer']))
                        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-light fw-semibold text-success">
                            <i class="bi bi-arrow-left-right me-1"></i> Switch to CRM
                        </a>
                    @endif

                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-light dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle fs-5"></i>
                            <span>{{ auth()->user()->name }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li>
                                <div class="px-3 py-2 border-bottom">
                                    <div class="fw-bold">{{ auth()->user()->name }}</div>
                                    <div class="text-muted small">Login: {{ auth()->user()->username ?? auth()->user()->phone }}</div>
                                </div>
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger d-flex align-items-center gap-2 py-2">
                                        <i class="bi bi-box-arrow-right"></i> Sign Out
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="container py-4 flex-grow-1">
        <!-- Flash Alerts -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm d-flex align-items-center gap-2" role="alert">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm d-flex align-items-center gap-2" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <div>{{ session('error') }}</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show shadow-sm d-flex align-items-center gap-2" role="alert">
                <i class="bi bi-info-circle-fill fs-5"></i>
                <div>{{ session('info') }}</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-top py-3 text-center text-muted small mt-auto">
        <div class="container">
            &copy; {{ date('Y') }} {{ config('app.name', 'Company') }} &middot; Employee HRM Self-Service Portal
        </div>
    </footer>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
