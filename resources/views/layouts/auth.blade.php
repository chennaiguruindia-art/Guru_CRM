<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Horticulture CRM') }} - @yield('title', 'Sign In')</title>

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom Horticulture CRM Styles -->
    <link rel="stylesheet" href="{{ asset('css/app-custom.css') }}">
    
    <style>
        .auth-wrapper {
            min-height: 100vh;
            background:
                radial-gradient(1100px 520px at 8% -10%, rgba(22, 163, 74, 0.18), transparent 60%),
                radial-gradient(900px 480px at 100% 0%, rgba(14, 165, 233, 0.16), transparent 55%),
                linear-gradient(160deg, #f5f7f9 0%, #eef2f6 55%, #e8f3ec 100%);
        }
        .auth-card {
            border-radius: 20px;
            box-shadow: 0 24px 48px rgba(16, 24, 40, 0.10), 0 2px 6px rgba(16, 24, 40, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.9);
            overflow: hidden;
            backdrop-filter: blur(6px);
            background: #ffffff;
        }
        .auth-header {
            background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 70%);
            padding: 2.5rem 2rem 1rem;
            text-align: center;
            border-bottom: 1px solid #e5e7eb;
        }
        .auth-body {
            background: #ffffff;
            padding: 1.5rem 2rem 2.5rem;
        }
        .auth-icon {
            width: 68px;
            height: 68px;
            border-radius: 18px;
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            color: #ffffff;
            font-size: 2rem;
            box-shadow: 0 8px 20px rgba(22, 163, 74, 0.32);
        }
        .auth-footer {
            color: #6b7280;
        }
    </style>
</head>
<body class="auth-wrapper d-flex align-items-center justify-content-center p-3">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">
                <div class="card auth-card">
                    <div class="auth-header">
                        <div class="d-inline-flex align-items-center justify-content-center auth-icon mb-3">
                            <i class="bi bi-flower1"></i>
                        </div>
                        <h4 class="fw-bold text-dark mb-1">Horticulture CRM</h4>
                        <p class="text-muted small mb-0">Garden &amp; Landscape Enterprise Suite</p>
                    </div>
                    <div class="auth-body">
                        @include('components.alert')
                        @yield('content')
                    </div>
                </div>
                <div class="text-center mt-3 auth-footer small">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5.3 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/common.js') }}"></script>
    @stack('scripts')
</body>
</html>
