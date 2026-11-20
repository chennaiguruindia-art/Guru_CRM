@extends('layouts.app')
@php
    $channelDetails = \App\Models\Lead::CHANNEL_LISTS[$channel]
        ?? \App\Models\Lead::CHANNEL_LISTS['cold_call'];
    $channelLabel = $channelDetails[0];
    $channelRoute = $channelDetails[1];

    $pageHeading = match($channel) {
        'existing_client' => 'Operation Team Visit',
        'promotion_email' => 'Promotion Email / Call',
        default => 'Add New Visit',
    };

    $clientTypeDefault = $channel === 'existing_client' ? 'Existing Client' : 'New Client';
@endphp
@section('title', $pageHeading)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark">
            <i class="bi {{ $channel === 'existing_client' ? 'bi-person-workspace text-primary' : ($channel === 'promotion_email' ? 'bi-megaphone text-primary' : 'bi-funnel-fill text-success') }} me-2"></i>
            {{ $pageHeading }}
        </h4>
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
            {{-- Contact / Visit Details Card --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">
                        @if($channel === 'existing_client')
                            <i class="bi bi-person-workspace text-primary me-2"></i>Operation Team Visit Details
                        @elseif($channel === 'promotion_email')
                            <i class="bi bi-megaphone text-primary me-2"></i>Promotion Email / Call Details
                        @else
                            <i class="bi bi-person text-success me-2"></i>Visit Contact Details
                        @endif
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        @if($channel === 'existing_client')
                            {{-- 1. Employee Name (Operation Team) --}}
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-secondary">Employee Name (Operation Team) <span class="text-danger">*</span></label>
                                <input type="text" name="employee_name" list="employee-list" class="form-control @error('employee_name') is-invalid @enderror"
                                       value="{{ old('employee_name', auth()->user()?->name) }}"
                                       placeholder="Employee name who visited" required>
                                <datalist id="employee-list">
                                    @foreach($users as $user)
                                        <option value="{{ $user->name }}">{{ $user->email }}</option>
                                    @endforeach
                                </datalist>
                                @error('employee_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- 2. Purpose of Visit --}}
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-secondary">Purpose of Visit <span class="text-danger">*</span></label>
                                <select name="purpose_of_visit" class="form-select @error('purpose_of_visit') is-invalid @enderror" required>
                                    <option value="">-- Select Purpose --</option>
                                    @foreach(['Routine Site Inspection','Client Service Review','Site Measurement & Audit','Work Progress Check','Plant & Material Replacement','Complaint & Issue Resolution','Contract Renewal Discussion','General Visit','Client Meeting','Man Power Search'] as $p)
                                        <option value="{{ $p }}" @selected(old('purpose_of_visit', 'Routine Site Inspection')===$p)>{{ $p }}</option>
                                    @endforeach
                                </select>
                                @error('purpose_of_visit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- 3. Site Name --}}
                            <div class="col-12">
                                <label class="form-label small fw-semibold text-secondary">Site Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}" placeholder="Site name (e.g. Taj Grand, Adyar Villa)" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- 4. Client Site Contact Person Details: Name, Designation, Phone --}}
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-secondary">Contact Person at Site</label>
                                <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person') }}" placeholder="Client / Site in-charge name">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-secondary">Contact Person Designation</label>
                                <input type="text" name="designation" class="form-control @error('designation') is-invalid @enderror"
                                       value="{{ old('designation') }}" placeholder="e.g. Facility Manager / Secretary / Site In-charge">
                                @error('designation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-secondary">Contact Phone Number</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="+91 98765 43210">
                            </div>

                        @elseif($channel === 'promotion_email')
                            {{-- Promotion Email Layout --}}
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-secondary">Mode of Approach <span class="text-danger">*</span></label>
                                <select name="mode_of_approach" class="form-select @error('mode_of_approach') is-invalid @enderror" required>
                                    <option value="Call" @selected(old('mode_of_approach')==='Call')>Call</option>
                                    <option value="Email" @selected(old('mode_of_approach','Email')==='Email')>Email</option>
                                    <option value="Call & Email" @selected(old('mode_of_approach')==='Call & Email')>Call & Email</option>
                                    <option value="WhatsApp" @selected(old('mode_of_approach')==='WhatsApp')>WhatsApp</option>
                                </select>
                                @error('mode_of_approach')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-secondary">Company Name <span class="text-danger">*</span></label>
                                <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror" value="{{ old('company_name') }}" placeholder="Company name" required>
                                @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-secondary">Contact Person <span class="text-danger">*</span></label>
                                <input type="text" name="contact_person" class="form-control @error('contact_person') is-invalid @enderror" value="{{ old('contact_person') }}" placeholder="Contact person name" required>
                                @error('contact_person')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-secondary">Contact Person Designation</label>
                                <input type="text" name="designation" class="form-control @error('designation') is-invalid @enderror" value="{{ old('designation') }}" placeholder="e.g. Managing Director, Manager, Owner">
                                @error('designation')<div class="invalid-feedback">{{ $message }}</div>@enderror
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

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-secondary">Employee Name (Handled By)</label>
                                <input type="text" name="employee_name" list="employee-list" class="form-control @error('employee_name') is-invalid @enderror"
                                       value="{{ old('employee_name', auth()->user()?->name) }}"
                                       placeholder="Employee name who approached">
                                <datalist id="employee-list">
                                    @foreach($users as $user)
                                        <option value="{{ $user->name }}">{{ $user->email }}</option>
                                    @endforeach
                                </datalist>
                                @error('employee_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        @else
                            {{-- Standard Cold Call / General Visit --}}
                            <div class="col-12">
                                <label class="form-label small fw-semibold text-secondary">Employee Name (Spoke with Lead / Handled By)</label>
                                <input type="text" name="employee_name" list="employee-list" class="form-control @error('employee_name') is-invalid @enderror"
                                       value="{{ old('employee_name', auth()->user()?->name) }}"
                                       placeholder="Type employee name who spoke with lead">
                                <datalist id="employee-list">
                                    @foreach($users as $user)
                                        <option value="{{ $user->name }}">{{ $user->email }}</option>
                                    @endforeach
                                </datalist>
                                <div class="form-text">Type employee name directly, or select from suggestions.</div>
                                @error('employee_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-secondary">Site Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Site or client name" required>
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
                                <label class="form-label small fw-semibold text-secondary">Contact Person Designation</label>
                                <input type="text" name="designation" class="form-control" value="{{ old('designation') }}" placeholder="e.g. Manager, Owner">
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
                        @endif
                    </div>
                </div>
            </div>

            {{-- Location & Area --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-geo-alt text-info me-2"></i>Location & Area</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Area / Locality</label>
                            <input type="text" name="area" class="form-control" value="{{ old('area') }}" placeholder="e.g. Guindy, Adyar, OMR, Whitefield">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">City</label>
                            <input type="text" name="city" class="form-control" value="{{ old('city', 'Chennai') }}" placeholder="City">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Site Address</label>
                            <textarea name="address" class="form-control" rows="2" placeholder="Full address or landmark">{{ old('address') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">State</label>
                            <input type="text" name="state" class="form-control" value="{{ old('state','Tamil Nadu') }}" placeholder="State">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Pincode</label>
                            <input type="text" name="pincode" class="form-control" value="{{ old('pincode') }}" placeholder="600001">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Visiting Card --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-card-image text-primary me-2"></i>Visiting Card / Site Photo <span class="text-muted fw-normal small">(optional)</span></h6>
                </div>
                <div class="card-body p-4">
                    <label for="visiting_card_photo" class="form-label small fw-semibold text-secondary">Photo</label>
                    <input type="file" name="visiting_card_photo" id="visiting_card_photo"
                           class="form-control @error('visiting_card_photo') is-invalid @enderror"
                           accept="image/jpeg,image/png,image/webp"
                           onchange="previewCard(this, 'card-preview-create')">
                    @error('visiting_card_photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text text-muted mb-2">JPG, PNG or WebP up to 5 MB.</div>
                    <img id="card-preview-create" src="" alt="" class="d-none rounded border mt-2" style="max-height:140px">
                </div>
            </div>

            {{-- Remarks --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-chat-text text-secondary me-2"></i>Visit Remarks & Observations</h6>
                </div>
                <div class="card-body p-4">
                    <textarea name="remarks" class="form-control" rows="4" placeholder="Inspection observations, work carried out, client feedback, special notes…">{{ old('remarks') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div class="col-12 col-lg-4">
            @if($channel === 'promotion_email' || $channel === 'existing_client')
            <div class="card border-0 shadow-sm mb-4 border-top border-3 border-success">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold text-success"><i class="bi bi-calendar-check me-2"></i>Follow-up Date</h6>
                </div>
                <div class="card-body p-4">
                    <input type="hidden" name="source" value="{{ $source }}">
                    <input type="hidden" name="client_type" value="{{ $clientTypeDefault }}">
                    <input type="hidden" name="status" value="{{ $channel === 'existing_client' ? 'Site Visit Required' : 'Contacted' }}">

                    <div class="mb-0">
                        <label for="follow_up_date" class="form-label small fw-semibold text-secondary">Follow-up Date <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="date" name="follow_up_date" id="follow_up_date"
                               class="form-control @error('follow_up_date') is-invalid @enderror"
                               value="{{ old('follow_up_date') }}">
                        <div class="form-text">Optional. Pick a date if a follow-up visit is required.</div>
                        @error('follow_up_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            @else
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-sliders text-warning me-2"></i>Visit Classification</h6>
                </div>
                <div class="card-body p-4">
                    <input type="hidden" name="source" value="{{ $source }}">
                    <input type="hidden" name="client_type" value="{{ $clientTypeDefault }}">

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
                    <div class="mb-0">
                        <label for="follow_up_date" class="form-label small fw-semibold text-secondary">Follow-up Date <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="date" name="follow_up_date" id="follow_up_date"
                               class="form-control @error('follow_up_date') is-invalid @enderror"
                               value="{{ old('follow_up_date') }}">
                        <div class="form-text">Optional. If the customer agreed on a follow-up date, pick it here. On that date, an alert reminder will notify you automatically.</div>
                        @error('follow_up_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            @endif

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-success py-2 fw-semibold"><i class="bi bi-check2-circle me-1"></i>Save Visit</button>
                <a href="{{ route($channelRoute) }}" class="btn btn-outline-secondary">Cancel</a>
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
            const reader = new FileReader();
            reader.onload = e => {
                preview.src = e.target.result;
                preview.classList.remove('d-none');
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.classList.add('d-none');
            preview.src = '';
        }
    }
</script>
@endpush
