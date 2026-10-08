@extends('layouts.app')
@section('title', 'Inventory & Nursery Stock')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-boxes text-success me-2"></i>Inventory Management</h4>
        <p class="text-muted small mb-0">Track nursery stock, pots, soil, fertilizers, tools, and irrigation items.</p>
    </div>
    <a href="{{ route('inventory.create') }}" class="btn btn-success shadow-sm">
        <i class="bi bi-plus-lg me-1"></i> Add Stock Item
    </a>
</div>

{{-- KPI cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="card border-0 shadow-sm p-3">
            <span class="text-muted small">Total Inventory Items</span>
            <h3 class="fw-bold mb-0 text-dark">{{ $totalItems }}</h3>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="card border-0 shadow-sm p-3">
            <span class="text-muted small">Low Stock Alert</span>
            <h3 class="fw-bold mb-0 {{ $lowStockCount > 0 ? 'text-danger' : 'text-success' }}">{{ $lowStockCount }}</h3>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm p-3">
            <span class="text-muted small">Warehouses / Nurseries</span>
            <h3 class="fw-bold mb-0 text-primary">{{ $warehouses->count() }}</h3>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-crm align-middle mb-0">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Current Stock</th>
                    <th>Min. Alert</th>
                    <th>Warehouse</th>
                    <th>Selling Price</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                <tr>
                    <td><a href="{{ route('inventory.show', $item) }}" class="fw-semibold text-success text-decoration-none">{{ $item->sku }}</a></td>
                    <td class="fw-semibold">{{ $item->name }}</td>
                    <td><span class="badge bg-light text-dark border">{{ $item->category?->name ?? 'General' }}</span></td>
                    <td>
                        <span class="fw-bold {{ $item->current_stock <= $item->minimum_stock ? 'text-danger' : 'text-dark' }}">
                            {{ $item->current_stock }} {{ $item->unit }}
                        </span>
                        @if($item->current_stock <= $item->minimum_stock)
                            <span class="badge bg-danger ms-1">Low</span>
                        @endif
                    </td>
                    <td class="small text-muted">{{ $item->minimum_stock }} {{ $item->unit }}</td>
                    <td class="small">{{ $item->warehouse?->name ?? 'Main Nursery' }}</td>
                    <td class="fw-semibold text-success">₹{{ number_format($item->selling_price, 2) }}</td>
                    <td>
                        <span class="badge {{ $item->status === 'active' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($item->status) }}</span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('inventory.show', $item) }}" class="btn btn-sm btn-outline-success"><i class="bi bi-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5">
                        <i class="bi bi-boxes fs-1 text-muted d-block mb-2"></i>
                        <span class="text-muted">No inventory items found. <a href="{{ route('inventory.create') }}">Add an item</a>.</span>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($items->hasPages())
    <div class="card-footer bg-white py-3">{{ $items->withQueryString()->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
