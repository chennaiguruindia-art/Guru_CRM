@extends('layouts.app')
@section('title', 'Add Plant')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-tree-fill text-success me-2"></i>Add New Plant</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-0"><li class="breadcrumb-item"><a href="{{ route('plants.index') }}" class="text-success text-decoration-none">Plant Master</a></li><li class="breadcrumb-item active">Add Plant</li></ol></nav>
    </div>
    <a href="{{ route('plants.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<form method="POST" action="{{ route('plants.store') }}" enctype="multipart/form-data">
    @csrf
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
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Areca Palm" value="{{ old('name') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Botanical / Scientific Name</label>
                            <input type="text" name="botanical_name" class="form-control" placeholder="e.g. Dypsis lutescens" value="{{ old('botanical_name') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Common Name</label>
                            <input type="text" name="common_name" class="form-control" placeholder="e.g. Golden Cane Palm" value="{{ old('common_name') }}">
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
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Plant Type</label>
                            <select name="plant_type" class="form-select">
                                @foreach(['Indoor Plants', 'Outdoor Plants', 'Flowering Plants', 'Trees', 'Shrubs', 'Palms', 'Climbers', 'Lawn', 'Ground Covers', 'Fruit Plants', 'Medicinal Plants'] as $t)
                                    <option value="{{ $t }}">{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Sunlight Requirement</label>
                            <select name="sunlight_requirement" class="form-select">
                                @foreach(['Full Sun', 'Partial Shade', 'Full Shade', 'Low Light'] as $sl)
                                    <option value="{{ $sl }}">{{ $sl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Water Requirement</label>
                            <select name="water_requirement" class="form-select">
                                @foreach(['Daily', 'Moderate', 'Low', 'Weekly'] as $wr)
                                    <option value="{{ $wr }}">{{ $wr }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Pot / Bag Size</label>
                            <input type="text" name="pot_size" class="form-control" placeholder="e.g. 10 inch, 12 inch" value="{{ old('pot_size') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Height</label>
                            <input type="text" name="height" class="form-control" placeholder="e.g. 3-4 feet" value="{{ old('height') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Unit</label>
                            <select name="unit" class="form-select">
                                @foreach(['Nos', 'Pot', 'Bag', 'Sq.Ft'] as $u)
                                    <option value="{{ $u }}">{{ $u }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Care instructions, features, landscaping uses…">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">Pricing & Inventory</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Purchase Price (₹)</label>
                        <input type="number" step="0.01" name="purchase_price" class="form-control" value="{{ old('purchase_price', 0) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Selling Price (₹)</label>
                        <input type="number" step="0.01" name="selling_price" class="form-control" value="{{ old('selling_price', 0) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Reorder Threshold</label>
                        <input type="number" name="reorder_level" class="form-control" value="{{ old('reorder_level', 10) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Status</label>
                        <select name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-secondary">Plant Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success py-2 fw-semibold">
                            <i class="bi bi-check2-circle me-1"></i> Save Plant
                        </button>
                        <a href="{{ route('plants.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
