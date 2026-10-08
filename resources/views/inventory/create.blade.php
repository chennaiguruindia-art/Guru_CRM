@extends('layouts.app')
@section('title', 'Add Inventory Item')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-boxes text-success me-2"></i>Add Inventory Item</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-0"><li class="breadcrumb-item"><a href="{{ route('inventory.index') }}" class="text-success text-decoration-none">Inventory</a></li><li class="breadcrumb-item active">Add Item</li></ol></nav>
    </div>
    <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<form method="POST" action="{{ route('inventory.store') }}">
    @csrf
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle text-success me-2"></i>Item Details</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Item Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Terracotta Pot 12 Inch" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">SKU / Code (optional, auto-generated)</label>
                            <input type="text" name="sku" class="form-control" placeholder="Leave blank to auto-generate" value="{{ old('sku') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Category</label>
                            <select name="category_id" class="form-select">
                                <option value="">— Select Category —</option>
                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Linked Plant (if plant stock)</label>
                            <select name="plant_id" class="form-select">
                                <option value="">— Non-Plant / Material —</option>
                                @foreach($plants as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Unit <span class="text-danger">*</span></label>
                            <select name="unit" class="form-select" required>
                                @foreach(['Nos', 'Kg', 'Bags', 'Pcs', 'Bags (25kg)', 'Bags (50kg)', 'Litre', 'Meters', 'Rolls'] as $u)
                                    <option value="{{ $u }}">{{ $u }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Opening Stock <span class="text-danger">*</span></label>
                            <input type="number" name="opening_stock" class="form-control" value="{{ old('opening_stock', 0) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Min. Alert Threshold <span class="text-danger">*</span></label>
                            <input type="number" name="minimum_stock" class="form-control" value="{{ old('minimum_stock', 5) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Warehouse / Nursery</label>
                            <select name="warehouse_id" class="form-select">
                                <option value="">— Select Location —</option>
                                @foreach($warehouses as $w)
                                    <option value="{{ $w->id }}">{{ $w->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Rack / Bay Location</label>
                            <input type="text" name="rack" class="form-control" placeholder="e.g. Bay 4 - Shelf B" value="{{ old('rack') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">Pricing</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Purchase Price (₹)</label>
                        <input type="number" step="0.01" name="purchase_price" class="form-control" value="{{ old('purchase_price', 0) }}">
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-secondary">Selling Price (₹)</label>
                        <input type="number" step="0.01" name="selling_price" class="form-control" value="{{ old('selling_price', 0) }}">
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success py-2 fw-semibold">
                            <i class="bi bi-check2-circle me-1"></i> Save Item
                        </button>
                        <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
