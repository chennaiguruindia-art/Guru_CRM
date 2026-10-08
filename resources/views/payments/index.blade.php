@extends('layouts.app')
@section('title', 'Payments')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-cash-stack text-success me-2"></i>Payments Received</h4>
        <p class="text-muted small mb-0">Record and track client collections, receipts, and invoices cleared.</p>
    </div>
    <a href="{{ route('payments.create') }}" class="btn btn-success shadow-sm">
        <i class="bi bi-plus-lg me-1"></i> Record Payment
    </a>
</div>

{{-- KPI cards --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <span class="text-muted small d-block">Total Collections Received</span>
                <h3 class="fw-bold mb-0 text-success">₹{{ number_format($totalCollected, 2) }}</h3>
            </div>
            <div class="kpi-icon-box bg-soft-success"><i class="bi bi-cash fs-4 text-success"></i></div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-crm align-middle mb-0">
            <thead>
                <tr>
                    <th>Payment No</th>
                    <th>Client</th>
                    <th>Invoice No</th>
                    <th>Date</th>
                    <th>Payment Method</th>
                    <th>Ref / Transaction No</th>
                    <th>Amount</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $pay)
                <tr>
                    <td><a href="{{ route('payments.show', $pay) }}" class="fw-semibold text-success text-decoration-none">{{ $pay->payment_number }}</a></td>
                    <td>{{ $pay->customer?->name ?? '—' }}</td>
                    <td>
                        @if($pay->invoice)
                            <a href="{{ route('invoices.show', $pay->invoice) }}" class="text-decoration-none">{{ $pay->invoice->invoice_number }}</a>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="small">{{ $pay->payment_date?->format('d M Y') }}</td>
                    <td><span class="badge bg-light text-dark border">{{ $pay->payment_method }}</span></td>
                    <td class="small">{{ $pay->reference_number ?: '—' }}</td>
                    <td class="fw-bold text-success">₹{{ number_format($pay->amount, 2) }}</td>
                    <td class="text-end">
                        <a href="{{ route('payments.show', $pay) }}" class="btn btn-sm btn-outline-success"><i class="bi bi-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <i class="bi bi-cash-stack fs-1 text-muted d-block mb-2"></i>
                        <span class="text-muted">No payments logged yet. <a href="{{ route('payments.create') }}">Record a payment</a>.</span>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($payments->hasPages())
    <div class="card-footer bg-white py-3">{{ $payments->withQueryString()->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
