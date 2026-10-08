@extends('layouts.app')
@section('title', 'Edit Plant — ' . $plant->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-pencil text-warning me-2"></i>Edit Plant — {{ $plant->name }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-0"><li class="breadcrumb-item"><a href="{{ route('plants.index') }}" class="text-success text-decoration-none">Plant Master</a></li><li class="breadcrumb-item active">Edit</li></ol></nav>
    </div>
    <a href="{{ route('plants.show', $plant) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<form method="POST" action="{{ route('plants.update', $plant) }}" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-flower1 text-success me-2"></i>Plant Details</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Plant Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $plant->name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Botanical Name</label>
                            <input type="text" name="botanical_name" class="form-control" value="{{ old('botanical_name', $plant->botanical_name) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Category</label>
                            <select name="category_id" class="form-select">
                                <option value="">— Select Category —</option>
                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}" @selected(old('category_id', $plant->category_id) == $c->id)>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Plant Type</label>
                            <select name="plant_type" class="form-select">
                                @foreach(['Indoor Plants', 'Outdoor Plants', 'Flowering Plants', 'Trees', 'Shrubs', 'Palms', 'Climbers', 'Lawn', 'Ground Covers', 'Fruit Plants', 'Medicinal Plants'] as $t)
                                    <option value="{{ $t }}" @selected(old('plant_type', $plant->plant_type) === $t)>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Purchase Price (₹)</label>
                            <input type="number" step="0.01" name="purchase_price" class="form-control" value="{{ old('purchase_price', $plant->purchase_price) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Selling Price (₹)</label>
                            <input type="number" step="0.01" name="selling_price" class="form-control" value="{{ old('selling_price', $plant->selling_price) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" @selected($plant->status === 'active')>Active</option>
                                <option value="inactive" @selected($plant->status === 'inactive')>Inactive</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Description</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description', $plant->description) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-warning py-2 px-4 fw-semibold text-dark">
                    <i class="bi bi-check2 me-1"></i> Update Plant
                </button>
                <a href="{{ route('plants.show', $plant) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
    </div>
</form>
@endsection
