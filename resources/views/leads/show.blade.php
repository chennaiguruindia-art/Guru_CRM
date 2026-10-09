@extends('layouts.app')
@section('title','Visit — '.$lead->lead_code)

@section('content')
@php
    // Which list this Visit sits under, so Breadcrumb and Back return there.
    // Unrecognised sources — every legacy row included — belong to the Cold
    // Call catch-all, and anyone who may not open their real list is sent
    // there instead of handed a link that would 403.
    $channelKey = array_search($lead->source, \App\Models\Lead::CHANNELS, true) ?: 'cold_call';
    if (! auth()->user()?->can(\App\Models\Lead::CHANNEL_LISTS[$channelKey][2])) {
        $channelKey = 'cold_call';
    }
    [$backLabel, $backRoute] = \App\Models\Lead::CHANNEL_LISTS[$channelKey];
@endphp
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-funnel-fill text-success me-2"></i>{{ $lead->name }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route($backRoute) }}" class="text-success text-decoration-none">{{ $backLabel }}</a></li>
            <li class="breadcrumb-item active">{{ $lead->lead_code }}</li>
        </ol></nav>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @can('leads.convert')
        @if($lead->status !== 'Converted')
        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#convertModal">
            <i class="bi bi-arrow-up-right-circle me-1"></i>Convert
        </button>
        @endif
        @endcan
        <a href="{{ route('leads.edit',$lead) }}" class="btn btn-primary btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
        <a href="{{ route($backRoute) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="row g-4">
    {{-- Left: Details --}}
    <div class="col-12 col-lg-8">
        {{-- Overview Card --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-person text-success me-2"></i>Visit Details</h6>
                <div class="d-flex gap-2">
                    <span class="badge {{ $lead->status_badge_class }}">{{ $lead->status }}</span>
                    <span class="badge {{ $lead->priority_badge_class }}">{{ $lead->priority }}</span>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="text-muted small d-block">Visit ID</label>
                        <span class="fw-semibold text-success">{{ $lead->lead_code }}</span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small d-block">Site Name</label>
                        <span class="fw-semibold">{{ $lead->name }}</span>
                    </div>
                    @if($lead->company_name)
                    <div class="col-sm-6">
                        <label class="text-muted small d-block">Company</label>
                        <span>{{ $lead->company_name }}</span>
                    </div>
                    @endif
                    <div class="col-sm-6">
                        <label class="text-muted small d-block">Phone</label>
                        <a href="tel:{{ $lead->phone }}" class="text-decoration-none fw-semibold">{{ $lead->phone }}</a>
                        @if($lead->whatsapp)
                        <a href="https://wa.me/{{ preg_replace('/\D/','',$lead->whatsapp) }}" target="_blank" class="ms-2 text-success" title="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                        @endif
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small d-block">Email</label>
                        @if($lead->email)<a href="mailto:{{ $lead->email }}" class="text-decoration-none">{{ $lead->email }}</a>@else<span class="text-muted">—</span>@endif
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small d-block">Source</label>
                        <span class="badge bg-light text-dark border">{{ $lead->source }}</span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small d-block">New or Existing Client</label>
                        @if($lead->client_type === 'Existing Client')
                            <span class="badge bg-soft-info text-info border">Existing Client</span>
                        @elseif($lead->client_type === 'New Client')
                            <span class="badge bg-soft-success text-success border">New Client</span>
                        @else
                            <span>—</span>
                        @endif
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small d-block">Purpose of Visit</label>
                        <span>{{ $lead->purpose_of_visit ?: '—' }}</span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small d-block">Type of Service</label>
                        @if($lead->service_type === 'AMC')
                            <span class="badge bg-soft-warning text-warning border">AMC Contract</span>
                        @elseif($lead->service_type === 'Single Project')
                            <span class="badge bg-soft-primary text-primary border">Single Project</span>
                        @else
                            <span>—</span>
                        @endif
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small d-block">Interested Service</label>
                        <span>{{ $lead->interested_service ?: '—' }}</span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small d-block">Expected Value</label>
                        <span class="fw-semibold text-success">₹{{ number_format($lead->expected_value ?? 0) }}</span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small d-block">Expected Closing</label>
                        <span>{{ $lead->expected_closing_date ? $lead->expected_closing_date->format('d M Y') : '—' }}</span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small d-block">Follow-up Date</label>
                        <span class="{{ $lead->follow_up_date && $lead->follow_up_date->isPast() ? 'text-danger fw-semibold' : '' }}">
                            {{ $lead->follow_up_date ? $lead->follow_up_date->format('d M Y') : '—' }}
                            @if($lead->follow_up_date && $lead->follow_up_date->isPast()) <span class="badge bg-danger ms-1">Overdue</span>@endif
                        </span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small d-block">Assigned To</label>
                        <span>{{ $lead->assignedTo?->name ?? '—' }}</span>
                    </div>
                    @if($lead->address)
                    <div class="col-12">
                        <label class="text-muted small d-block">Address</label>
                        <span>{{ $lead->address }}{{ $lead->city ? ', '.$lead->city : '' }}{{ $lead->state ? ', '.$lead->state : '' }} {{ $lead->pincode }}</span>
                    </div>
                    @endif
                    @if($lead->remarks)
                    <div class="col-12">
                        <label class="text-muted small d-block">Remarks</label>
                        <p class="mb-0 text-muted small bg-light rounded p-3">{{ $lead->remarks }}</p>
                    </div>
                    @endif
                    @if($lead->visiting_card_photo)
                    <div class="col-12">
                        <label class="text-muted small d-block">Visiting Card</label>
                        <a href="{{ asset('storage/' . $lead->visiting_card_photo) }}" target="_blank">
                            <img src="{{ asset('storage/' . $lead->visiting_card_photo) }}" alt="Visiting card for {{ $lead->name }}"
                                 class="rounded border" style="max-height:160px">
                        </a>
                        <div class="form-text">Click to view full size.</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Activity Timeline --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-clock-history text-info me-2"></i>Activity Timeline</h6>
                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#activityModal">
                    <i class="bi bi-plus me-1"></i>Log Activity
                </button>
            </div>
            <div class="card-body p-4">
                @forelse($lead->activities as $activity)
                <div class="d-flex gap-3 mb-4">
                    <div class="timeline-dot bg-success rounded-circle flex-shrink-0" style="width:10px;height:10px;margin-top:6px;"></div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between">
                            <span class="fw-semibold small">{{ $activity->activity_type }}</span>
                            <span class="text-muted small">{{ $activity->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="mb-0 text-muted small">{{ $activity->description }}</p>
                        <span class="text-muted x-small">by {{ $activity->user?->name ?? 'System' }}</span>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center py-3 mb-0"><i class="bi bi-clock-history me-1"></i>No activities yet. Log your first activity above.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Right: Info sidebar --}}
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle text-secondary me-2"></i>Quick Info</h6></div>
            <div class="card-body p-3">
                <table class="table table-sm table-borderless mb-0 small">
                    <tr><td class="text-muted">Created</td><td class="text-end fw-semibold">{{ $lead->created_at->format('d M Y') }}</td></tr>
                    <tr><td class="text-muted">Last Updated</td><td class="text-end">{{ $lead->updated_at->diffForHumans() }}</td></tr>
                    <tr><td class="text-muted">Created By</td><td class="text-end">{{ $lead->createdBy?->name ?? '—' }}</td></tr>
                </table>
            </div>
        </div>

        @if($lead->status === 'Converted' && $lead->convertedCustomer)
        <div class="card border-0 shadow-sm border-start border-success border-3 mb-4">
            <div class="card-body p-3">
                <h6 class="fw-semibold text-success mb-2"><i class="bi bi-check2-circle me-2"></i>Converted to Client</h6>
                <a href="{{ route('customers.show',$lead->convertedCustomer) }}" class="btn btn-success btn-sm w-100">
                    View Client: {{ $lead->convertedCustomer->customer_code }}
                </a>
            </div>
        </div>
        @endif

        {{-- Quick actions --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold"><i class="bi bi-lightning text-warning me-2"></i>Quick Actions</h6></div>
            <div class="card-body p-3 d-grid gap-2">
                <a href="{{ route('leads.edit',$lead) }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-pencil me-1"></i>Edit Visit
                </a>
                @if($lead->phone)
                <a href="tel:{{ $lead->phone }}" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-telephone me-1"></i>Call {{ $lead->phone }}
                </a>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Log Activity Modal --}}
<div class="modal fade" id="activityModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('leads.activity',$lead) }}">
                @csrf
                <div class="modal-header"><h5 class="modal-title fw-semibold"><i class="bi bi-clock-history text-success me-2"></i>Log Activity</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Activity Type <span class="text-danger">*</span></label>
                        <select name="activity_type" class="form-select" required>
                            @foreach(['Call','Email','WhatsApp','Meeting','Site Visit','Follow-up','Note','Status Change','Other'] as $t)
                            <option value="{{ $t }}">{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control" rows="3" placeholder="What happened? What was discussed?" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Update Status to</label>
                        <select name="new_status" class="form-select">
                            <option value="">— Keep Current Status —</option>
                            @foreach(['Contacted','Qualified','Site Visit Required','Quotation Required','Lost'] as $s)
                            <option value="{{ $s }}" @selected($lead->status===$s)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-semibold">Next Follow-up Date</label>
                        <input type="date" name="follow_up_date" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-check2 me-1"></i>Save Activity</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Convert Modal --}}
<div class="modal fade" id="convertModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('leads.convert',$lead) }}">
                @csrf
                <div class="modal-header"><h5 class="modal-title fw-semibold"><i class="bi bi-arrow-up-right-circle text-warning me-2"></i>Convert to Client</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Client Type</label>
                        <select name="customer_type" class="form-select">
                            @foreach(['Individual','Company','Apartment','Villa','School','Hospital','Hotel','Factory','Corporate','Government','Other'] as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Type of Service decides what conversion creates, so the manual
                         tickbox only exists when the Visit has none chosen. --}}
                    @if($lead->service_type)
                        <div class="small py-2 px-3 rounded border bg-light mb-2">
                            <i class="bi bi-info-circle me-1"></i>Type of Service is <strong>{{ $lead->service_type }}</strong> — converting
                            {{ $lead->service_type === 'AMC' ? 'will create an AMC Contract' : 'will create a Project' }} for this Client.
                        </div>
                    @else
                        <div class="form-check mb-2">
                            <input type="checkbox" name="create_project" value="1" class="form-check-input" id="chkProj">
                            <label class="form-check-label small" for="chkProj">Create a Project too</label>
                        </div>
                        <div id="proj-wrap" class="d-none">
                            <input type="text" name="project_name" class="form-control form-control-sm" placeholder="Project name">
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-check2-circle me-1"></i>Convert Now</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('chkProj')?.addEventListener('change', function() {
    document.getElementById('proj-wrap').classList.toggle('d-none', !this.checked);
});
</script>
@endpush
