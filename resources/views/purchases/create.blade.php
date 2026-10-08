@extends('layouts.app')
@section('title', 'Create Purchase Order')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-cart-check-fill text-success me-2"></i>New Purchase Order</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-0"><li class="breadcrumb-item"><a href="{{ route('purchases.index') }}" class="text-success text-decoration-none">Purchases</a></li><li class="breadcrumb-item active">Create</li></ol></nav>
    </div>
    <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<form method="POST" action="{{ route('purchases.store') }}">
    @csrf
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle text-success me-2"></i>Purchase Order Details</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Vendor / Supplier <span class="text-danger">*</span></label>
                            <select name="vendor_id" class="form-select @error('vendor_id') is-invalid @enderror" required>
                                <option value="">— Select Vendor —</option>
                                @foreach($vendors as $v)
                                    <option value="{{ $v->id }}">{{ $v->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-secondary">PO Date <span class="text-danger">*</span></label>
                            <input type="date" name="po_date" class="form-control" value="{{ old('po_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-secondary">Expected Delivery</label>
                            <input type="date" name="delivery_date" class="form-control" value="{{ old('delivery_date', date('Y-m-d', strtotime('+7 days'))) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Subtotal (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="subtotal" class="form-control" value="{{ old('subtotal', 0) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Tax Amount (₹)</label>
                            <input type="number" step="0.01" name="tax_amount" class="form-control" value="{{ old('tax_amount', 0) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Discount (₹)</label>
                            <input type="number" step="0.01" name="discount_amount" class="form-control" value="{{ old('discount_amount', 0) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Order Notes / Instructions</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Items ordered, quality requirements, nursery delivery location…"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success py-2 px-4 fw-semibold">
                    <i class="bi bi-check2-circle me-1"></i> Issue Purchase Order
                </button>
                <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
    </div>
</form>
@endsection
