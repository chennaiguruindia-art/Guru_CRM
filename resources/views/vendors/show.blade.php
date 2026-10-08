@extends('layouts.app')
@section('title', 'Vendor — ' . $vendor->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-shop text-success me-2"></i>{{ $vendor->name }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-0"><li class="breadcrumb-item"><a href="{{ route('vendors.index') }}" class="text-success text-decoration-none">Vendors</a></li><li class="breadcrumb-item active">{{ $vendor->vendor_code }}</li></ol></nav>
    </div>
    <a href="{{ route('vendors.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle text-success me-2"></i>Vendor Information</h6>
        <span class="badge {{ $vendor->status === 'active' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($vendor->status) }}</span>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-sm-6"><label class="text-muted small d-block">Vendor Code</label><span class="fw-semibold text-success">{{ $vendor->vendor_code }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Contact Person</label><span>{{ $vendor->contact_person ?: '—' }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Phone</label><span>{{ $vendor->phone }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Email</label><span>{{ $vendor->email ?: '—' }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">GST Number</label><span>{{ $vendor->gst_number ?: '—' }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Payment Terms</label><span>{{ $vendor->payment_terms ?: '—' }}</span></div>
            @if($vendor->address)
            <div class="col-12"><label class="text-muted small d-block">Address</label><p class="bg-light p-3 rounded mb-0 small">{{ $vendor->address }}, {{ $vendor->city }}, {{ $vendor->state }}</p></div>
            @endif
        </div>
    </div>
</div>
@endsection
