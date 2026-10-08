@extends('layouts.app')
@section('title', 'Payment — ' . $payment->payment_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-cash-stack text-success me-2"></i>{{ $payment->payment_number }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-0"><li class="breadcrumb-item"><a href="{{ route('payments.index') }}" class="text-success text-decoration-none">Payments</a></li><li class="breadcrumb-item active">{{ $payment->payment_number }}</li></ol></nav>
    </div>
    <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle text-success me-2"></i>Payment Receipt Details</h6>
        <span class="badge bg-success">Received</span>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-sm-6"><label class="text-muted small d-block">Client</label><span class="fw-semibold">{{ $payment->customer?->name }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Invoice</label><span>{{ $payment->invoice?->invoice_number ?? 'Advance / General' }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Payment Date</label><span>{{ $payment->payment_date?->format('d M Y') }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Amount Received</label><span class="fw-bold text-success fs-5">₹{{ number_format($payment->amount, 2) }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Method</label><span class="badge bg-light text-dark border">{{ $payment->payment_method }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Reference / Transaction</label><span>{{ $payment->reference_number ?: '—' }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Recorded By</label><span>{{ $payment->recordedBy?->name ?? 'Staff' }}</span></div>
            @if($payment->notes)
            <div class="col-12"><label class="text-muted small d-block">Notes</label><div class="bg-light p-3 rounded small">{{ $payment->notes }}</div></div>
            @endif
        </div>
    </div>
</div>
@endsection
