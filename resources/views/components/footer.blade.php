@php
    // Every figure below is read from the running system rather than typed
    // into the template, so the footer stays truthful once this is deployed.
    $drivers = [
        'mysql' => 'MySQL',
        'mariadb' => 'MariaDB',
        'pgsql' => 'PostgreSQL',
        'sqlite' => 'SQLite',
        'sqlsrv' => 'SQL Server',
    ];

    $driver = strtolower((string) config(
        'database.connections.' . config('database.default') . '.driver',
        'mysql'
    ));

    $stack = 'Laravel ' . \Illuminate\Foundation\Application::VERSION
        . ' / ' . ($drivers[$driver] ?? strtoupper($driver));

    // A genuine check rather than a badge that is always green: can the
    // application reach its database, and are that database's tables there?
    try {
        $online = \Illuminate\Support\Facades\DB::getSchemaBuilder()->hasTable('users');
    } catch (\Throwable $e) {
        $online = false;
    }

    // Rendered only when the app is not marked as production, so a live box
    // stays clean while an accidental deploy to the wrong place is obvious.
    $environment = (string) config('app.env');
@endphp

<!-- Footer Component -->
<footer class="bg-white border-top py-3 px-4 mt-auto">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 small text-muted">
        <div>
            &copy; {{ now()->format('Y') }} <strong class="text-dark">{{ config('app.name', 'Horticulture CRM') }}</strong>. {{ config('app.tagline', 'Professional Landscaping & Nursery ERP') }}.
        </div>
        <div class="d-flex gap-3 flex-wrap justify-content-center">
            <span>System Version {{ config('app.version', '2.0') }}</span>
            <span>&bull;</span>
            <span>{{ $stack }}</span>
            <span>&bull;</span>
            @if ($online)
                <span class="badge bg-soft-success text-success"><i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> System Online</span>
            @else
                <span class="badge bg-soft-danger text-danger"><i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> Database Offline</span>
            @endif
            @unless ($environment === 'production')
                <span class="badge bg-soft-warning text-warning border">{{ strtoupper($environment) }}</span>
            @endunless
        </div>
    </div>
</footer>
