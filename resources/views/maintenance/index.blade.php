@extends('layouts.app')
@section('title', 'Garden Maintenance')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-tools text-success me-2"></i>Garden Maintenance</h4>
        <p class="text-muted small mb-0">Record daily garden maintenance visits, watering, pruning, pest control and tasks.</p>
    </div>
    <a href="{{ route('maintenance.create') }}" class="btn btn-success shadow-sm">
        <i class="bi bi-plus-lg me-1"></i> Log Maintenance Visit
    </a>
</div>

{{-- Upcoming Scheduled Visits --}}
@if($schedules->count())
<div class="card border-0 shadow-sm mb-4 border-start border-warning border-4">
    <div class="card-body p-3">
        <h6 class="fw-bold mb-2 text-dark"><i class="bi bi-calendar-event text-warning me-2"></i>Upcoming AMC Scheduled Visits</h6>
        <div class="row g-2">
            @foreach($schedules as $sch)
            <div class="col-md-4">
                <div class="bg-light p-2 rounded small">
                    <span class="fw-semibold text-dark">{{ $sch->contract?->customer?->name }}</span>
                    <div class="text-muted"><strong>{{ $sch->scheduled_date?->format('d M Y') }}</strong></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-crm align-middle mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Client</th>
                    <th>Activity</th>
                    <th>Gardener / Supervisor</th>
                    <th>Approval</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $rec)
                <tr>
                    <td class="small fw-semibold">{{ $rec->visit_date?->format('d M Y') }}</td>
                    <td>{{ $rec->customer?->name ?? '—' }}</td>
                    <td><span class="badge bg-light text-dark border">{{ $rec->activity_type }}</span></td>
                    <td class="small">{{ $rec->employee?->name ?? 'Assigned Staff' }}</td>
                    <td>
                        <span class="badge {{ $rec->customer_approval ? 'bg-success' : 'bg-warning text-dark' }}">
                            {{ $rec->customer_approval ? 'Approved' : 'Pending' }}
                        </span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('maintenance.show', $rec) }}" class="btn btn-sm btn-outline-success"><i class="bi bi-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <i class="bi bi-tools fs-1 text-muted d-block mb-2"></i>
                        <span class="text-muted">No maintenance records logged. <a href="{{ route('maintenance.create') }}">Log first visit</a>.</span>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($records->hasPages())
    <div class="card-footer bg-white py-3">{{ $records->withQueryString()->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
