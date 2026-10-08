<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Horticulture CRM') }} - @yield('title', 'Enterprise Operations')</title>

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom Horticulture CRM Styles -->
    <link rel="stylesheet" href="{{ asset('css/app-custom.css') }}">

    @stack('styles')
</head>
<body class="bg-light">
    <div class="d-flex">
        <!-- Sidebar Navigation -->
        @include('components.sidebar')

        <!-- Main Wrapper -->
        <div id="content-wrapper" class="flex-grow-1 d-flex flex-column min-vh-100">
            <!-- Top Navbar -->
            @include('components.navbar')

            <!-- Main Content Area -->
            <main class="flex-grow-1 app-main">
                <!-- Flash Alerts -->
                @include('components.alert')

                <!-- Page Content -->
                @yield('content')
            </main>

            <!-- Footer -->
            @include('components.footer')
        </div>
    </div>

    <!-- Toast Notification Container -->
    <div id="toast-container" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;"></div>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

    <!-- Custom Common Scripts -->
    <script src="{{ asset('js/common.js') }}"></script>

    <!-- Toolbar: global search + notifications -->
    <script src="{{ asset('js/toolbar.js') }}"></script>

    <!-- Sidebar: pins / favourites -->
    <script src="{{ asset('js/sidebar.js') }}"></script>

    @stack('scripts')
</body>
</html>
