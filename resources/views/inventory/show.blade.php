@extends('layouts.app')
@section('title', 'Item — ' . $inventory->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-boxes text-success me-2"></i>{{ $inventory->name }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-0"><li class="breadcrumb-item"><a href="{{ route('inventory.index') }}" class="text-success text-decoration-none">Inventory</a></li><li class="breadcrumb-item active">{{ $inventory->sku }}</li></ol></nav>
    </div>
    <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle text-success me-2"></i>Stock Information</h6>
                <span class="badge {{ $inventory->status === 'active' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($inventory->status) }}</span>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-sm-6"><label class="text-muted small d-block">SKU</label><span class="fw-semibold text-success">{{ $inventory->sku }}</span></div>
                    <div class="col-sm-6"><label class="text-muted small d-block">Category</label><span>{{ $inventory->category?->name ?? 'General' }}</span></div>
                    <div class="col-sm-6"><label class="text-muted small d-block">Current Stock</label><span class="fs-4 fw-bold {{ $inventory->current_stock <= $inventory->minimum_stock ? 'text-danger' : 'text-dark' }}">{{ $inventory->current_stock }} {{ $inventory->unit }}</span></div>
                    <div class="col-sm-6"><label class="text-muted small d-block">Minimum Threshold</label><span>{{ $inventory->minimum_stock }} {{ $inventory->unit }}</span></div>
                    <div class="col-sm-6"><label class="text-muted small d-block">Warehouse / Nursery</label><span>{{ $inventory->warehouse?->name ?? 'Main' }}</span></div>
                    <div class="col-sm-6"><label class="text-muted small d-block">Rack / Location</label><span>{{ $inventory->rack ?: '—' }}</span></div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-clock-history text-info me-2"></i>Stock Transactions</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>Date</th><th>Type</th><th>Qty</th><th>Logged By</th></tr></thead>
                    <tbody>
                        @forelse($inventory->transactions as $tx)
                        <tr>
                            <td class="small">{{ $tx->created_at->format('d M Y, h:i A') }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $tx->transaction_type }}</span></td>
                            <td class="fw-semibold {{ $tx->quantity > 0 ? 'text-success' : 'text-danger' }}">{{ $tx->quantity > 0 ? '+' : '' }}{{ $tx->quantity }}</td>
                            <td class="small">{{ $tx->createdBy?->name ?? 'System' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-4 text-muted">No stock movement transactions logged yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">Valuation</h6>
            </div>
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Purchase Rate:</span>
                    <span class="fw-semibold">₹{{ number_format($inventory->purchase_price, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Selling Rate:</span>
                    <span class="fw-bold text-success fs-5">₹{{ number_format($inventory->selling_price, 2) }}</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Total Stock Value:</span>
                    <span class="fw-bold">₹{{ number_format($inventory->current_stock * $inventory->purchase_price, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
