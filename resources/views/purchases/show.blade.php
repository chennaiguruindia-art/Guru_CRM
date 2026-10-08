@extends('layouts.app')
@section('title', 'PO — ' . $purchase->po_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-cart-check-fill text-success me-2"></i>{{ $purchase->po_number }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-0"><li class="breadcrumb-item"><a href="{{ route('purchases.index') }}" class="text-success text-decoration-none">Purchases</a></li><li class="breadcrumb-item active">{{ $purchase->po_number }}</li></ol></nav>
    </div>
    <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle text-success me-2"></i>Purchase Order Summary</h6>
        <span class="badge bg-info text-dark">{{ $purchase->status }}</span>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-sm-6"><label class="text-muted small d-block">Vendor</label><span class="fw-semibold">{{ $purchase->vendor?->name }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">PO Date</label><span>{{ $purchase->po_date?->format('d M Y') }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Expected Delivery</label><span>{{ $purchase->delivery_date?->format('d M Y') ?? '—' }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Total Value</label><span class="fw-bold text-success fs-5">₹{{ number_format($purchase->total_amount, 2) }}</span></div>
            @if($purchase->notes)
            <div class="col-12"><label class="text-muted small d-block">Notes</label><div class="bg-light p-3 rounded small">{{ $purchase->notes }}</div></div>
            @endif
        </div>
    </div>
</div>
@endsection
