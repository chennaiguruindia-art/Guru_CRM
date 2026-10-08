@extends('layouts.app')
@section('title','Add Client')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-people-fill text-success me-2"></i>Add Client</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-0"><li class="breadcrumb-item"><a href="{{ route('customers.index') }}" class="text-success text-decoration-none">Clients</a></li><li class="breadcrumb-item active">Add New</li></ol></nav>
    </div>
    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<form method="POST" action="{{ route('customers.store') }}">
    @csrf
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            {{-- Contact --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold"><i class="bi bi-person text-success me-2"></i>Client Information</h6></div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Client Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Company Name</label>
                            <input type="text" name="company_name" class="form-control" value="{{ old('company_name') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Contact Person</label>
                            <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Phone <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" required>
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">WhatsApp</label>
                            <input type="text" name="whatsapp" class="form-control" value="{{ old('whatsapp') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">GST Number</label>
                            <input type="text" name="gst_number" class="form-control" value="{{ old('gst_number') }}" placeholder="27AAAAA0000A1Z5">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">PAN Number</label>
                            <input type="text" name="pan_number" class="form-control" value="{{ old('pan_number') }}" placeholder="AAAAA0000A">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Address --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold"><i class="bi bi-geo-alt text-info me-2"></i>Address</h6></div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Address</label>
                            <textarea name="address" class="form-control" rows="2">{{ old('address') }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">City</label>
                            <input type="text" name="city" class="form-control" value="{{ old('city') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">State</label>
                            <input type="text" name="state" class="form-control" value="{{ old('state','Karnataka') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Pincode</label>
                            <input type="text" name="pincode" class="form-control" value="{{ old('pincode') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Internal notes about this client…">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold"><i class="bi bi-sliders text-warning me-2"></i>Classification</h6></div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Client Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            @foreach(['Individual','Company','Apartment','Villa','School','Hospital','Hotel','Factory','Corporate','Government','Other'] as $t)
                                <option value="{{ $t }}" @selected(old('type')===$t)>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Status</label>
                        <select name="status" class="form-select">
                            @foreach(['Active','Inactive','Prospect'] as $s)
                                <option value="{{ $s }}" @selected(old('status','Active')===$s)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-semibold text-secondary">Assigned Employee</label>
                        <select name="assigned_to_id" class="form-select">
                            <option value="">— Unassigned —</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" @selected(old('assigned_to_id')==$user->id)>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-success py-2 fw-semibold"><i class="bi bi-check2-circle me-1"></i>Save Client</button>
                <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
    </div>
</form>
@endsection
