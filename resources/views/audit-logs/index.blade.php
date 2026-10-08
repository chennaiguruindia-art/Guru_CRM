@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')
<div class="container-fluid px-4 py-3">

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h2 class="fw-bold mb-0">
                <i class="bi bi-journal-text me-2 text-primary"></i>Audit Trail
                <span class="badge bg-secondary fw-normal fs-6 ms-2">(Read-only)</span>
            </h2>
            <nav aria-label="breadcrumb" class="mt-1">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Audit Logs</li>
                </ol>
            </nav>
        </div>

        {{-- Export CSV Button --}}
        <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}"
           class="btn btn-outline-success">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export to CSV
        </a>
    </div>

    {{-- Info Notice --}}
    <div class="alert alert-info d-flex align-items-start gap-2 mb-4 border-0 shadow-sm">
        <i class="bi bi-info-circle-fill flex-shrink-0 mt-1 text-info"></i>
        <div class="small">
            This log records all <strong>create</strong>, <strong>update</strong>, and <strong>delete</strong>
            actions performed in the CRM. Records are read-only and cannot be modified or deleted.
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('audit-logs.index') }}" id="filterForm">
                <div class="row g-2 align-items-end">

                    {{-- Action Filter --}}
                    <div class="col-sm-6 col-md-3">
                        <label for="filterAction" class="form-label small fw-semibold mb-1">Action</label>
                        <select class="form-select form-select-sm"
                                id="filterAction"
                                name="action"
                                onchange="this.form.submit()">
                            <option value="">All Actions</option>
                            <option value="create"  {{ request('action') === 'create'  ? 'selected' : '' }}>Create</option>
                            <option value="update"  {{ request('action') === 'update'  ? 'selected' : '' }}>Update</option>
                            <option value="delete"  {{ request('action') === 'delete'  ? 'selected' : '' }}>Delete</option>
                            <option value="view"    {{ request('action') === 'view'    ? 'selected' : '' }}>View</option>
                        </select>
                    </div>

                    {{-- Module Filter --}}
                    <div class="col-sm-6 col-md-3">
                        <label for="filterModule" class="form-label small fw-semibold mb-1">Module</label>
                        <input type="text"
                               class="form-control form-control-sm"
                               id="filterModule"
                               name="module"
                               value="{{ request('module') }}"
                               placeholder="e.g. Clients, Orders…">
                    </div>

                    {{-- Search --}}
                    <div class="col-md-4">
                        <label for="filterSearch" class="form-label small fw-semibold mb-1">Search</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text"
                                   class="form-control"
                                   id="filterSearch"
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="User, record ID, IP…">
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm flex-fill">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        <a href="{{ route('audit-logs.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- Audit Logs Table Card --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
            <h5 class="mb-0 fw-semibold">
                <i class="bi bi-list-ul me-2 text-secondary"></i>Log Entries
            </h5>
            <span class="badge bg-light text-muted border">
                {{ $logs->total() }} total record{{ $logs->total() !== 1 ? 's' : '' }}
            </span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="width: 50px;">#</th>
                            <th style="min-width: 160px;">User</th>
                            <th style="width: 110px;">Action</th>
                            <th>Module</th>
                            <th class="text-center" style="width: 100px;">Record ID</th>
                            <th style="width: 130px;">IP Address</th>
                            <th style="min-width: 160px;">Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        @php
                            $actionBadge = match(strtolower($log->action ?? '')) {
                                'create' => 'bg-success',
                                'update' => 'bg-warning text-dark',
                                'delete' => 'bg-danger',
                                'view'   => 'bg-info text-dark',
                                default  => 'bg-secondary',
                            };
                        @endphp
                        <tr>
                            {{-- Row Number --}}
                            <td class="ps-4 text-muted small">
                                {{ ($logs->currentPage() - 1) * $logs->perPage() + $loop->iteration }}
                            </td>

                            {{-- User --}}
                            <td>
                                @if($log->user)
                                <div class="d-flex align-items-center gap-2">
                                    <span class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex
                                          align-items-center justify-content-center flex-shrink-0"
                                          style="width:32px; height:32px; font-size:.85rem;">
                                        <i class="bi bi-person"></i>
                                    </span>
                                    <div>
                                        <div class="fw-semibold small">{{ $log->user->name }}</div>
                                        <div class="text-muted" style="font-size:.75rem;">{{ $log->user->email }}</div>
                                    </div>
                                </div>
                                @else
                                <span class="text-muted small fst-italic">System / Guest</span>
                                @endif
                            </td>

                            {{-- Action Badge --}}
                            <td>
                                <span class="badge {{ $actionBadge }} text-capitalize">
                                    {{ $log->action ?? 'N/A' }}
                                </span>
                            </td>

                            {{-- Module --}}
                            <td>
                                <span class="small">{{ $log->module ? \App\Services\ModuleLabel::for($log->module) : '—' }}</span>
                            </td>

                            {{-- Record ID --}}
                            <td class="text-center">
                                @if($log->record_id)
                                    <code class="small bg-light px-2 py-1 rounded">#{{ $log->record_id }}</code>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- IP Address --}}
                            <td>
                                @if(!empty($log->ip_address))
                                    <span class="small font-monospace text-muted">{{ $log->ip_address }}</span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>

                            {{-- Timestamp --}}
                            <td>
                                <span class="small" title="{{ $log->created_at }}">
                                    <i class="bi bi-clock me-1 text-muted"></i>
                                    {{ $log->created_at->format('d M Y, H:i') }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-journal-text fs-1 d-block mb-3 text-muted opacity-50"></i>
                                <p class="mb-1 fw-semibold">No audit log entries found</p>
                                @if(request()->hasAny(['action', 'module', 'search']))
                                    <p class="small mb-2">No records match the current filters.</p>
                                    <a href="{{ route('audit-logs.index') }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Clear Filters
                                    </a>
                                @else
                                    <p class="small">Actions performed in the CRM will appear here.</p>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination Footer --}}
        @if($logs->hasPages())
        <div class="card-footer bg-white border-top py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="text-muted small">
                Showing
                <strong>{{ $logs->firstItem() }}</strong>–<strong>{{ $logs->lastItem() }}</strong>
                of <strong>{{ $logs->total() }}</strong> entries
            </div>
            <div>
                {{ $logs->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @endif

    </div>{{-- /card --}}

</div>
@endsection
