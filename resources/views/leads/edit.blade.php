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
                            <label class="form-label small fw-semibold text-secondary">Site Name <span class="text-danger">*</span></label>
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

            {{-- Client Documents & Records --}}
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
                        <label class="form-label small fw-semibold text-secondary">New or Existing Client</label>
                        <select name="client_type" class="form-select @error('client_type') is-invalid @enderror">
                            <option value="">-- Select --</option>
                            @foreach(\App\Models\Lead::CLIENT_TYPES as $type)
                                <option value="{{ $type }}" @selected(old('client_type',$lead->client_type)===$type)>{{ $type }}</option>
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
