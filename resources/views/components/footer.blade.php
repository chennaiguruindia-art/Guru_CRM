<!-- Footer Component -->
<footer class="bg-white border-top py-3 px-4 mt-auto">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 small text-muted">
        <div>
            &copy; {{ date('Y') }} <strong class="text-dark">{{ config('app.name', 'Horticulture CRM') }}</strong>. Professional Landscaping & Nursery ERP.
        </div>
        <div class="d-flex gap-3">
            <span>System Version 2.0</span>
            <span>&bull;</span>
            <span>Laravel 12 / MySQL</span>
            <span>&bull;</span>
            <span class="badge bg-soft-success text-success"><i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> System Online</span>
        </div>
    </div>
</footer>
