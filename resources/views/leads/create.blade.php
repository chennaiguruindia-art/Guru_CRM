@extends('layouts.app')
@section('title','Add New Visit')

@section('content')
@php
    // Where this Visit lands once saved — shown because the Source dropdown
    // is gone and the filer would otherwise have no cue.
    [$channelLabel, $channelRoute] = \App\Models\Lead::CHANNEL_LISTS[$channel]
        ?? \App\Models\Lead::CHANNEL_LISTS['cold_call'];

    // A Visit opened from the Existing Client list is already known to be one.
    $clientTypeDefault = $channel === 'existing_client' ? 'Existing Client' : 'New Client';
@endphp
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-funnel-fill text-success me-2"></i>Add New Visit</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route($channelRoute) }}" class="text-success text-decoration-none">{{ $channelLabel }}</a></li>
            <li class="breadcrumb-item active">Add New</li>
        </ol></nav>
    </div>
    <div class="d-flex gap-2 align-items-center flex-wrap">
        <span class="badge bg-soft-info text-info border"><i class="bi bi-inbox me-1"></i>Saved under {{ $channelLabel }}</span>
        <a href="{{ route($channelRoute) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

<form method="POST" action="{{ route('leads.store') }}" id="lead-form" enctype="multipart/form-data">
    @csrf
    <div class="row g-4">
        {{-- Left Column --}}
        <div class="col-12 col-lg-8">
            {{-- Contact Details --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-person text-success me-2"></i>Visit Contact Details</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Site Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Full name of contact person" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Company Name</label>
                            <input type="text" name="company_name" class="form-control" value="{{ old('company_name') }}" placeholder="Company or organisation name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Contact Person</label>
                            <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person') }}" placeholder="Decision maker name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="email@example.com">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Phone <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="+91 98765 43210" required>
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">WhatsApp Number</label>
                            <input type="text" name="whatsapp" class="form-control" value="{{ old('whatsapp') }}" placeholder="WhatsApp number (if different)">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Address --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-geo-alt text-info me-2"></i>Location & Address</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Address</label>
                            <textarea name="address" class="form-control" rows="2" placeholder="Full address">{{ old('address') }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">City</label>
                            <input type="text" name="city" class="form-control" value="{{ old('city') }}" placeholder="City">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">State</label>
                            <input type="text" name="state" class="form-control" value="{{ old('state','Karnataka') }}" placeholder="State">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Pincode</label>
                            <input type="text" name="pincode" class="form-control" value="{{ old('pincode') }}" placeholder="560001">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Visiting Card --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-card-image text-primary me-2"></i>Visiting Card <span class="text-muted fw-normal small">(optional)</span></h6>
                </div>
                <div class="card-body p-4">
                    <label for="visiting_card_photo" class="form-label small fw-semibold text-secondary">Card Photo</label>
                    <input type="file" name="visiting_card_photo" id="visiting_card_photo"
                           class="form-control @error('visiting_card_photo') is-invalid @enderror"
                           accept="image/jpeg,image/png,image/webp"
                           onchange="previewCard(this, 'card-preview-create')">
                    @error('visiting_card_photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text text-muted mb-2">JPG, PNG or WebP — up to 5 MB. Photograph the card handed over at the hotel, apartment or villa.</div>
                    <img id="card-preview-create" src="" alt="" class="d-none rounded border mt-2" style="max-height:140px">
                </div>
            </div>

            {{-- Remarks --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-chat-text text-secondary me-2"></i>Remarks & Requirements</h6>
                </div>
                <div class="card-body p-4">
                    <textarea name="remarks" class="form-control" rows="4" placeholder="Client requirements, site details, special notes…">{{ old('remarks') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-sliders text-warning me-2"></i>Visit Classification</h6>
                </div>
                <div class="card-body p-4">
                    {{--
                        There is no Source dropdown any more. The channel comes
                        from which Add button was pressed (Cold Call /
                        Promotion Email / Existing Client) and is posted back
                        untouched.
                    --}}
                    <input type="hidden" name="source" value="{{ $source }}">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">New or Existing Client</label>
                        <select name="client_type" class="form-select @error('client_type') is-invalid @enderror">
                            <option value="">-- Select --</option>
                            @foreach(\App\Models\Lead::CLIENT_TYPES as $type)
                                <option value="{{ $type }}" @selected(old('client_type',$clientTypeDefault)===$type)>{{ $type }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Is this Visit with somebody new, or a Client you already have?</div>
                        @error('client_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Purpose of Visit</label>
                        <select name="purpose_of_visit" class="form-select @error('purpose_of_visit') is-invalid @enderror">
                            <option value="">-- Select Purpose --</option>
                            @foreach(\App\Models\Lead::PURPOSES as $purpose)
                                <option value="{{ $purpose }}" @selected(old('purpose_of_visit')===$purpose)>{{ $purpose }}</option>
                            @endforeach
                        </select>
                        @error('purpose_of_visit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Type of Service</label>
                        <select name="service_type" class="form-select @error('service_type') is-invalid @enderror">
                            <option value="">-- Select Type --</option>
                            @foreach(\App\Models\Lead::SERVICE_TYPES as $type)
                                <option value="{{ $type }}" @selected(old('service_type')===$type)>{{ $type }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Decides what converting this Visit creates: an AMC Contract or a Project.</div>
                        @error('service_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Interested Service</label>
                        <select name="interested_service" class="form-select">
                            <option value="">-- Select Service --</option>
                            @foreach(['Landscaping','Garden Design','Garden Maintenance','Plant Supply','AMC Contract','Irrigation Installation','Terrace Garden','Indoor Plants','Nursery Purchase','Vertical Garden','Other'] as $svc)
                                <option value="{{ $svc }}" @selected(old('interested_service')===$svc)>{{ $svc }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Visit Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            @foreach(['New','Contacted','Qualified','Site Visit Required','Quotation Required'] as $s)
                                <option value="{{ $s }}" @selected(old('status','New')===$s)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Priority <span class="text-danger">*</span></label>
                        <select name="priority" class="form-select" required>
                            @foreach(['Low','Medium','High','Urgent'] as $p)
                                <option value="{{ $p }}" @selected(old('priority','Medium')===$p)>{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-calendar3 text-info me-2"></i>Expected Timeline</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Expected Value (₹)</label>
                        <input type="number" name="expected_value" class="form-control" value="{{ old('expected_value',0) }}" min="0" step="1000">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Expected Closing Date</label>
                        <input type="date" name="expected_closing_date" class="form-control" value="{{ old('expected_closing_date') }}">
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-semibold text-secondary">Follow-up Date</label>
                        <input type="date" name="follow_up_date" class="form-control" value="{{ old('follow_up_date', now()->format('Y-m-d')) }}">
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-success py-2 fw-semibold"><i class="bi bi-check2-circle me-1"></i>Save Visit</button>
                <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary">Cancel</a>
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
