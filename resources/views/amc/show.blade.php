@extends('layouts.app')
@section('title', 'AMC Contract — ' . $amc->amc_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-shield-check text-success me-2"></i>{{ $amc->amc_number }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-0"><li class="breadcrumb-item"><a href="{{ route('amc.index') }}" class="text-success text-decoration-none">AMC Contracts</a></li><li class="breadcrumb-item active">{{ $amc->amc_number }}</li></ol></nav>
    </div>
    <a href="{{ route('amc.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle text-success me-2"></i>Contract Summary</h6>
        <span class="badge {{ $amc->status === 'Active' ? 'bg-success' : 'bg-warning text-dark' }}">{{ $amc->status }}</span>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-sm-6"><label class="text-muted small d-block">Client</label><span class="fw-semibold">{{ $amc->customer?->name }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Period</label><span>{{ $amc->start_date?->format('d M Y') }} to {{ $amc->end_date?->format('d M Y') }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Annual Value</label><span class="fw-bold text-success fs-5">₹{{ number_format($amc->contract_value, 2) }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Billing Frequency</label><span>{{ $amc->billing_frequency }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Service Frequency</label><span>{{ $amc->service_frequency }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Assigned Team Lead</label><span>{{ $amc->teamLead?->name ?? 'Staff' }}</span></div>
            @if($amc->scope_of_work)
            <div class="col-12"><label class="text-muted small d-block">Scope of Work</label><div class="bg-light p-3 rounded small">{!! nl2br(e($amc->scope_of_work)) !!}</div></div>
            @endif
        </div>
    </div>
</div>
@endsection
