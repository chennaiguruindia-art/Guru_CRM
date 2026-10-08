@extends('layouts.app')
@section('title', 'Invoices')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-receipt text-success me-2"></i>Invoices & Billing</h4>
        <p class="text-muted small mb-0">Generate GST-compliant tax invoices and track payment balances.</p>
    </div>
    <a href="{{ route('invoices.create') }}" class="btn btn-success shadow-sm">
        <i class="bi bi-plus-lg me-1"></i> Create Invoice
    </a>
</div>

{{-- KPI cards --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-4">
        <div class="card border-0 shadow-sm p-3">
            <span class="text-muted small">Total Invoiced</span>
            <h4 class="fw-bold mb-0 text-dark">₹{{ number_format($totalInvoiced, 2) }}</h4>
        </div>
    </div>
    <div class="col-12 col-sm-4">
        <div class="card border-0 shadow-sm p-3">
            <span class="text-muted small">Total Paid</span>
            <h4 class="fw-bold mb-0 text-success">₹{{ number_format($totalPaid, 2) }}</h4>
        </div>
    </div>
    <div class="col-12 col-sm-4">
        <div class="card border-0 shadow-sm p-3">
            <span class="text-muted small">Pending Balance</span>
            <h4 class="fw-bold mb-0 text-danger">₹{{ number_format($totalPending, 2) }}</h4>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-crm align-middle mb-0">
            <thead>
                <tr>
                    <th>Invoice No</th>
                    <th>Client</th>
                    <th>Invoice Date</th>
                    <th>Due Date</th>
                    <th>Total</th>
                    <th>Paid</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $inv)
                <tr>
                    <td><a href="{{ route('invoices.show', $inv) }}" class="fw-semibold text-success text-decoration-none">{{ $inv->invoice_number }}</a></td>
                    <td>{{ $inv->customer?->name ?? '—' }}</td>
                    <td class="small">{{ $inv->invoice_date?->format('d M Y') }}</td>
                    <td class="small">{{ $inv->due_date?->format('d M Y') }}</td>
                    <td class="fw-bold">₹{{ number_format($inv->total_amount, 2) }}</td>
                    <td class="text-success">₹{{ number_format($inv->paid_amount, 2) }}</td>
                    <td class="fw-semibold text-danger">₹{{ number_format($inv->balance_amount, 2) }}</td>
                    <td>
                        <span class="badge {{ $inv->payment_status === 'paid' ? 'bg-success' : ($inv->payment_status === 'partial' ? 'bg-warning text-dark' : 'bg-danger') }}">
                            {{ ucfirst($inv->payment_status) }}
                        </span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('invoices.show', $inv) }}" class="btn btn-sm btn-outline-success"><i class="bi bi-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5">
                        <i class="bi bi-receipt fs-1 text-muted d-block mb-2"></i>
                        <span class="text-muted">No invoices generated yet. <a href="{{ route('invoices.create') }}">Create an invoice</a>.</span>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($invoices->hasPages())
    <div class="card-footer bg-white py-3">{{ $invoices->withQueryString()->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
