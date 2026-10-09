@extends('layouts.app')
@php
    // $channel is the sidebar key. Cold Calls carries every Visit, so it is
    // also what /leads renders — there is no separate Visits list any more.
    $pageTitle = \App\Models\Lead::CHANNEL_LISTS[$channel][0]
        ?? \App\Models\Lead::CHANNEL_LISTS['cold_call'][0];

    [$pageSubtitle, $pageIcon] = match ($channel) {
        'promotion_email' => ['Every promotional mail or call sent from this list is filed as a Visit.', 'bi-envelope'],
        'existing_client' => ['Visits to Clients that are already in the system.', 'bi-person-check'],
        default            => ['Every visit logged from this list, whatever its channel.', 'bi-telephone'],
    };

    // Null-safe: the view is also reachable outside a routed request.
    $listRoute = request()->route()?->getName() ?? 'cold_calls.index';
    $createUrl = route('leads.create', $channel === 'cold_call' ? [] : ['source' => $channel]);
@endphp
@section('title', $pageTitle)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi {{ $pageIcon }} text-success me-2"></i>{{ $pageTitle }}</h4>
        <p class="text-muted small mb-0">{{ $pageSubtitle }}</p>
    </div>
    <a href="{{ $createUrl }}" class="btn btn-success shadow-sm">
        <i class="bi bi-plus-lg me-1"></i> Add New Visit
    </a>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route($listRoute) }}" id="filter-form">
            <div class="row g-2">
                <div class="col-12 col-md-4">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name, phone, company…" value="{{ request('search') }}">
                </div>
                <div class="col-6 col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        @foreach(['New','Contacted','Qualified','Site Visit Required','Quotation Required','Converted','Lost'] as $s)
                            <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                @if($channel === 'cold_call')
                <div class="col-6 col-md-2">
                    <select name="source" class="form-select form-select-sm">
                        <option value="">All Sources</option>
                        @foreach($sources as $src)
                            <option value="{{ $src }}" @selected(request('source')===$src)>{{ $src }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-6 col-md-2">
                    <select name="priority" class="form-select form-select-sm">
                        <option value="">All Priorities</option>
                        @foreach(['Low','Medium','High','Urgent'] as $p)
                            <option value="{{ $p }}" @selected(request('priority')===$p)>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-success btn-sm flex-fill"><i class="bi bi-search me-1"></i>Filter</button>
                    <a href="{{ route($listRoute) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-lg"></i></a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Summary badges --}}
<div class="d-flex flex-wrap gap-2 mb-3">
    <span class="badge bg-soft-info text-info px-3 py-2">Total: {{ $leads->total() }}</span>
    @foreach(['New'=>'info','Contacted'=>'primary','Converted'=>'success','Lost'=>'danger'] as $st=>$color)
        <a href="{{ route($listRoute,['status'=>$st]) }}" class="badge text-decoration-none bg-soft-{{ $color }} text-{{ $color }} px-3 py-2">{{ $st }}</a>
    @endforeach
</div>

{{-- Table --}}
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-crm align-middle mb-0">
            <thead>
                <tr>
                    <th>Visit ID</th>
                    <th>Site / Company</th>
                    <th>Phone</th>
                    <th>Source</th>
                    <th>Service Interest</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Assigned To</th>
                    <th>Follow-up</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leads as $lead)
                <tr id="lead-row-{{ $lead->id }}">
                    <td><a href="{{ route('leads.show',$lead) }}" class="fw-semibold text-success text-decoration-none">{{ $lead->lead_code }}</a></td>
                    <td>
                        <div class="fw-semibold text-dark">{{ $lead->name }}</div>
                        @if($lead->company_name)<div class="small text-muted">{{ $lead->company_name }}</div>@endif
                    </td>
                    <td class="small">{{ $lead->phone }}</td>
                    <td><span class="badge bg-light text-dark border">{{ $lead->source }}</span></td>
                    <td class="small">{{ $lead->interested_service ?: '—' }}</td>
                    <td>
                        <span class="badge badge-status {{ $lead->status_badge_class }}">{{ $lead->status }}</span>
                    </td>
                    <td>
                        <span class="badge {{ $lead->priority_badge_class }}">{{ $lead->priority }}</span>
                    </td>
                    <td class="small">{{ $lead->assignedTo?->name ?? '—' }}</td>
                    <td class="small {{ $lead->follow_up_date && $lead->follow_up_date->isPast() ? 'text-danger fw-semibold' : '' }}">
                        {{ $lead->follow_up_date ? $lead->follow_up_date->format('d M Y') : '—' }}
                    </td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-1">
                            <a href="{{ route('leads.show',$lead) }}" class="btn btn-sm btn-outline-success" title="View"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('leads.edit',$lead) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                            @can('leads.convert')
                            @if($lead->status !== 'Converted')
                            <button class="btn btn-sm btn-outline-warning btn-convert" data-id="{{ $lead->id }}" data-name="{{ $lead->name }}" data-service="{{ $lead->service_type }}" title="Convert to Client"><i class="bi bi-arrow-up-right-circle"></i></button>
                            @endif
                            @endcan
                            <button class="btn btn-sm btn-outline-danger btn-delete" data-id="{{ $lead->id }}" data-name="{{ $lead->name }}" title="Delete"><i class="bi bi-trash"></i></button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center py-5">
                        <i class="bi bi-funnel fs-1 text-muted d-block mb-2"></i>
                        <div class="text-muted">No visits found. <a href="{{ $createUrl }}">Add your first visit</a>.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($leads->hasPages())
    <div class="card-footer bg-white border-top py-3">
        {{ $leads->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

{{-- Convert Modal --}}
<div class="modal fade" id="convertModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="convert-form" method="POST">
                @csrf
                <div class="modal-header"><h5 class="modal-title fw-semibold"><i class="bi bi-arrow-up-right-circle text-warning me-2"></i>Convert Visit to Client</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <p class="text-muted small">Converting: <strong id="convert-lead-name"></strong></p>
                    <p class="small d-none mb-0 py-2 px-3 rounded border bg-light" id="convert-service-note"></p>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Client Type</label>
                        <select name="customer_type" class="form-select">
                            @foreach(['Individual','Company','Apartment','Villa','School','Hospital','Hotel','Factory','Corporate','Government','Other'] as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="project-option">
                        <div class="form-check">
                            <input type="checkbox" name="create_project" value="1" class="form-check-input" id="chkCreateProject">
                            <label class="form-check-label small" for="chkCreateProject">Also create a Project for this visit</label>
                        </div>
                        <div id="project-name-wrap" class="mt-2 d-none">
                            <input type="text" name="project_name" class="form-control form-control-sm" placeholder="Project name">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-check2-circle me-1"></i>Convert Now</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <form id="delete-form" method="POST">
                @csrf @method('DELETE')
                <div class="modal-header"><h5 class="modal-title text-danger"><i class="bi bi-trash me-1"></i>Delete Visit</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <p class="text-muted small mb-0">Delete <strong id="delete-lead-name"></strong>? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Convert modal
document.querySelectorAll('.btn-convert').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        document.getElementById('convert-lead-name').textContent = this.dataset.name;
        document.getElementById('convert-form').action = `/leads/${id}/convert`;

        // Type of Service decides what conversion creates, so the manual
        // "create a project" tickbox only exists when none was chosen.
        const service = this.dataset.service || '';
        const note = document.getElementById('convert-service-note');
        const projectOption = document.getElementById('project-option');
        if (service) {
            note.textContent = service === 'AMC'
                ? 'Type of Service is AMC — converting will create an AMC Contract for this Client.'
                : 'Type of Service is Single Project — converting will create a Project for this Client.';
            note.classList.remove('d-none');
            projectOption.classList.add('d-none');
            document.getElementById('chkCreateProject').checked = false;
        } else {
            note.classList.add('d-none');
            projectOption.classList.remove('d-none');
        }

        new bootstrap.Modal(document.getElementById('convertModal')).show();
    });
});

// Create project toggle
document.getElementById('chkCreateProject')?.addEventListener('change', function() {
    document.getElementById('project-name-wrap').classList.toggle('d-none', !this.checked);
});

// Delete modal
document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('delete-lead-name').textContent = this.dataset.name;
        document.getElementById('delete-form').action = `/leads/${this.dataset.id}`;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    });
});
</script>
@endpush
