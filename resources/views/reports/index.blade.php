@extends('layouts.app')

@section('title', 'Reports & Analytics')

@section('content')
<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold text-dark">
                <i class="bi bi-bar-chart-line me-2 text-primary"></i>Reports & Analytics
            </h1>
            <p class="text-muted mb-0 small">Comprehensive overview of visits, sales, projects and finances</p>
        </div>
        <div class="text-muted small">
            <i class="bi bi-calendar3 me-1"></i>{{ now()->format('d M Y') }}
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Summary Cards Row --}}
    <div class="row g-4 mb-5">
        {{-- Total Visits --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="flex-shrink-0 rounded-3 d-flex align-items-center justify-content-center"
                         style="width:56px;height:56px;background:rgba(13,110,253,.1)">
                        <i class="bi bi-person-lines-fill fs-4 text-primary"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase letter-spacing-1">Total Visits</div>
                        <div class="fs-2 fw-bold text-dark">{{ number_format($leadStats['total']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Quotations Value --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="flex-shrink-0 rounded-3 d-flex align-items-center justify-content-center"
                         style="width:56px;height:56px;background:rgba(25,135,84,.1)">
                        <i class="bi bi-receipt fs-4 text-success"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Quotations Value</div>
                        <div class="fs-2 fw-bold text-dark">
                            ₹{{ number_format($salesStats['quotations_total'], 0) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Active Projects --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="flex-shrink-0 rounded-3 d-flex align-items-center justify-content-center"
                         style="width:56px;height:56px;background:rgba(255,193,7,.1)">
                        <i class="bi bi-kanban fs-4 text-warning"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Active Projects</div>
                        <div class="fs-2 fw-bold text-dark">{{ number_format($projectStats['in_progress']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Invoiced --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="flex-shrink-0 rounded-3 d-flex align-items-center justify-content-center"
                         style="width:56px;height:56px;background:rgba(220,53,69,.1)">
                        <i class="bi bi-cash-stack fs-4 text-danger"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Total Invoiced</div>
                        <div class="fs-2 fw-bold text-dark">
                            ₹{{ number_format($financeStats['invoiced'], 0) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ====================== --}}
    {{-- VISIT ANALYTICS SECTION --}}
    {{-- ====================== --}}
    <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">
        <i class="bi bi-person-check me-2 text-primary"></i>Visit Analytics
    </h5>

    @php
        $leadTotal     = $leadStats['total'] ?? 0;
        $leadConverted = $leadStats['converted'] ?? 0;
        $leadLost      = $leadStats['lost'] ?? 0;
        $leadActive    = max(0, $leadTotal - $leadConverted - $leadLost);
        $conversionRate = $leadTotal > 0 ? round(($leadConverted / $leadTotal) * 100, 1) : 0;
    @endphp

    <div class="row g-4 mb-5">
        {{-- Visit Stats Table --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom fw-semibold py-3">
                    <i class="bi bi-table me-2 text-primary"></i>Visit Breakdown
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Status</th>
                                <th class="text-end">Count</th>
                                <th class="text-end pe-4">Share</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1">
                                        <i class="bi bi-circle-fill me-1" style="font-size:.45rem;vertical-align:middle"></i>Active
                                    </span>
                                </td>
                                <td class="text-end fw-semibold">{{ number_format($leadActive) }}</td>
                                <td class="text-end pe-4 text-muted small">
                                    {{ $leadTotal > 0 ? round(($leadActive/$leadTotal)*100,1) : 0 }}%
                                </td>
                            </tr>
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-success bg-opacity-10 text-success px-2 py-1">
                                        <i class="bi bi-circle-fill me-1" style="font-size:.45rem;vertical-align:middle"></i>Converted
                                    </span>
                                </td>
                                <td class="text-end fw-semibold">{{ number_format($leadConverted) }}</td>
                                <td class="text-end pe-4 text-muted small">
                                    {{ $leadTotal > 0 ? round(($leadConverted/$leadTotal)*100,1) : 0 }}%
                                </td>
                            </tr>
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1">
                                        <i class="bi bi-circle-fill me-1" style="font-size:.45rem;vertical-align:middle"></i>Lost
                                    </span>
                                </td>
                                <td class="text-end fw-semibold">{{ number_format($leadLost) }}</td>
                                <td class="text-end pe-4 text-muted small">
                                    {{ $leadTotal > 0 ? round(($leadLost/$leadTotal)*100,1) : 0 }}%
                                </td>
                            </tr>
                            <tr class="table-light">
                                <td class="ps-4 fw-bold">Total</td>
                                <td class="text-end fw-bold">{{ number_format($leadTotal) }}</td>
                                <td class="text-end pe-4"></td>
                            </tr>
                        </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-top">
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted small">Conversion Rate:</span>
                        <span class="badge bg-success fs-6 px-3">{{ $conversionRate }}%</span>
                        <div class="flex-grow-1 ms-1">
                            <div class="progress" style="height:6px">
                                <div class="progress-bar bg-success" style="width:{{ $conversionRate }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Visit Doughnut Chart --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom fw-semibold py-3">
                    <i class="bi bi-pie-chart me-2 text-primary"></i>Visit Distribution
                </div>
                <div class="card-body d-flex align-items-center justify-content-center py-4">
                    <div style="max-width:280px;width:100%">
                        <canvas id="leadChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ======================== --}}
    {{-- SALES ANALYTICS SECTION --}}
    {{-- ======================== --}}
    <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">
        <i class="bi bi-graph-up-arrow me-2 text-success"></i>Sales Analytics
    </h5>

    <div class="row g-4 mb-5">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom fw-semibold py-3">
                    <i class="bi bi-file-earmark-text me-2 text-success"></i>Quotations Summary
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Metric</th>
                                <th class="text-end pe-4">Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="ps-4">
                                    <i class="bi bi-files me-2 text-muted"></i>Total Quotations
                                </td>
                                <td class="text-end pe-4 fw-semibold">
                                    {{ number_format($salesStats['quotations_count']) }}
                                </td>
                            </tr>
                            <tr>
                                <td class="ps-4">
                                    <i class="bi bi-currency-rupee me-2 text-muted"></i>Total Quotation Value
                                </td>
                                <td class="text-end pe-4 fw-semibold text-primary">
                                    ₹{{ number_format($salesStats['quotations_total'], 2) }}
                                </td>
                            </tr>
                            <tr>
                                <td class="ps-4">
                                    <i class="bi bi-check2-circle me-2 text-muted"></i>Approved Value
                                </td>
                                <td class="text-end pe-4 fw-semibold text-success">
                                    ₹{{ number_format($salesStats['approved_total'], 2) }}
                                </td>
                            </tr>
                            <tr class="table-light">
                                <td class="ps-4">
                                    <i class="bi bi-percent me-2 text-muted"></i>Approval Rate
                                </td>
                                <td class="text-end pe-4">
                                    @php
                                        $approvalRate = $salesStats['quotations_total'] > 0
                                            ? round(($salesStats['approved_total'] / $salesStats['quotations_total']) * 100, 1)
                                            : 0;
                                    @endphp
                                    <span class="badge bg-success px-3">{{ $approvalRate }}%</span>
                                </td>
                            </tr>
                        </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-center align-items-center text-center gap-3 py-4">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:80px;height:80px;background:rgba(25,135,84,.1)">
                        <i class="bi bi-trophy fs-2 text-success"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase mb-1">Approval Rate</div>
                        <div class="display-5 fw-bold text-success">{{ $approvalRate }}%</div>
                        <div class="text-muted small mt-1">of total quotation value approved</div>
                    </div>
                    <div class="w-100">
                        <div class="progress" style="height:10px;border-radius:8px">
                            <div class="progress-bar bg-success" style="width:{{ $approvalRate }}%;border-radius:8px"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ======================== --}}
    {{-- FINANCE SUMMARY SECTION --}}
    {{-- ======================== --}}
    <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">
        <i class="bi bi-wallet2 me-2 text-danger"></i>Finance Summary
    </h5>

    <div class="row g-4 mb-5">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-4 border-primary h-100">
                <div class="card-body">
                    <div class="text-muted small fw-semibold text-uppercase mb-2">
                        <i class="bi bi-file-earmark-check me-1"></i>Invoiced
                    </div>
                    <div class="fs-3 fw-bold text-primary">₹{{ number_format($financeStats['invoiced'], 0) }}</div>
                    <div class="text-muted small mt-1">Total invoices raised</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-4 border-success h-100">
                <div class="card-body">
                    <div class="text-muted small fw-semibold text-uppercase mb-2">
                        <i class="bi bi-cash-coin me-1"></i>Collected
                    </div>
                    <div class="fs-3 fw-bold text-success">₹{{ number_format($financeStats['collected'], 0) }}</div>
                    <div class="text-muted small mt-1">Payments received</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-4 border-warning h-100">
                <div class="card-body">
                    <div class="text-muted small fw-semibold text-uppercase mb-2">
                        <i class="bi bi-hourglass-split me-1"></i>Pending
                    </div>
                    <div class="fs-3 fw-bold text-warning">₹{{ number_format($financeStats['pending'], 0) }}</div>
                    <div class="text-muted small mt-1">Outstanding amount</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-4 border-danger h-100">
                <div class="card-body">
                    <div class="text-muted small fw-semibold text-uppercase mb-2">
                        <i class="bi bi-arrow-down-circle me-1"></i>Expenses
                    </div>
                    <div class="fs-3 fw-bold text-danger">₹{{ number_format($financeStats['expenses'], 0) }}</div>
                    <div class="text-muted small mt-1">Total expenditure</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================= --}}
    {{-- PROJECT STATUS SECTION   --}}
    {{-- ========================= --}}
    <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">
        <i class="bi bi-diagram-3 me-2 text-warning"></i>Project Status
    </h5>

    @php
        $projTotal      = $projectStats['total'] ?? 0;
        $projInProgress = $projectStats['in_progress'] ?? 0;
        $projCompleted  = $projectStats['completed'] ?? 0;
        $projPending    = max(0, $projTotal - $projInProgress - $projCompleted);
    @endphp

    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom fw-semibold py-3">
                    <i class="bi bi-list-check me-2 text-warning"></i>Project Breakdown
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Status</th>
                                <th class="text-end">Count</th>
                                <th class="text-end pe-4">Share</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1">
                                        <i class="bi bi-circle-fill me-1" style="font-size:.45rem;vertical-align:middle"></i>In Progress
                                    </span>
                                </td>
                                <td class="text-end fw-semibold">{{ number_format($projInProgress) }}</td>
                                <td class="text-end pe-4 text-muted small">
                                    {{ $projTotal > 0 ? round(($projInProgress/$projTotal)*100,1) : 0 }}%
                                </td>
                            </tr>
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-success bg-opacity-10 text-success px-2 py-1">
                                        <i class="bi bi-circle-fill me-1" style="font-size:.45rem;vertical-align:middle"></i>Completed
                                    </span>
                                </td>
                                <td class="text-end fw-semibold">{{ number_format($projCompleted) }}</td>
                                <td class="text-end pe-4 text-muted small">
                                    {{ $projTotal > 0 ? round(($projCompleted/$projTotal)*100,1) : 0 }}%
                                </td>
                            </tr>
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-warning bg-opacity-10 text-warning px-2 py-1">
                                        <i class="bi bi-circle-fill me-1" style="font-size:.45rem;vertical-align:middle"></i>Pending
                                    </span>
                                </td>
                                <td class="text-end fw-semibold">{{ number_format($projPending) }}</td>
                                <td class="text-end pe-4 text-muted small">
                                    {{ $projTotal > 0 ? round(($projPending/$projTotal)*100,1) : 0 }}%
                                </td>
                            </tr>
                            <tr class="table-light">
                                <td class="ps-4 fw-bold">Total</td>
                                <td class="text-end fw-bold">{{ number_format($projTotal) }}</td>
                                <td class="text-end pe-4"></td>
                            </tr>
                        </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-top">
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted small">Completion Rate:</span>
                        @php $completionRate = $projTotal > 0 ? round(($projCompleted/$projTotal)*100,1) : 0; @endphp
                        <span class="badge bg-success fs-6 px-3">{{ $completionRate }}%</span>
                        <div class="flex-grow-1 ms-1">
                            <div class="progress" style="height:6px">
                                <div class="progress-bar bg-success" style="width:{{ $completionRate }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Project Doughnut Chart --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom fw-semibold py-3">
                    <i class="bi bi-pie-chart me-2 text-warning"></i>Project Distribution
                </div>
                <div class="card-body d-flex align-items-center justify-content-center py-4">
                    <div style="max-width:280px;width:100%">
                        <canvas id="projectChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    // ── Visit Doughnut Chart ──────────────────────────────────────────────────
    const leadCtx = document.getElementById('leadChart');
    if (leadCtx) {
        new Chart(leadCtx, {
            type: 'doughnut',
            data: {
                labels: ['Converted', 'Lost', 'Active'],
                datasets: [{
                    data: [
                        {{ $leadConverted }},
                        {{ $leadLost }},
                        {{ $leadActive }}
                    ],
                    backgroundColor: ['#16a34a', '#ef4444', '#0ea5e9'],
                    borderColor: ['#fff', '#fff', '#fff'],
                    borderWidth: 3,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 16,
                            usePointStyle: true,
                            font: { size: 13 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const pct   = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
                                return ` ${ctx.label}: ${ctx.parsed} (${pct}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    // ── Project Doughnut Chart ───────────────────────────────────────────────
    const projCtx = document.getElementById('projectChart');
    if (projCtx) {
        new Chart(projCtx, {
            type: 'doughnut',
            data: {
                labels: ['In Progress', 'Completed', 'Pending'],
                datasets: [{
                    data: [
                        {{ $projInProgress }},
                        {{ $projCompleted }},
                        {{ $projPending }}
                    ],
                    backgroundColor: ['#0ea5e9', '#16a34a', '#f59e0b'],
                    borderColor: ['#fff', '#fff', '#fff'],
                    borderWidth: 3,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 16,
                            usePointStyle: true,
                            font: { size: 13 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const pct   = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
                                return ` ${ctx.label}: ${ctx.parsed} (${pct}%)`;
                            }
                        }
                    }
                }
            }
        });
    }
})();
</script>
@endpush
