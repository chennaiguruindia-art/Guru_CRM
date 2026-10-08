@extends('layouts.app')

@section('title', 'Executive Dashboard')

@section('content')
<div class="container-fluid px-0">
    <!-- Top Action Bar -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">Horticulture Operations Dashboard</h4>
            <p class="text-muted small mb-0">Live overview of visits, landscaping projects, nursery inventory, and AMC contracts.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button id="refresh-stats-btn" class="btn btn-outline-success btn-sm d-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-arrow-clockwise"></i>
                <span>Refresh Live KPIs</span>
            </button>
            <div class="dropdown">
                <button class="btn btn-success btn-sm dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-plus-lg me-1"></i> Quick Create
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                    <li><a class="dropdown-item py-2" href="{{ Route::has('leads.create') ? route('leads.create') : '#' }}"><i class="bi bi-funnel text-success me-2"></i> New Visit</a></li>
                    <li><a class="dropdown-item py-2" href="{{ Route::has('customers.create') ? route('customers.create') : '#' }}"><i class="bi bi-people text-primary me-2"></i> New Client</a></li>
                    <li><a class="dropdown-item py-2" href="{{ Route::has('settings.index') ? route('settings.index') : '#' }}"><i class="bi bi-gear text-secondary me-2"></i> Global Settings</a></li>
                    <li><a class="dropdown-item py-2" href="{{ Route::has('quotations.create') ? route('quotations.create') : '#' }}"><i class="bi bi-file-earmark-plus text-info me-2"></i> Create Quotation</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Needs Your Attention -->
    <div id="todo" class="card border-0 shadow-sm mb-4 todo-card">
        <div class="d-flex justify-content-between align-items-center px-4 py-3 border-bottom flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="todo-heading-icon"><i class="bi bi-lightning-charge-fill"></i></span>
                <h6 class="mb-0 fw-bold">Needs Your Attention</h6>
                <span class="badge bg-success-subtle text-success" id="todo-count">{{ count($todo) }}</span>
            </div>
            <small class="text-muted"><i class="bi bi-arrow-down-up me-1"></i>Most urgent first</small>
        </div>

        <div class="p-3">
            <div class="row g-2" id="todo-list">
                @forelse($todo as $item)
                    <div class="col-12 col-md-6 col-xl-4">
                        <a href="{{ $item['url'] }}" class="todo-item severity-{{ $item['severity'] }}">
                            <span class="todo-icon sev-{{ $item['severity'] }}"><i class="bi {{ $item['icon'] }}"></i></span>
                            <span class="todo-body">
                                <span class="todo-title">{{ $item['title'] }}</span>
                                <span class="todo-meta">{{ $item['meta'] }}</span>
                                <span class="todo-label sev-text-{{ $item['severity'] }}">{{ $item['label'] }}</span>
                            </span>
                            <i class="bi bi-arrow-right todo-go"></i>
                        </a>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="todo-empty">
                            <i class="bi bi-check2-circle"></i>
                            <div class="fw-semibold text-dark">Nothing needs attention</div>
                            <small>Overdue invoices, due visits and tasks will appear here automatically.</small>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- 13 KPI Cards Grid -->
    <div class="row g-3 mb-4">
        <!-- 1. Total Visits -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card card-kpi p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-medium">Total Visits</span>
                    <div class="kpi-icon-box bg-soft-primary"><i class="bi bi-funnel"></i></div>
                </div>
                <h3 class="fw-bold mb-0 text-dark" id="kpi-total_leads">{{ number_format($metrics['total_leads']) }}</h3>
                <small class="text-muted mt-1" style="font-size: 0.75rem;">All logged visits</small>
            </div>
        </div>

        <!-- 2. New Visits -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card card-kpi p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-medium">New Visits</span>
                    <div class="kpi-icon-box bg-soft-info"><i class="bi bi-star"></i></div>
                </div>
                <h3 class="fw-bold mb-0 text-dark" id="kpi-new_leads">{{ number_format($metrics['new_leads']) }}</h3>
                <small class="text-info mt-1" style="font-size: 0.75rem;">Awaiting first contact</small>
            </div>
        </div>

        <!-- 3. Converted Visits -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card card-kpi p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-medium">Converted</span>
                    <div class="kpi-icon-box bg-soft-success"><i class="bi bi-check2-circle"></i></div>
                </div>
                <h3 class="fw-bold mb-0 text-success" id="kpi-converted_leads">{{ number_format($metrics['converted_leads']) }}</h3>
                <small class="text-success mt-1" style="font-size: 0.75rem;">Converted to clients</small>
            </div>
        </div>

        <!-- 4. Total Clients -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card card-kpi p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-medium">Clients</span>
                    <div class="kpi-icon-box bg-soft-forest"><i class="bi bi-people"></i></div>
                </div>
                <h3 class="fw-bold mb-0 text-dark" id="kpi-total_customers">{{ number_format($metrics['total_customers']) }}</h3>
                <small class="text-muted mt-1" style="font-size: 0.75rem;">Active client accounts</small>
            </div>
        </div>

        <!-- 5. Active Projects -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card card-kpi p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-medium">Active Projects</span>
                    <div class="kpi-icon-box bg-soft-warning"><i class="bi bi-kanban"></i></div>
                </div>
                <h3 class="fw-bold mb-0 text-warning" id="kpi-active_projects">{{ number_format($metrics['active_projects']) }}</h3>
                <small class="text-muted mt-1" style="font-size: 0.75rem;">In landscaping execution</small>
            </div>
        </div>

        <!-- 6. Completed Projects -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card card-kpi p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-medium">Completed</span>
                    <div class="kpi-icon-box bg-soft-success"><i class="bi bi-trophy"></i></div>
                </div>
                <h3 class="fw-bold mb-0 text-success" id="kpi-completed_projects">{{ number_format($metrics['completed_projects']) }}</h3>
                <small class="text-muted mt-1" style="font-size: 0.75rem;">Delivered projects</small>
            </div>
        </div>

        <!-- 7. Pending Quotations -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card card-kpi p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-medium">Pending Quotes</span>
                    <div class="kpi-icon-box bg-soft-warning"><i class="bi bi-file-earmark-text"></i></div>
                </div>
                <h3 class="fw-bold mb-0 text-dark" id="kpi-pending_quotations">{{ number_format($metrics['pending_quotations']) }}</h3>
                <small class="text-warning mt-1" style="font-size: 0.75rem;">Under review</small>
            </div>
        </div>

        <!-- 8. Approved Quotations -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card card-kpi p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-medium">Approved Quotes</span>
                    <div class="kpi-icon-box bg-soft-success"><i class="bi bi-check-all"></i></div>
                </div>
                <h3 class="fw-bold mb-0 text-success" id="kpi-approved_quotations">{{ number_format($metrics['approved_quotations']) }}</h3>
                <small class="text-success mt-1" style="font-size: 0.75rem;">Ready for Work Order</small>
            </div>
        </div>

        <!-- 9. Active AMC -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card card-kpi p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-medium">Active AMC</span>
                    <div class="kpi-icon-box bg-soft-forest"><i class="bi bi-shield-check"></i></div>
                </div>
                <h3 class="fw-bold mb-0 text-dark" id="kpi-active_amc">{{ number_format($metrics['active_amc']) }}</h3>
                <small class="text-muted mt-1" style="font-size: 0.75rem;">Garden maintenance contracts</small>
            </div>
        </div>

        <!-- 10. Pending Invoices -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card card-kpi p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-medium">Pending Invoices</span>
                    <div class="kpi-icon-box bg-soft-danger"><i class="bi bi-receipt"></i></div>
                </div>
                <h3 class="fw-bold mb-0 text-danger" id="kpi-pending_invoices">{{ number_format($metrics['pending_invoices']) }}</h3>
                <small class="text-danger mt-1" style="font-size: 0.75rem;">Unpaid billings</small>
            </div>
        </div>

        <!-- 11. Paid Amount -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card card-kpi p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-medium">Collections</span>
                    <div class="kpi-icon-box bg-soft-success"><i class="bi bi-cash"></i></div>
                </div>
                <h4 class="fw-bold mb-0 text-success" id="kpi-paid_amount">₹{{ number_format($metrics['paid_amount'], 2) }}</h4>
                <small class="text-muted mt-1" style="font-size: 0.75rem;">Total paid collections</small>
            </div>
        </div>

        <!-- 12. Pending & Overdue Amount -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card card-kpi p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-medium">Pending / Overdue</span>
                    <div class="kpi-icon-box bg-soft-danger"><i class="bi bi-exclamation-octagon"></i></div>
                </div>
                <h5 class="fw-bold mb-0 text-danger" id="kpi-pending_amount">₹{{ number_format($metrics['pending_amount']) }}</h5>
                <small class="text-danger mt-1" style="font-size: 0.75rem;">Overdue: ₹{{ number_format($metrics['overdue_amount']) }}</small>
            </div>
        </div>
    </div>

    <!-- Charts Row 1: Visits Analytics & Pipeline -->
    <div class="row g-4 mb-4">
        <!-- 1. Visits by Source -->
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 fw-semibold text-dark">
                        <i class="bi bi-pie-chart text-success me-2"></i> Visits by Source
                    </h6>
                    <span class="badge bg-light text-muted">All Time</span>
                </div>
                <div class="card-body p-3">
                    <div style="height: 240px;">
                        <canvas id="chartLeadsSource"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Visits by Status -->
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 fw-semibold text-dark">
                        <i class="bi bi-bar-chart text-primary me-2"></i> Visits by Status
                    </h6>
                    <span class="badge bg-light text-muted">Active Pipeline</span>
                </div>
                <div class="card-body p-3">
                    <div style="height: 240px;">
                        <canvas id="chartLeadsStatus"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Sales Pipeline Funnel -->
        <div class="col-12 col-md-12 col-xl-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 fw-semibold text-dark">
                        <i class="bi bi-funnel-fill text-warning me-2"></i> Sales Pipeline Conversion
                    </h6>
                    <span class="badge bg-light text-muted">Conversion Funnel</span>
                </div>
                <div class="card-body p-3">
                    <div style="height: 240px;">
                        <canvas id="chartSalesPipeline"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2: Revenue, Quotations, and Projects -->
    <div class="row g-4 mb-4">
        <!-- 4. Monthly Quotation Value -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 fw-semibold text-dark">
                        <i class="bi bi-graph-up text-accent me-2" style="color: #0ea5e9;"></i> Monthly Quotation Trend
                    </h6>
                    <span class="badge bg-soft-success text-success">Last 6 Months</span>
                </div>
                <div class="card-body p-3">
                    <div style="height: 260px;">
                        <canvas id="chartMonthlyQuotation"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. Monthly Revenue vs Expenses -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 fw-semibold text-dark">
                        <i class="bi bi-cash-coin text-success me-2"></i> Monthly Revenue vs Expenses
                    </h6>
                    <span class="badge bg-light text-muted">Cash Flow (₹)</span>
                </div>
                <div class="card-body p-3">
                    <div style="height: 260px;">
                        <canvas id="chartMonthlyRevenue"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 3: Operations & Payments Aging -->
    <div class="row g-4 mb-4">
        <!-- 6. Project Status -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 fw-semibold text-dark">
                        <i class="bi bi-diagram-3 text-info me-2"></i> Landscaping Projects Status
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div style="height: 230px;">
                        <canvas id="chartProjectStatus"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- 7. AMC Status -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 fw-semibold text-dark">
                        <i class="bi bi-calendar2-check text-success me-2"></i> AMC Contracts Status
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div style="height: 230px;">
                        <canvas id="chartAmcStatus"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- 8. Pending Payments Aging -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 fw-semibold text-dark">
                        <i class="bi bi-hourglass-split text-danger me-2"></i> Receivables Aging (₹)
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div style="height: 230px;">
                        <canvas id="chartPendingAging"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent System Audit Activity Trail -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="card-title mb-0 fw-semibold text-dark">
                <i class="bi bi-clock-history text-secondary me-2"></i> Recent System & Audit Activity
            </h6>
            <a href="{{ Route::has('audit-logs.index') ? route('audit-logs.index') : '#' }}" class="small text-decoration-none text-success fw-medium">View All Logs</a>
        </div>
        <div class="table-responsive">
            <table class="table table-crm mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Module</th>
                        <th>IP Address</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentActivities as $log)
                        <tr>
                            <td class="small text-muted">{{ $log->created_at->format('d M Y, h:i A') }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="navbar-user-avatar" style="width: 26px; height: 26px; font-size: 0.75rem;">
                                        {{ $log->user ? $log->user->initials : 'SYS' }}
                                    </div>
                                    <span class="small fw-medium">{{ $log->user ? $log->user->name : 'System' }}</span>
                                </div>
                            </td>
                            <td><span class="badge bg-light text-dark text-capitalize">{{ str_replace('_', ' ', $log->action) }}</span></td>
                            <td class="small text-secondary">{{ \App\Services\ModuleLabel::for($log->module) }}</td>
                            <td class="small text-muted">{{ $log->ip_address ?? '127.0.0.1' }}</td>
                            <td><span class="badge bg-soft-success text-success">Logged</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted small">
                                <i class="bi bi-check-circle text-success fs-4 d-block mb-1"></i>
                                System operational. Fresh installation initialized. All module activities will be recorded here.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/dashboard.js') }}"></script>
@endpush
