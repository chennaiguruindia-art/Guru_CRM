@extends('layouts.app')
@section('title', 'Purchases')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-cart-check-fill text-success me-2"></i>Purchase Orders</h4>
        <p class="text-muted small mb-0">Manage procurement of plants, pots, fertilizers, and landscaping materials.</p>
    </div>
    <a href="{{ route('purchases.create') }}" class="btn btn-success shadow-sm">
        <i class="bi bi-plus-lg me-1"></i> New Purchase Order
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-crm align-middle mb-0">
            <thead>
                <tr>
                    <th>PO Number</th>
                    <th>Vendor</th>
                    <th>PO Date</th>
                    <th>Delivery Date</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $po)
                <tr>
                    <td><a href="{{ route('purchases.show', $po) }}" class="fw-semibold text-success text-decoration-none">{{ $po->po_number }}</a></td>
                    <td>{{ $po->vendor?->name ?? '—' }}</td>
                    <td class="small">{{ $po->po_date?->format('d M Y') }}</td>
                    <td class="small">{{ $po->delivery_date?->format('d M Y') ?? '—' }}</td>
                    <td class="fw-bold text-success">₹{{ number_format($po->total_amount, 2) }}</td>
                    <td><span class="badge bg-info text-dark">{{ $po->status }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('purchases.show', $po) }}" class="btn btn-sm btn-outline-success"><i class="bi bi-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="bi bi-cart-check fs-1 text-muted d-block mb-2"></i>
                        <span class="text-muted">No purchase orders found. <a href="{{ route('purchases.create') }}">Create one</a>.</span>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($purchases->hasPages())
    <div class="card-footer bg-white py-3">{{ $purchases->withQueryString()->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
