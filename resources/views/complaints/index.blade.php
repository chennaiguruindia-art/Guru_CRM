@extends('layouts.app')

@section('title', 'Complaints')

@section('content')
<div class="container-fluid">

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Page Header --}}
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 mb-0 fw-bold text-dark">
                <i class="bi bi-ticket-perforated me-2 text-danger"></i>Complaints
            </h1>
            <p class="text-muted mb-0 small">Manage and track client complaints and support tickets.</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('complaints.create') }}" class="btn btn-danger">
                <i class="bi bi-plus-circle me-1"></i>Log Complaint
            </a>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-warning bg-opacity-10 p-3">
                        <i class="bi bi-exclamation-circle-fill fs-3 text-warning"></i>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold text-warning">{{ $openCount }}</div>
                        <div class="text-muted small">Open Tickets</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-success bg-opacity-10 p-3">
                        <i class="bi bi-check-circle-fill fs-3 text-success"></i>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold text-success">{{ $resolvedCount }}</div>
                        <div class="text-muted small">Resolved</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-primary bg-opacity-10 p-3">
                        <i class="bi bi-ticket-perforated-fill fs-3 text-primary"></i>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold text-primary">{{ $complaints->total() }}</div>
                        <div class="text-muted small">Total Tickets</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Search & Filter --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('complaints.index') }}" class="row g-3 align-items-end">
                <div class="col-12 col-md-6">
                    <label for="search" class="form-label fw-semibold small text-muted">Search</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text"
                               id="search"
                               name="search"
                               class="form-control border-start-0"
                               placeholder="Search by ticket #, client, or subject…"
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <label for="status" class="form-label fw-semibold small text-muted">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="Open"        {{ request('status') === 'Open'        ? 'selected' : '' }}>Open</option>
                        <option value="Assigned"    {{ request('status') === 'Assigned'    ? 'selected' : '' }}>Assigned</option>
                        <option value="In Progress" {{ request('status') === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="Resolved"    {{ request('status') === 'Resolved'    ? 'selected' : '' }}>Resolved</option>
                        <option value="Closed"      {{ request('status') === 'Closed'      ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                    <a href="{{ route('complaints.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Complaints Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-semibold">
                <i class="bi bi-list-ul me-2 text-muted"></i>Complaints List
            </h6>
            <span class="badge bg-secondary rounded-pill">{{ $complaints->total() }} record(s)</span>
        </div>
        <div class="card-body p-0">
            @if($complaints->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4 text-muted small fw-semibold text-uppercase">Ticket #</th>
                                <th class="text-muted small fw-semibold text-uppercase">Client</th>
                                <th class="text-muted small fw-semibold text-uppercase">Subject</th>
                                <th class="text-muted small fw-semibold text-uppercase">Priority</th>
                                <th class="text-muted small fw-semibold text-uppercase">Status</th>
                                <th class="text-muted small fw-semibold text-uppercase">Assigned To</th>
                                <th class="text-muted small fw-semibold text-uppercase">Created</th>
                                <th class="text-muted small fw-semibold text-uppercase text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($complaints as $c)
                                <tr>
                                    <td class="ps-4">
                                        <span class="fw-semibold font-monospace text-primary">
                                            {{ $c->ticket_number }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ optional($c->customer)->name ?? '—' }}</div>
                                    </td>
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width:200px;" title="{{ $c->subject }}">
                                            {{ $c->subject }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $priorityClass = match($c->priority) {
                                                'Critical' => 'danger',
                                                'High'     => 'warning',
                                                'Medium'   => 'info',
                                                'Low'      => 'secondary',
                                                default    => 'secondary',
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $priorityClass }}">{{ $c->priority }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = match($c->status) {
                                                'Open'        => 'danger',
                                                'Assigned'    => 'warning',
                                                'In Progress' => 'info',
                                                'Resolved'    => 'success',
                                                'Closed'      => 'secondary',
                                                default       => 'secondary',
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $statusClass }}">{{ $c->status }}</span>
                                    </td>
                                    <td>{{ optional($c->assignedTo)->name ?? '—' }}</td>
                                    <td class="text-muted small">
                                        {{ $c->created_at->format('d M Y') }}
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('complaints.show', $c) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="View Complaint">
                                            <i class="bi bi-eye me-1"></i>View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($complaints->hasPages())
                    <div class="px-4 py-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="text-muted small">
                            Showing {{ $complaints->firstItem() }}–{{ $complaints->lastItem() }}
                            of {{ $complaints->total() }} complaints
                        </div>
                        {{ $complaints->appends(request()->query())->links() }}
                    </div>
                @endif

            @else
                {{-- Empty State --}}
                <div class="text-center py-5 my-3">
                    <i class="bi bi-ticket-perforated display-1 text-muted opacity-25"></i>
                    <h5 class="mt-3 text-muted">No complaints found</h5>
                    <p class="text-muted small mb-4">
                        @if(request('search') || request('status'))
                            No complaints match your current filters. Try adjusting your search.
                        @else
                            No complaints have been logged yet.
                        @endif
                    </p>
                    @if(request('search') || request('status'))
                        <a href="{{ route('complaints.index') }}" class="btn btn-outline-secondary me-2">
                            <i class="bi bi-x-circle me-1"></i>Clear Filters
                        </a>
                    @endif
                    <a href="{{ route('complaints.create') }}" class="btn btn-danger">
                        <i class="bi bi-plus-circle me-1"></i>Log First Complaint
                    </a>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
