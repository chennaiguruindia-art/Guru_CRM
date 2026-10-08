@extends('layouts.app')
@section('title', 'Work Orders')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-briefcase-fill text-success me-2"></i>Work Orders</h4>
        <p class="text-muted small mb-0">Track on-site operations and field execution work orders.</p>
    </div>
    <a href="{{ route('work-orders.create') }}" class="btn btn-success shadow-sm">
        <i class="bi bi-plus-lg me-1"></i> New Work Order
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-crm align-middle mb-0">
            <thead>
                <tr>
                    <th>WO Number</th>
                    <th>Client</th>
                    <th>Supervisor</th>
                    <th>Start Date</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($workOrders as $wo)
                <tr>
                    <td><a href="{{ route('work-orders.show', $wo) }}" class="fw-semibold text-success text-decoration-none">{{ $wo->wo_number }}</a></td>
                    <td>{{ $wo->customer?->name ?? '—' }}</td>
                    <td>{{ $wo->supervisor?->name ?? '—' }}</td>
                    <td class="small">{{ $wo->start_date?->format('d M Y') ?? '—' }}</td>
                    <td>
                        <span class="badge {{ $wo->status === 'Completed' ? 'bg-success' : 'bg-primary' }}">{{ $wo->status }}</span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('work-orders.show', $wo) }}" class="btn btn-sm btn-outline-success"><i class="bi bi-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <i class="bi bi-briefcase fs-1 text-muted d-block mb-2"></i>
                        <span class="text-muted">No work orders issued yet. <a href="{{ route('work-orders.create') }}">Create one now</a>.</span>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($workOrders->hasPages())
    <div class="card-footer bg-white py-3">{{ $workOrders->withQueryString()->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
