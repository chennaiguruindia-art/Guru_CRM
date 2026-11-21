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
        .portal-brand-badge {
            font-size: 0.68rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            font-weight: 700;
            background: rgba(25, 135, 84, 0.12);
            color: #198754;
            padding: 3px 8px;
            border-radius: 4px;
        }
        .portal-user-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px;
            margin: 10px 12px 14px;
        }
        .portal-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: linear-gradient(135deg, #198754 0%, #157347 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
            box-shadow: 0 2px 6px rgba(25, 135, 84, 0.25);
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
        @media print {
            #sidebar, .top-navbar, footer, .no-print, .btn, #sidebar-toggle {
                display: none !important;
            }
            #content-wrapper {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
            }
            body {
                background: #fff !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body class="bg-light">
    @php
        $authUser = auth()->user();
        $authEmployee = $authUser?->employee ?? \App\Models\Employee::with('branch')->where('user_id', $authUser?->id)->first();
    @endphp

    <div class="d-flex">
        <!-- Sidebar Navigation for Employee Portal -->
        <aside id="sidebar" class="d-flex flex-column" aria-label="Portal Navigation">
            <!-- Brand header -->
            <div class="sidebar-brand-wrapper d-flex align-items-center justify-content-between">
                <a href="{{ route('portal.dashboard') }}" class="sidebar-brand text-decoration-none d-flex align-items-center gap-2">
                    <img src="{{ asset('logo/guru.png') }}" alt="Guru Living Assets" class="sidebar-brand-logo" style="max-height: 40px; width: auto;">
                </a>
                <button id="sidebar-close-btn" class="btn btn-sm btn-link text-muted p-1 d-lg-none" type="button" aria-label="Close navigation">
                    <i class="bi bi-x-lg fs-5"></i>
                </button>
            </div>

            <!-- Portal Badge & Employee Quick Info -->
            <div class="px-3 pt-3 pb-1 d-flex align-items-center justify-content-between">
                <span class="portal-brand-badge"><i class="bi bi-person-workspace me-1"></i>Employee Portal</span>
                <span class="badge bg-success-subtle text-success border border-success-subtle small">
                    <i class="bi bi-circle-fill text-success me-1" style="font-size: 0.5rem;"></i>Active
                </span>
            </div>

            <div class="portal-user-card">
                <div class="d-flex align-items-center gap-2">
                    <div class="portal-avatar">
                        {{ strtoupper(substr($authEmployee ? $authEmployee->name : ($authUser->name ?? 'U'), 0, 1)) }}
                    </div>
                    <div class="overflow-hidden">
                        <div class="fw-bold text-dark text-truncate small" title="{{ $authEmployee ? $authEmployee->name : $authUser->name }}">
                            {{ $authEmployee ? $authEmployee->name : $authUser->name }}
                        </div>
                        <div class="text-success font-monospace fw-semibold" style="font-size: 0.75rem;">
                            {{ $authEmployee ? $authEmployee->employee_code : ($authUser->username ?? 'ID Pending') }}
                        </div>
                        <div class="text-muted text-truncate" style="font-size: 0.7rem;">
                            {{ $authEmployee?->designation ?? 'Staff Member' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Navigation Links -->
            <div class="sidebar-menu px-2 py-1 flex-grow-1 overflow-y-auto">
                <div class="sidebar-section-divider">MAIN MENU</div>

                <a href="{{ route('portal.dashboard') }}" class="sidebar-link {{ request()->routeIs('portal.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span class="sidebar-text">Dashboard</span>
                </a>

                <a href="{{ route('portal.punch') }}" class="sidebar-link {{ request()->routeIs('portal.punch') ? 'active' : '' }}">
                    <i class="bi bi-fingerprint"></i>
                    <span class="sidebar-text">Punch Attendance</span>
                </a>

                <a href="{{ route('portal.attendance') }}" class="sidebar-link {{ request()->routeIs('portal.attendance') ? 'active' : '' }}">
                    <i class="bi bi-calendar2-check-fill"></i>
                    <span class="sidebar-text">Attendance History</span>
                </a>

                <div class="sidebar-section-divider mt-3">SELF-SERVICE</div>

                <a href="{{ route('portal.profile') }}" class="sidebar-link {{ request()->routeIs('portal.profile') ? 'active' : '' }}">
                    <i class="bi bi-person-vcard-fill"></i>
                    <span class="sidebar-text">My Profile & Details</span>
                </a>

                <a href="{{ route('portal.family') }}" class="sidebar-link {{ request()->routeIs('portal.family') ? 'active' : '' }}">
                    <i class="bi bi-people-fill"></i>
                    <span class="sidebar-text">Parents & Family Details</span>
                </a>

                <a href="{{ route('portal.payslips') }}" class="sidebar-link {{ request()->routeIs('portal.payslip*') ? 'active' : '' }}">
                    <i class="bi bi-receipt-cutoff"></i>
                    <span class="sidebar-text">My Payslips</span>
                </a>

                @if($authUser && $authUser->hasAnyRole(['super-admin', 'admin', 'fieldmarketer']))
                    <div class="sidebar-section-divider mt-3">MANAGEMENT</div>
                    <a href="{{ route('dashboard') }}" class="sidebar-link text-primary fw-semibold">
                        <i class="bi bi-arrow-left-right text-primary"></i>
                        <span class="sidebar-text">Switch to CRM</span>
                    </a>
                @endif
            </div>

            <!-- Sidebar Footer: Logged in user & Sign Out -->
            <div class="sidebar-footer d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2 overflow-hidden">
                    <i class="bi bi-shield-check text-success fs-5"></i>
                    <div class="overflow-hidden">
                        <div class="fw-semibold text-dark text-truncate small" style="max-width: 130px;">
                            {{ $authEmployee?->branch?->name ?? 'HQ Office' }}
                        </div>
                        <div class="text-muted" style="font-size: 0.7rem;">Verified Employee</div>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger p-1 px-2 rounded" title="Sign out of Employee Portal">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div id="content-wrapper" class="flex-grow-1 d-flex flex-column min-vh-100">
            <!-- Top Navbar -->
            <header class="top-navbar bg-white border-bottom shadow-sm px-3 py-2 d-flex align-items-center justify-content-between sticky-top">
                <div class="d-flex align-items-center gap-2">
                    <button id="sidebar-toggle" class="btn btn-sm btn-outline-secondary d-lg-none" type="button" aria-label="Toggle navigation">
                        <i class="bi bi-list fs-5"></i>
                    </button>
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="fw-bold mb-0 text-dark d-none d-sm-block">
                            @yield('title', 'Employee HRM Portal')
                        </h5>
                        @if($authEmployee?->branch)
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $authEmployee->branch->name }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('portal.punch') }}" class="btn btn-sm btn-outline-success fw-semibold d-none d-md-flex align-items-center gap-1 shadow-sm">
                        <i class="bi bi-fingerprint"></i>
                        <span>Punch Today</span>
                    </a>

                    @if($authUser && $authUser->hasAnyRole(['super-admin', 'admin', 'fieldmarketer']))
                        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-light text-primary border fw-semibold d-none d-sm-inline-flex align-items-center gap-1">
                            <i class="bi bi-arrow-left-right"></i>
                            <span>CRM Desk</span>
                        </a>
                    @endif

                    <!-- User Dropdown Menu -->
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light border dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle text-success fs-5"></i>
                            <span class="fw-semibold text-dark d-none d-md-inline">{{ $authEmployee ? $authEmployee->name : $authUser->name }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-1">
                            <li class="px-3 py-2 border-bottom bg-light">
                                <div class="fw-bold text-dark">{{ $authEmployee ? $authEmployee->name : $authUser->name }}</div>
                                <div class="text-success small font-monospace">{{ $authEmployee ? $authEmployee->employee_code : ($authUser->username ?? 'ID Pending') }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">{{ $authEmployee?->designation ?? 'Employee' }}</div>
                            </li>
                            <li>
                                <a class="dropdown-item py-2" href="{{ route('portal.profile') }}">
                                    <i class="bi bi-person me-2 text-primary"></i>My Profile
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2" href="{{ route('portal.family') }}">
                                    <i class="bi bi-people me-2 text-info"></i>Parents & Family
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2" href="{{ route('portal.payslips') }}">
                                    <i class="bi bi-receipt me-2 text-success"></i>My Payslips
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger py-2">
                                        <i class="bi bi-box-arrow-right me-2"></i>Sign Out
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Main App Content -->
            <main class="flex-grow-1 p-3 p-md-4">
                <!-- Flash Alerts -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
                        <i class="bi bi-check-circle-fill fs-5"></i>
                        <div>{{ session('success') }}</div>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                        <div>{{ session('error') }}</div>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
                        <i class="bi bi-info-circle-fill fs-5"></i>
                        <div>{{ session('info') }}</div>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="bg-white border-top py-3 text-center text-muted small mt-auto">
                <div class="container-fluid px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        &copy; {{ date('Y') }} <strong>GURU Living Assets</strong> &middot; India's Green Transformation Partner
                    </div>
                    <div>
                        Employee Self-Service HRM Portal v2.0
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom Common Scripts (for mobile sidebar toggle, toasts, alerts) -->
    <script src="{{ asset('js/common.js') }}"></script>

    @stack('scripts')
</body>
</html>
