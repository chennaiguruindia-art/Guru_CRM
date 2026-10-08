@extends('layouts.app')
@section('title','Edit Client — '.$customer->customer_code)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-pencil text-warning me-2"></i>Edit Client — {{ $customer->customer_code }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('customers.index') }}" class="text-success text-decoration-none">Clients</a></li>
            <li class="breadcrumb-item"><a href="{{ route('customers.show',$customer) }}" class="text-success text-decoration-none">{{ $customer->customer_code }}</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol></nav>
    </div>
    <a href="{{ route('customers.show',$customer) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<form method="POST" action="{{ route('customers.update',$customer) }}">
    @csrf @method('PUT')
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold"><i class="bi bi-person text-success me-2"></i>Client Information</h6></div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Client Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name',$customer->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Company Name</label>
                            <input type="text" name="company_name" class="form-control" value="{{ old('company_name',$customer->company_name) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Contact Person</label>
                            <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person',$customer->contact_person) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email',$customer->email) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Phone <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone',$customer->phone) }}" required>
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">WhatsApp</label>
                            <input type="text" name="whatsapp" class="form-control" value="{{ old('whatsapp',$customer->whatsapp) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">GST Number</label>
                            <input type="text" name="gst_number" class="form-control" value="{{ old('gst_number',$customer->gst_number) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">PAN Number</label>
                            <input type="text" name="pan_number" class="form-control" value="{{ old('pan_number',$customer->pan_number) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Address</label>
                            <textarea name="address" class="form-control" rows="2">{{ old('address',$customer->address) }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">City</label>
                            <input type="text" name="city" class="form-control" value="{{ old('city',$customer->city) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">State</label>
                            <input type="text" name="state" class="form-control" value="{{ old('state',$customer->state) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Pincode</label>
                            <input type="text" name="pincode" class="form-control" value="{{ old('pincode',$customer->pincode) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Notes</label>
                            <textarea name="notes" class="form-control" rows="3">{{ old('notes',$customer->notes) }}</textarea>
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
                        <label class="form-label small fw-semibold text-secondary">Client Type</label>
                        <select name="type" class="form-select">
                            @foreach(['Individual','Company','Apartment','Villa','School','Hospital','Hotel','Factory','Corporate','Government','Other'] as $t)
                                <option value="{{ $t }}" @selected(old('type',$customer->type)===$t)>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Status</label>
                        <select name="status" class="form-select">
                            @foreach(['Active','Inactive','Prospect','Blacklisted'] as $s)
                                <option value="{{ $s }}" @selected(old('status',$customer->status)===$s)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-semibold text-secondary">Assigned Employee</label>
                        <select name="assigned_to_id" class="form-select">
                            <option value="">— Unassigned —</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" @selected(old('assigned_to_id',$customer->assigned_to_id)==$user->id)>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-warning py-2 fw-semibold text-dark"><i class="bi bi-check2 me-1"></i>Update Client</button>
                <a href="{{ route('customers.show',$customer) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
    </div>
</form>
@endsection
