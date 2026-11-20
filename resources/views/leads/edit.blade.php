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
        <h4 class="fw-bold mb-0 text-dark">
            <i class="bi {{ $channelKey === 'existing_client' ? 'bi-person-workspace text-primary' : ($channelKey === 'promotion_email' ? 'bi-megaphone text-primary' : 'bi-pencil text-warning') }} me-2"></i>
            {{ $channelKey === 'existing_client' ? 'Edit Operation Team Visit' : ($channelKey === 'promotion_email' ? 'Edit Promotion Email / Call' : 'Edit Visit') }} — {{ $lead->lead_code }}
        </h4>
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
        {{-- Left: Main Details --}}
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">
                        @if($channelKey === 'existing_client')
                            <i class="bi bi-person-workspace text-primary me-2"></i>Operation Team Visit Details
                        @elseif($channelKey === 'promotion_email')
                            <i class="bi bi-megaphone text-primary me-2"></i>Promotion Email / Call Details
                        @else
                            <i class="bi bi-person text-success me-2"></i>Visit Contact Details
                        @endif
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        @if($channelKey === 'existing_client')
                            @php
                                $opPurposes = [
                                    'Routine Site Inspection',
                                    'Client Service Review',
                                    'Site Measurement & Audit',
                                    'Work Progress Check',
                                    'Plant & Material Replacement',
                                    'Complaint & Issue Resolution',
                                    'Contract Renewal Discussion',
                                    'General Visit',
                                    'Client Meeting',
                                    'Man Power Search',
                                ];
                                if ($lead->purpose_of_visit && !in_array($lead->purpose_of_visit, $opPurposes, true)) {
                                    $opPurposes[] = $lead->purpose_of_visit;
                                }
                            @endphp
                            {{-- 1. Employee Name (Operation Team) --}}
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-secondary">Employee Name (Operation Team) <span class="text-danger">*</span></label>
                                <input type="text" name="employee_name" list="employee-list-edit" class="form-control @error('employee_name') is-invalid @enderror"
                                       value="{{ old('employee_name', $lead->employee_name ?: $lead->assignedTo?->name) }}"
                                       placeholder="Employee name who visited" required>
                                <datalist id="employee-list-edit">
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
                                    @foreach($opPurposes as $p)
                                        <option value="{{ $p }}" @selected(old('purpose_of_visit', $lead->purpose_of_visit)===$p)>{{ $p }}</option>
                                    @endforeach
                                </select>
                                @error('purpose_of_visit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- 3. Site Name --}}
                            <div class="col-12">
                                <label class="form-label small fw-semibold text-secondary">Site Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $lead->name) }}" placeholder="Site name (e.g. Taj Grand, Adyar Villa)" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- 4. Client Site Contact Person Details: Name, Designation, Phone --}}
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-secondary">Contact Person at Site</label>
                                <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person', $lead->contact_person) }}" placeholder="Client / Site in-charge name">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-secondary">Contact Person Designation</label>
                                <input type="text" name="designation" class="form-control @error('designation') is-invalid @enderror"
                                       value="{{ old('designation', $lead->designation) }}" placeholder="e.g. Facility Manager / Secretary / Site In-charge">
                                @error('designation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-secondary">Contact Phone Number</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $lead->phone) }}" placeholder="+91 98765 43210">
                            </div>

                        @elseif($channelKey === 'promotion_email')
                            {{-- Promotion Email Layout --}}
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-secondary">Mode of Approach <span class="text-danger">*</span></label>
                                <select name="mode_of_approach" class="form-select @error('mode_of_approach') is-invalid @enderror" required>
                                    <option value="Call" @selected(old('mode_of_approach', $lead->mode_of_approach)==='Call')>Call</option>
                                    <option value="Email" @selected(old('mode_of_approach', $lead->mode_of_approach ?? 'Email')==='Email')>Email</option>
                                    <option value="Call & Email" @selected(old('mode_of_approach', $lead->mode_of_approach)==='Call & Email')>Call & Email</option>
                                    <option value="WhatsApp" @selected(old('mode_of_approach', $lead->mode_of_approach)==='WhatsApp')>WhatsApp</option>
                                </select>
                                @error('mode_of_approach')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-secondary">Company Name</label>
                                <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $lead->company_name) }}" placeholder="Company or organisation name">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-secondary">Contact Person</label>
                                <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person', $lead->contact_person) }}" placeholder="Decision maker name">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-secondary">Contact Person Designation</label>
                                <input type="text" name="designation" class="form-control @error('designation') is-invalid @enderror" value="{{ old('designation', $lead->designation) }}" placeholder="e.g. Managing Director, Manager">
                                @error('designation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-secondary">Email</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $lead->email) }}" placeholder="email@example.com">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-secondary">Phone <span class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $lead->phone) }}" placeholder="+91 98765 43210" required>
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-secondary">WhatsApp Number</label>
                                <input type="text" name="whatsapp" class="form-control" value="{{ old('whatsapp', $lead->whatsapp) }}" placeholder="WhatsApp number (if different)">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-secondary">Employee Name (Handled By)</label>
                                <input type="text" name="employee_name" list="employee-list-edit" class="form-control @error('employee_name') is-invalid @enderror"
                                       value="{{ old('employee_name', $lead->employee_name ?: $lead->assignedTo?->name) }}"
                                       placeholder="Employee name who approached">
                                <datalist id="employee-list-edit">
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
                                <input type="text" name="employee_name" list="employee-list-edit" class="form-control @error('employee_name') is-invalid @enderror"
                                       value="{{ old('employee_name', $lead->employee_name ?: $lead->assignedTo?->name) }}"
                                       placeholder="Type employee name who spoke with lead">
                                <datalist id="employee-list-edit">
                                    @foreach($users as $user)
                                        <option value="{{ $user->name }}">{{ $user->email }}</option>
                                    @endforeach
                                </datalist>
                                <div class="form-text">Type employee name directly, or select from suggestions.</div>
                                @error('employee_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-secondary">Site Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name',$lead->name) }}" placeholder="Site or client name" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-secondary">Company Name</label>
                                <input type="text" name="company_name" class="form-control" value="{{ old('company_name',$lead->company_name) }}" placeholder="Company or organisation name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-secondary">Contact Person</label>
                                <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person',$lead->contact_person) }}" placeholder="Decision maker name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-secondary">Contact Person Designation</label>
                                <input type="text" name="designation" class="form-control" value="{{ old('designation', $lead->designation) }}" placeholder="e.g. Managing Director, Manager">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-secondary">Email</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email',$lead->email) }}" placeholder="email@example.com">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-secondary">Phone <span class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone',$lead->phone) }}" placeholder="+91 98765 43210" required>
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-secondary">WhatsApp Number</label>
                                <input type="text" name="whatsapp" class="form-control" value="{{ old('whatsapp',$lead->whatsapp) }}" placeholder="WhatsApp number (if different)">
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
                            <input type="text" name="area" class="form-control" value="{{ old('area', $lead->area) }}" placeholder="e.g. Guindy, Adyar, OMR, Whitefield">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">City</label>
                            <input type="text" name="city" class="form-control" value="{{ old('city', $lead->city ?? 'Chennai') }}" placeholder="City">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Site Address</label>
                            <textarea name="address" class="form-control" rows="2" placeholder="Full address or landmark">{{ old('address', $lead->address) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">State</label>
                            <input type="text" name="state" class="form-control" value="{{ old('state', $lead->state ?? 'Tamil Nadu') }}" placeholder="State">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Pincode</label>
                            <input type="text" name="pincode" class="form-control" value="{{ old('pincode', $lead->pincode) }}" placeholder="600001">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Visiting Card / Site Photo --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-card-image text-primary me-2"></i>Visiting Card / Site Photo <span class="text-muted fw-normal small">(optional)</span></h6>
                </div>
                <div class="card-body p-4">
                    @if($lead->visiting_card_photo)
                        <div class="mb-2">
                            <a href="{{ asset('storage/' . $lead->visiting_card_photo) }}" target="_blank">
                                <img src="{{ asset('storage/' . $lead->visiting_card_photo) }}" alt="Visiting card"
                                     class="rounded border" style="max-height:120px">
                            </a>
                            <div class="form-text">Upload a new photo to replace the current card/photo.</div>
                        </div>
                    @endif
                    <input type="file" name="visiting_card_photo" id="visiting_card_photo"
                           class="form-control @error('visiting_card_photo') is-invalid @enderror"
                           accept="image/jpeg,image/png,image/webp"
                           onchange="previewCard(this, 'card-preview-edit')">
                    @error('visiting_card_photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text text-muted mb-2">JPG, PNG or WebP up to 5 MB.</div>
                    <img id="card-preview-edit" src="" alt="" class="d-none rounded border mt-2" style="max-height:140px">
                </div>
            </div>

            {{-- Remarks --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-chat-text text-secondary me-2"></i>Visit Remarks & Observations</h6>
                </div>
                <div class="card-body p-4">
                    <textarea name="remarks" class="form-control" rows="4" placeholder="Inspection observations, work carried out, client feedback, special notes…">{{ old('remarks', $lead->remarks) }}</textarea>
                </div>
            </div>

            {{-- Client Documents & Old Records --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold text-primary"><i class="bi bi-folder2-open me-2"></i>Client Documents & Old Records</h6>
                    <span class="badge bg-primary-subtle text-primary">Multi-file upload</span>
                </div>
                <div class="card-body p-4">
                    @if($lead->documents && $lead->documents->count())
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Existing Uploaded Files ({{ $lead->documents->count() }})</label>
                            <div class="list-group list-group-flush border rounded small">
                                @foreach($lead->documents as $doc)
                                    <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                                        <div class="d-flex align-items-center gap-2 text-truncate">
                                            <i class="bi bi-file-earmark-check text-success fs-5"></i>
                                            <span class="fw-semibold text-dark text-truncate" style="max-width: 280px;">{{ $doc->title }}</span>
                                            <span class="badge bg-light text-secondary border">{{ $doc->category }}</span>
                                        </div>
                                        <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="btn btn-sm btn-link text-decoration-none">View</a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label small fw-semibold text-secondary">Document Category</label>
                            <select name="document_category" class="form-select">
                                <option value="Past Agreement / Contract" selected>Past Agreement / Contract</option>
                                <option value="Previous Quotation / Estimate">Previous Quotation / Estimate</option>
                                <option value="Old Invoices & Payment History">Old Invoices & Payment History</option>
                                <option value="Site Plans & Garden Photos">Site Plans & Garden Photos</option>
                                <option value="Maintenance / Service Log">Maintenance / Service Log</option>
                                <option value="Existing Client Record">Other Old Record</option>
                            </select>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label small fw-semibold text-secondary">Upload More Files (CSV, Excel, PDF, Photos)</label>
                            <input type="file" name="client_documents[]" class="form-control" multiple accept=".csv,.txt,.pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.webp">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div class="col-12 col-lg-4">
            @if($channelKey === 'promotion_email' || $channelKey === 'existing_client')
                <div class="card border-0 shadow-sm mb-4 border-top border-3 border-success">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-semibold text-success"><i class="bi bi-calendar-check me-2"></i>Follow-up Date</h6>
                    </div>
                    <div class="card-body p-4">
                        <input type="hidden" name="source" value="{{ $lead->source }}">
                        <input type="hidden" name="client_type" value="{{ $lead->client_type }}">
                        <input type="hidden" name="status" value="{{ $lead->status ?? ($channelKey === 'existing_client' ? 'Site Visit Required' : 'Contacted') }}">

                        <div class="mb-0">
                            <label for="follow_up_date" class="form-label small fw-semibold text-secondary">Follow-up Date <span class="text-muted fw-normal">(optional)</span></label>
                            <input type="date" name="follow_up_date" id="follow_up_date"
                                   class="form-control @error('follow_up_date') is-invalid @enderror"
                                   value="{{ old('follow_up_date', $lead->follow_up_date?->format('Y-m-d')) }}">
                            <div class="form-text">Optional. Pick a date if a follow-up is required.</div>
                            @error('follow_up_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold"><i class="bi bi-sliders text-warning me-2"></i>Classification & Assignment</h6></div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Source</label>
                            <div class="form-control bg-light text-muted">{{ $lead->source ?: '—' }}</div>
                        </div>
                        <input type="hidden" name="client_type" value="{{ $lead->client_type }}">
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
                        <div class="mb-0">
                            <label for="follow_up_date" class="form-label small fw-semibold text-secondary">Follow-up Date <span class="text-muted fw-normal">(optional)</span></label>
                            <input type="date" name="follow_up_date" id="follow_up_date"
                                   class="form-control @error('follow_up_date') is-invalid @enderror"
                                   value="{{ old('follow_up_date', $lead->follow_up_date?->format('Y-m-d')) }}">
                            <div class="form-text">Optional. If customer gives a follow-up date, set it here to get automated reminders.</div>
                            @error('follow_up_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            @endif

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
