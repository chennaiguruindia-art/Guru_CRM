@extends('layouts.app')
@section('title', 'Invoice — ' . $invoice->invoice_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-receipt text-success me-2"></i>{{ $invoice->invoice_number }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-0"><li class="breadcrumb-item"><a href="{{ route('invoices.index') }}" class="text-success text-decoration-none">Invoices</a></li><li class="breadcrumb-item active">{{ $invoice->invoice_number }}</li></ol></nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('payments.create') }}?customer_id={{ $invoice->customer_id }}&invoice_id={{ $invoice->id }}" class="btn btn-success btn-sm">
            <i class="bi bi-cash me-1"></i> Record Payment
        </a>
        <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle text-success me-2"></i>Invoice Details</h6>
                <span class="badge {{ $invoice->payment_status === 'paid' ? 'bg-success' : 'bg-danger' }}">{{ ucfirst($invoice->payment_status) }}</span>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-sm-6"><label class="text-muted small d-block">Client</label><span class="fw-semibold">{{ $invoice->customer?->name }}</span></div>
                    <div class="col-sm-6"><label class="text-muted small d-block">Invoice Date</label><span>{{ $invoice->invoice_date?->format('d M Y') }}</span></div>
                    <div class="col-sm-6"><label class="text-muted small d-block">Due Date</label><span class="{{ $invoice->due_date?->isPast() && $invoice->payment_status !== 'paid' ? 'text-danger fw-semibold' : '' }}">{{ $invoice->due_date?->format('d M Y') }}</span></div>
                    @if($invoice->terms)
                    <div class="col-12"><label class="text-muted small d-block">Terms & Conditions</label><p class="bg-light p-3 rounded mb-0 small">{{ $invoice->terms }}</p></div>
                    @endif
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-cash-stack text-success me-2"></i>Payments Received Against This Invoice</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>Payment No</th><th>Date</th><th>Method</th><th>Ref No</th><th class="text-end">Amount</th></tr></thead>
                    <tbody>
                        @forelse($invoice->payments as $p)
                        <tr>
                            <td class="fw-semibold text-success">{{ $p->payment_number }}</td>
                            <td class="small">{{ $p->payment_date?->format('d M Y') }}</td>
                            <td>{{ $p->payment_method }}</td>
                            <td class="small">{{ $p->reference_number ?: '—' }}</td>
                            <td class="text-end fw-bold text-success">₹{{ number_format($p->amount, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-4 text-muted">No payments logged yet for this invoice.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">Balance Summary</h6>
            </div>
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total Amount:</span>
                    <span class="fw-semibold">₹{{ number_format($invoice->total_amount, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Paid to Date:</span>
                    <span class="fw-semibold text-success">₹{{ number_format($invoice->paid_amount, 2) }}</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Outstanding Due:</span>
                    <span class="fw-bold text-danger fs-5">₹{{ number_format($invoice->balance_amount, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
