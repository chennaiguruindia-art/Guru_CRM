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
        @if($channel === 'existing_client')
        <a href="{{ route('leads.sample_template') }}" class="btn btn-outline-success btn-sm">
            <i class="bi bi-file-earmark-arrow-down me-1"></i>Download Sample Data (CSV)
        </a>
        @endif
        <span class="badge bg-soft-info text-info border"><i class="bi bi-inbox me-1"></i>Saved under {{ $channelLabel }}</span>
        <a href="{{ route($channelRoute) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

@if($channel === 'existing_client')
{{-- Standalone form for CSV bulk import so it never triggers single-visit validation on Site Name/Phone --}}
<form id="bulk-csv-form" method="POST" action="{{ route('leads.import') }}" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="source" value="{{ $source }}">
</form>
@endif

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
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Employee Name (Spoke with Lead / Handled By) <span class="text-danger">*</span></label>
                            <select name="assigned_to_id" class="form-select @error('assigned_to_id') is-invalid @enderror" required>
                                <option value="">-- Select Employee --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" @selected(old('assigned_to_id', auth()->id()) == $user->id)>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Select which employee or staff member spoke with this lead.</div>
                            @error('assigned_to_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
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

            {{-- Existing Client Old Records & Data Upload (Only shown for Existing Client channel) --}}
            @if($channel === 'existing_client')
            <div class="card border-0 shadow-sm mb-4 border-top border-4 border-primary" id="existing-client-docs-card">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h6 class="mb-0 fw-semibold text-primary">
                            <i class="bi bi-cloud-arrow-up-fill me-2"></i>Existing Client Old Records & Data Upload
                        </h6>
                        <small class="text-muted">Sample data download panni, client data fill panni upload panna direct-aa DB-la store aagidum.</small>
                    </div>
                    <a href="{{ route('leads.sample_template') }}" class="btn btn-sm btn-outline-success">
                        <i class="bi bi-file-earmark-arrow-down me-1"></i>Download Sample Data (CSV)
                    </a>
                </div>
                <div class="card-body p-4">
                    {{-- Nav Tabs --}}
                    <ul class="nav nav-pills mb-3 gap-2" id="clientRecordTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active small py-1 px-3" id="tab-csv-import" data-bs-toggle="pill" data-bs-target="#pane-csv-import" type="button" role="tab">
                                <i class="bi bi-file-earmark-spreadsheet me-1"></i>1. Bulk Upload Sample CSV to DB
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link small py-1 px-3" id="tab-doc-upload" data-bs-toggle="pill" data-bs-target="#pane-doc-upload" type="button" role="tab">
                                <i class="bi bi-paperclip me-1"></i>2. Attach Old Documents / Files
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="clientRecordTabContent">
                        {{-- Pane 1: CSV Bulk Import --}}
                        <div class="tab-pane fade show active" id="pane-csv-import" role="tabpanel">
                            <div class="p-3 bg-light rounded border mb-3">
                                <div class="row align-items-center g-3">
                                    <div class="col-md-7">
                                        <div class="fw-semibold text-dark small mb-1">
                                            <i class="bi bi-info-circle-fill text-primary me-1"></i>How to upload and store in Database:
                                        </div>
                                        <ol class="small text-muted mb-0 ps-3">
                                            <li>Click <strong>Download Sample Data</strong> to get the sample CSV format.</li>
                                            <li>Fill in existing clients' details (Name, Phone, Address, Service, Follow-up date).</li>
                                            <li>Select the filled CSV below and click <strong>Upload & Save to DB</strong>.</li>
                                        </ol>
                                    </div>
                                    <div class="col-md-5 text-md-end">
                                        <a href="{{ route('leads.sample_template') }}" class="btn btn-success btn-sm w-100 py-2 fw-semibold">
                                            <i class="bi bi-download me-1"></i>Download Sample Template (CSV)
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 align-items-center">
                                <div class="col-md-8">
                                    <label class="form-label small fw-semibold text-secondary">Select Filled Sample CSV File</label>
                                    <input type="file" name="import_file" id="import_file" form="bulk-csv-form" class="form-control" accept=".csv,.txt" required>
                                </div>
                                <div class="col-md-4 pt-md-4">
                                    <button type="submit" form="bulk-csv-form" formnovalidate class="btn btn-primary w-100 py-2 fw-semibold shadow-sm">
                                        <i class="bi bi-database-fill-add me-1"></i>Upload & Save to DB
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Pane 2: Attach Old Files & Documents --}}
                        <div class="tab-pane fade" id="pane-doc-upload" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <label class="form-label small fw-semibold text-secondary">Record / Document Category</label>
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
                                    <label for="client_documents" class="form-label small fw-semibold text-secondary">
                                        Select Files (CSV, Excel, PDF, Word, Photos)
                                    </label>
                                    <input type="file" name="client_documents[]" id="client_documents"
                                           class="form-control @error('client_documents.*') is-invalid @enderror"
                                           multiple
                                           accept=".csv,.txt,.pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.webp"
                                           onchange="handleClientFilesSelect(this)">
                                    @error('client_documents.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    <div class="form-text">CSV, Excel, PDF, Word, JPG, PNG up to 10 MB each. Select multiple files together.</div>
                                </div>

                                <div class="col-12">
                                    <div id="selected-files-list" class="p-3 bg-light rounded border d-none">
                                        <div class="small fw-semibold text-secondary mb-2"><i class="bi bi-paperclip me-1"></i>Selected Files to Upload:</div>
                                        <div id="selected-files-items" class="d-flex flex-column gap-2"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

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
    function handleClientFilesSelect(input) {
        const container = document.getElementById('selected-files-list');
        const items = document.getElementById('selected-files-items');
        if (!container || !items) return;

        items.innerHTML = '';
        if (!input.files || input.files.length === 0) {
            container.classList.add('d-none');
            return;
        }

        container.classList.remove('d-none');
        Array.from(input.files).forEach((file) => {
            const ext = file.name.split('.').pop().toLowerCase();
            let iconClass = 'bi-file-earmark text-secondary';
            if (['pdf'].includes(ext)) iconClass = 'bi-file-earmark-pdf text-danger';
            else if (['doc', 'docx'].includes(ext)) iconClass = 'bi-file-earmark-word text-primary';
            else if (['csv', 'txt', 'xls', 'xlsx'].includes(ext)) iconClass = 'bi-file-earmark-spreadsheet text-success';
            else if (['jpg', 'jpeg', 'png', 'webp'].includes(ext)) iconClass = 'bi-file-earmark-image text-info';

            const sizeKb = (file.size / 1024).toFixed(1);
            const sizeStr = sizeKb > 1024 ? (sizeKb / 1024).toFixed(2) + ' MB' : sizeKb + ' KB';

            const row = document.createElement('div');
            row.className = 'd-flex align-items-center justify-content-between p-2 bg-white rounded border small';
            row.innerHTML = `
                <div class="d-flex align-items-center gap-2 text-truncate me-2">
                    <i class="bi ${iconClass} fs-5"></i>
                    <div>
                        <div class="fw-semibold text-dark text-truncate" style="max-width: 320px;">${escHtml(file.name)}</div>
                        <span class="text-muted" style="font-size: 0.72rem;">${sizeStr} · .${ext.toUpperCase()}</span>
                    </div>
                </div>
                <span class="badge bg-success-subtle text-success border">Ready to upload</span>
            `;
            items.appendChild(row);
        });
    }

    function escHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

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
