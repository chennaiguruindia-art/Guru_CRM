@extends('layouts.app')
@section('title', 'Plant — ' . $plant->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-tree-fill text-success me-2"></i>{{ $plant->name }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-0"><li class="breadcrumb-item"><a href="{{ route('plants.index') }}" class="text-success text-decoration-none">Plant Master</a></li><li class="breadcrumb-item active">{{ $plant->plant_code }}</li></ol></nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('plants.edit', $plant) }}" class="btn btn-primary btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
        <a href="{{ route('plants.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle text-success me-2"></i>Botanical & Horticultural Specifications</h6>
                <span class="badge {{ $plant->status === 'active' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($plant->status) }}</span>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-sm-6"><label class="text-muted small d-block">Plant Code</label><span class="fw-semibold text-success">{{ $plant->plant_code }}</span></div>
                    <div class="col-sm-6"><label class="text-muted small d-block">Botanical Name</label><span class="fst-italic">{{ $plant->botanical_name ?: '—' }}</span></div>
                    <div class="col-sm-6"><label class="text-muted small d-block">Common Name</label><span>{{ $plant->common_name ?: '—' }}</span></div>
                    <div class="col-sm-6"><label class="text-muted small d-block">Category</label><span>{{ $plant->category?->name ?? '—' }}</span></div>
                    <div class="col-sm-6"><label class="text-muted small d-block">Plant Type</label><span class="badge bg-light text-dark border">{{ $plant->plant_type }}</span></div>
                    <div class="col-sm-6"><label class="text-muted small d-block">Pot / Bag Size</label><span>{{ $plant->pot_size ?: '—' }}</span></div>
                    <div class="col-sm-6"><label class="text-muted small d-block">Sunlight</label><span><i class="bi bi-sun text-warning me-1"></i>{{ $plant->sunlight_requirement ?: '—' }}</span></div>
                    <div class="col-sm-6"><label class="text-muted small d-block">Watering</label><span><i class="bi bi-droplet-half text-info me-1"></i>{{ $plant->water_requirement ?: '—' }}</span></div>
                    @if($plant->description)
                    <div class="col-12"><label class="text-muted small d-block">Description</label><p class="bg-light p-3 rounded mb-0 small">{{ $plant->description }}</p></div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        @if($plant->image_path)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-2 text-center">
                <img src="{{ asset('storage/' . $plant->image_path) }}" class="img-fluid rounded" style="max-height: 250px; object-fit: cover;" alt="{{ $plant->name }}">
            </div>
        </div>
        @endif
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">Pricing</h6>
            </div>
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Purchase Rate:</span>
                    <span class="fw-semibold">₹{{ number_format($plant->purchase_price, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Selling Rate:</span>
                    <span class="fw-bold text-success fs-5">₹{{ number_format($plant->selling_price, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Reorder Threshold:</span>
                    <span>{{ $plant->reorder_level }} {{ $plant->unit }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
