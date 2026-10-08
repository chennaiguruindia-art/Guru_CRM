@extends('layouts.app')
@section('title', 'Maintenance Record')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-tools text-success me-2"></i>Maintenance Record</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-0"><li class="breadcrumb-item"><a href="{{ route('maintenance.index') }}" class="text-success text-decoration-none">Maintenance</a></li><li class="breadcrumb-item active">Record #{{ $maintenance->id }}</li></ol></nav>
    </div>
    <a href="{{ route('maintenance.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle text-success me-2"></i>Record Details</h6>
        <span class="badge bg-light text-dark border">{{ $maintenance->activity_type }}</span>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-sm-6"><label class="text-muted small d-block">Client</label><span class="fw-semibold">{{ $maintenance->customer?->name }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Visit Date</label><span>{{ $maintenance->visit_date?->format('d M Y') }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Staff / Supervisor</label><span>{{ $maintenance->employee?->name ?? 'Staff' }}</span></div>
            @if($maintenance->area_details)
            <div class="col-12"><label class="text-muted small d-block">Area Details</label><p class="bg-light p-3 rounded mb-0 small">{{ $maintenance->area_details }}</p></div>
            @endif
            @if($maintenance->work_description)
            <div class="col-12"><label class="text-muted small d-block">Work Description</label><div class="bg-light p-3 rounded small">{!! nl2br(e($maintenance->work_description)) !!}</div></div>
            @endif
        </div>
    </div>
</div>
@endsection
