@extends('layouts.app')
@section('title','Edit Visit - '.$lead->lead_code)

@section('content')
@php
    // Which list this Visit sits under — the same fallback the detail page
    // uses, so a Field Marketer is never handed a breadcrumb that would 403.
    $channelKey = array_search($lead->source, \App\Models\Lead::CHANNELS, true) ?: 'cold_call';
    if (! auth()->user()?->can(\App\Models\Lead::CHANNEL_LISTS[$channelKey][2])) {
        $channelKey = 'cold_call';
    }
    [$backLabel, $backRoute] = \App\Models\Lead::CHANNEL_LISTS[$channelKey];
@endphp
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-pencil text-warning me-2"></i>Edit Visit — {{ $lead->lead_code }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route($backRoute) }}" class="text-success text-decoration-none">{{ $backLabel }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('leads.show',$lead) }}" class="text-success text-decoration-none">{{ $lead->lead_code }}</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol></nav>
    </div>
    <a href="{{ route('leads.show',$lead) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<form method="POST" action="{{ route('leads.update',$lead) }}" id="lead-form" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold"><i class="bi bi-person text-success me-2"></i>Contact Details</h6></div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Visit Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name',$lead->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Company Name</label>
                            <input type="text" name="company_name" class="form-control" value="{{ old('company_name',$lead->company_name) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Contact Person</label>
                            <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person',$lead->contact_person) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email',$lead->email) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Phone <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone',$lead->phone) }}" required>
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">WhatsApp</label>
                            <input type="text" name="whatsapp" class="form-control" value="{{ old('whatsapp',$lead->whatsapp) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Address</label>
                            <textarea name="address" class="form-control" rows="2">{{ old('address',$lead->address) }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">City</label>
                            <input type="text" name="city" class="form-control" value="{{ old('city',$lead->city) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">State</label>
                            <input type="text" name="state" class="form-control" value="{{ old('state',$lead->state) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Pincode</label>
                            <input type="text" name="pincode" class="form-control" value="{{ old('pincode',$lead->pincode) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="3">{{ old('remarks',$lead->remarks) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Visiting Card Photo</label>
                            @if($lead->visiting_card_photo)
                                <div class="mb-2">
                                    <a href="{{ asset('storage/' . $lead->visiting_card_photo) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $lead->visiting_card_photo) }}" alt="Visiting card"
                                             class="rounded border" style="max-height:120px">
                                    </a>
                                    <div class="form-text">Upload a new photo to replace the current card.</div>
                                </div>
                            @endif
                            <input type="file" name="visiting_card_photo" id="visiting_card_photo"
                                   class="form-control @error('visiting_card_photo') is-invalid @enderror"
                                   accept="image/jpeg,image/png,image/webp"
                                   onchange="previewCard(this, 'card-preview-edit')">
                            @error('visiting_card_photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <img id="card-preview-edit" src="" alt="" class="d-none rounded border mt-2" style="max-height:140px">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold"><i class="bi bi-sliders text-warning me-2"></i>Classification & Assignment</h6></div>
                <div class="card-body p-4">
                    {{--
                        Source is stamped when the Visit is created and is no
                        longer editable — rendered as plain text so it is not
                        submitted and silently rewritten on save.
                    --}}
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Source</label>
                        <div class="form-control bg-light text-muted">{{ $lead->source ?: '—' }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Purpose of Visit</label>
                        <select name="purpose_of_visit" class="form-select @error('purpose_of_visit') is-invalid @enderror">
                            <option value="">-- Select Purpose --</option>
                            @foreach(\App\Models\Lead::PURPOSES as $purpose)
                                <option value="{{ $purpose }}" @selected(old('purpose_of_visit',$lead->purpose_of_visit)===$purpose)>{{ $purpose }}</option>
                            @endforeach
                        </select>
                        @error('purpose_of_visit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Type of Service</label>
                        <select name="service_type" class="form-select @error('service_type') is-invalid @enderror">
                            <option value="">-- Select Type --</option>
                            @foreach(\App\Models\Lead::SERVICE_TYPES as $type)
                                <option value="{{ $type }}" @selected(old('service_type',$lead->service_type)===$type)>{{ $type }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Decides what converting this Visit creates: an AMC Contract or a Project.</div>
                        @error('service_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Interested Service</label>
                        <select name="interested_service" class="form-select">
                            <option value="">-- Select --</option>
                            @foreach(['Landscaping','Garden Design','Garden Maintenance','Plant Supply','AMC Contract','Irrigation Installation','Terrace Garden','Indoor Plants','Nursery Purchase','Vertical Garden','Other'] as $svc)
                                <option value="{{ $svc }}" @selected(old('interested_service',$lead->interested_service)===$svc)>{{ $svc }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            @foreach(['New','Contacted','Qualified','Site Visit Required','Quotation Required','Converted','Lost'] as $s)
                                <option value="{{ $s }}" @selected(old('status',$lead->status)===$s)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Priority <span class="text-danger">*</span></label>
                        <select name="priority" class="form-select" required>
                            @foreach(['Low','Medium','High','Urgent'] as $p)
                                <option value="{{ $p }}" @selected(old('priority',$lead->priority)===$p)>{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Assigned To</label>
                        <select name="assigned_to_id" class="form-select">
                            <option value="">-- Unassigned --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" @selected(old('assigned_to_id',$lead->assigned_to_id)==$user->id)>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Expected Value (₹)</label>
                        <input type="number" name="expected_value" class="form-control" value="{{ old('expected_value',$lead->expected_value) }}" min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Expected Closing Date</label>
                        <input type="date" name="expected_closing_date" class="form-control" value="{{ old('expected_closing_date', $lead->expected_closing_date?->format('Y-m-d')) }}">
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-semibold text-secondary">Follow-up Date</label>
                        <input type="date" name="follow_up_date" class="form-control" value="{{ old('follow_up_date', $lead->follow_up_date?->format('Y-m-d')) }}">
                    </div>
                </div>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-warning py-2 fw-semibold text-dark"><i class="bi bi-check2 me-1"></i>Update Visit</button>
                <a href="{{ route('leads.show',$lead) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    function previewCard(input, previewId) {
        const preview = document.getElementById(previewId);
        if (!preview) return;
        if (input.files && input.files[0]) {
            preview.src = URL.createObjectURL(input.files[0]);
            preview.classList.remove('d-none');
        } else {
            preview.classList.add('d-none');
            preview.src = '';
        }
    }
</script>
@endpush
