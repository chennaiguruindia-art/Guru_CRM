@extends('layouts.app')
@php($isEdit = $branch->exists)
@section('title', $isEdit ? 'Edit Branch' : 'Add Branch')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark">
            <i class="bi bi-building text-success me-2"></i>{{ $isEdit ? 'Edit Branch' : 'Add Branch' }}
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="{{ route('branches.index') }}" class="text-success text-decoration-none">Branches</a></li>
                <li class="breadcrumb-item active">{{ $isEdit ? 'Edit' : 'Add' }}</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('branches.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

<form method="POST" action="{{ $isEdit ? route('branches.update', $branch) : route('branches.store') }}">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-building text-success me-2"></i>Branch Details</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Branch Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $branch->name) }}" placeholder="e.g. Bangalore Central Nursery" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Branch Code</label>
                            @if($isEdit)
                                <input type="text" class="form-control" value="{{ $branch->code }}" disabled>
                                <div class="form-text">Codes are generated automatically and cannot be changed.</div>
                            @else
                                <input type="text" class="form-control" value="Generated on save" disabled>
                                <div class="form-text">A unique code is assigned when you save.</div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Location</label>
                            <input type="text" name="location" class="form-control @error('location') is-invalid @enderror"
                                   value="{{ old('location', $branch->location) }}" placeholder="e.g. HSR Layout, Bengaluru">
                            @error('location') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Phone</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone', $branch->phone) }}" placeholder="+91 …">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $branch->email) }}">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Status</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="active" {{ old('status', $branch->status ?: 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $branch->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Notes</label>
                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2"
                                      placeholder="Anything worth remembering about this location">{{ old('notes', $branch->notes) }}</textarea>
                            @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success py-2 px-4 fw-semibold">
                    <i class="bi bi-check2-circle me-1"></i> {{ $isEdit ? 'Save Changes' : 'Create Branch' }}
                </button>
                <a href="{{ route('branches.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle text-success me-2"></i>About Branches</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-2">
                        A branch is an organisational label. Assigning a person to a branch
                        records where they work and shows it on their profile.
                    </p>
                    <p class="small text-muted mb-0">
                        Branches do <strong>not</strong> restrict data — every user still sees
                        the same records their role allows.
                    </p>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
