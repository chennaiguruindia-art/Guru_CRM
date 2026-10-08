@extends('layouts.app')
@section('title', 'Work Order — ' . $workOrder->wo_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-briefcase-fill text-success me-2"></i>{{ $workOrder->wo_number }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-0"><li class="breadcrumb-item"><a href="{{ route('work-orders.index') }}" class="text-success text-decoration-none">Work Orders</a></li><li class="breadcrumb-item active">{{ $workOrder->wo_number }}</li></ol></nav>
    </div>
    <a href="{{ route('work-orders.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle text-success me-2"></i>Work Order Details</h6>
        <span class="badge {{ $workOrder->status === 'Completed' ? 'bg-success' : 'bg-primary' }}">{{ $workOrder->status }}</span>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-sm-6"><label class="text-muted small d-block">Client</label><span class="fw-semibold">{{ $workOrder->customer?->name }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Project</label><span>{{ $workOrder->project?->name ?? '—' }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Supervisor</label><span>{{ $workOrder->supervisor?->name ?? '—' }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Start Date</label><span>{{ $workOrder->start_date?->format('d M Y') ?? '—' }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">End Date</label><span>{{ $workOrder->end_date?->format('d M Y') ?? '—' }}</span></div>
            @if($workOrder->scope_of_work)
            <div class="col-12"><label class="text-muted small d-block">Scope of Work</label><div class="bg-light p-3 rounded small">{!! nl2br(e($workOrder->scope_of_work)) !!}</div></div>
            @endif
            @if($workOrder->remarks)
            <div class="col-12"><label class="text-muted small d-block">Remarks</label><div class="bg-light p-3 rounded small">{{ $workOrder->remarks }}</div></div>
            @endif
        </div>
    </div>
</div>
@endsection
