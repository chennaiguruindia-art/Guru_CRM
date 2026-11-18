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
    <div class="d-flex gap-2 align-items-center">
        <a href="{{ route('leads.sample_template') }}" class="btn btn-outline-success shadow-sm" title="Download Sample CSV Data Template">
            <i class="bi bi-file-earmark-arrow-down me-1"></i> Sample CSV
        </a>
        <a href="{{ $createUrl }}" class="btn btn-success shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Add New Visit
        </a>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route($listRoute) }}" id="filter-form">
            <div class="row g-2">
                <div class="col-12 col-md-5">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name, phone, company…" value="{{ request('search') }}">
                </div>
                <div class="col-6 col-md-3">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        @foreach(['New','Contacted','Qualified','Site Visit Required','Quotation Required','Converted','Lost'] as $s)
                            <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                @if($branches->isNotEmpty())
                <div class="col-6 col-md-2">
                    <select name="branch_id" class="form-select form-select-sm">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" @selected(request('branch_id')==$branch->id)>{{ $branch->display_name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-6 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-success btn-sm flex-fill"><i class="bi bi-search me-1"></i>Filter</button>
                    <a href="{{ route($listRoute) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-lg"></i></a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Everything from here down is swapped in place by the filter script. --}}
<div id="visit-list-region">

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
                    @if($channel === 'promotion_email')
                        <th>Lead ID</th>
                        <th>Mode of Approach</th>
                        <th>Company Name</th>
                        <th>Contact Person & Designation</th>
                        <th>Email & Phone</th>
                        <th>Location</th>
                        <th>Employee</th>
                        <th>Follow-up</th>
                        <th class="text-end">Actions</th>
                    @elseif($channel === 'existing_client')
                        <th>Visit ID</th>
                        <th>Site Name</th>
                        <th>Site Contact Person</th>
                        <th>Operation Team Employee</th>
                        <th>Purpose of Visit</th>
                        <th>Area & Location</th>
                        <th>Remarks</th>
                        <th>Follow-up</th>
                        <th class="text-end">Actions</th>
                    @else
                        <th>Visit ID</th>
                        <th>Site / Company</th>
                        <th>Phone</th>
                        <th>Source</th>
                        <th>Service Interest</th>
                        <th>Status</th>
                        <th>Employee</th>
                        <th>Follow-up</th>
                        <th class="text-end">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($leads as $lead)
                <tr id="lead-row-{{ $lead->id }}">
                    @if($channel === 'promotion_email')
                        <td><a href="{{ route('leads.show',$lead) }}" class="fw-semibold text-success text-decoration-none">{{ $lead->lead_code }}</a></td>
                        <td>
                            @php
                                $mode = $lead->mode_of_approach ?: 'Email';
                                $badgeClass = match($mode) {
                                    'Call' => 'bg-info-subtle text-info-emphasis border border-info-subtle',
                                    'Email' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                    'WhatsApp' => 'bg-success-subtle text-success border border-success-subtle',
                                    default => 'bg-secondary-subtle text-secondary border'
                                };
                                $modeIcon = match($mode) {
                                    'Call' => 'bi-telephone-fill',
                                    'Email' => 'bi-envelope-fill',
                                    'WhatsApp' => 'bi-whatsapp',
                                    default => 'bi-chat-dots-fill'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}"><i class="bi {{ $modeIcon }} me-1"></i>{{ $mode }}</span>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $lead->company_name ?: $lead->name }}</div>
                            @if($lead->company_name && $lead->name && $lead->name !== $lead->company_name)
                                <div class="small text-muted">{{ $lead->name }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $lead->contact_person ?: '—' }}</div>
                            @if($lead->designation)
                                <div class="small text-muted">{{ $lead->designation }}</div>
                            @endif
                        </td>
                        <td>
                            @if($lead->email)<div class="small text-dark"><i class="bi bi-envelope me-1 text-muted"></i>{{ $lead->email }}</div>@endif
                            <div class="small text-muted"><i class="bi bi-telephone me-1 text-muted"></i>{{ $lead->phone }}</div>
                        </td>
                        <td class="small">{{ $lead->city ?: ($lead->state ?: '—') }}</td>
                        <td class="small">{{ $lead->employee_name ?: ($lead->assignedTo?->name ?? '—') }}</td>
                        <td class="small {{ $lead->follow_up_date && $lead->follow_up_date->isPast() ? 'text-danger fw-semibold' : '' }}">
                            {{ $lead->follow_up_date ? $lead->follow_up_date->format('d M Y') : '—' }}
                        </td>
                    @elseif($channel === 'existing_client')
                        <td><a href="{{ route('leads.show',$lead) }}" class="fw-semibold text-success text-decoration-none">{{ $lead->lead_code }}</a></td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $lead->name }}</div>
                            @if($lead->company_name)<div class="small text-muted">{{ $lead->company_name }}</div>@endif
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">
                                {{ $lead->contact_person ?: ($lead->designation ? '—' : '—') }}
                                @if($lead->designation)
                                    <span class="badge bg-secondary-subtle text-secondary border ms-1" style="font-size:0.75rem;">{{ $lead->designation }}</span>
                                @endif
                            </div>
                            @if($lead->phone && $lead->phone !== '—')
                                <div class="small text-muted"><i class="bi bi-telephone me-1"></i>{{ $lead->phone }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $lead->employee_name ?: ($lead->assignedTo?->name ?? '—') }}</div>
                        </td>
                        <td>
                            <span class="badge bg-soft-info text-info border">{{ $lead->purpose_of_visit ?: 'Routine Inspection' }}</span>
                        </td>
                        <td>
                            <div class="small text-dark fw-semibold">{{ $lead->area ?: ($lead->city ?: '—') }}</div>
                            @if($lead->address && $lead->area)<div class="small text-muted text-truncate" style="max-width: 180px;">{{ $lead->address }}</div>@endif
                        </td>
                        <td class="small text-muted text-truncate" style="max-width: 220px;" title="{{ $lead->remarks }}">
                            {{ $lead->remarks ? Str::limit($lead->remarks, 40) : '—' }}
                        </td>
                        <td class="small {{ $lead->follow_up_date && $lead->follow_up_date->isPast() ? 'text-danger fw-semibold' : '' }}">
                            {{ $lead->follow_up_date ? $lead->follow_up_date->format('d M Y') : '—' }}
                        </td>
                    @else
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
                        <td class="small">{{ $lead->employee_name ?: ($lead->assignedTo?->name ?? '—') }}</td>
                        <td class="small {{ $lead->follow_up_date && $lead->follow_up_date->isPast() ? 'text-danger fw-semibold' : '' }}">
                            {{ $lead->follow_up_date ? $lead->follow_up_date->format('d M Y') : '—' }}
                        </td>
                    @endif
                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-1">
                            <a href="{{ route('leads.show',$lead) }}" class="btn btn-sm btn-outline-success" title="View"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('leads.edit',$lead) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                            @can('leads.convert')
                            @if($lead->status !== 'Converted')
                            <button class="btn btn-sm btn-outline-success btn-convert" data-id="{{ $lead->id }}" data-name="{{ $lead->name }}" data-service="{{ $lead->service_type }}" title="Convert to Client"><i class="bi bi-person-check"></i></button>
                            @if($lead->status !== 'Lost')
                            <button class="btn btn-sm btn-outline-danger btn-reject" data-id="{{ $lead->id }}" data-name="{{ $lead->name }}" title="Reject / Mark as Lost"><i class="bi bi-x-circle"></i></button>
                            @endif
                            @else
                            @if($lead->converted_customer_id)
                            <a href="{{ route('customers.show', $lead->converted_customer_id) }}" class="btn btn-sm btn-success text-white" title="Client: {{ $lead->convertedCustomer?->customer_code }}"><i class="bi bi-person-check-fill"></i></a>
                            @endif
                            @endif
                            @endcan
                            <button class="btn btn-sm btn-outline-secondary btn-delete" data-id="{{ $lead->id }}" data-name="{{ $lead->name }}" title="Delete"><i class="bi bi-trash"></i></button>
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
</div>

{{-- Convert Modal --}}
<div class="modal fade" id="convertModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="convert-form" method="POST">
                @csrf
                <div class="modal-header"><h5 class="modal-title fw-semibold text-success"><i class="bi bi-person-check-fill text-success me-2"></i>Convert Visit to Client</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
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

{{-- Reject Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="reject-form" method="POST">
                @csrf
                <div class="modal-header"><h5 class="modal-title fw-semibold text-danger"><i class="bi bi-x-circle text-danger me-2"></i>Reject / Mark as Lost</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">Are you sure you want to mark visit <strong id="reject-lead-name"></strong> as Lost / Rejected?</p>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Reason (Optional)</label>
                        <textarea name="reason" rows="3" class="form-control" placeholder="e.g. Client not interested, budget mismatch, chose competitor..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-x-circle me-1"></i>Confirm Reject</button>
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
<style>
    /* Held back while the filters are fetching, so a click cannot land on rows
       that are about to be replaced. */
    #visit-list-region { transition: opacity .15s ease; }
    #visit-list-region.is-loading { opacity: .4; pointer-events: none; }
</style>
<script>
// The row buttons live inside the swapped region, so binding has to be a
// function that runs again after every refresh rather than a one-off loop.
function bindVisitRowButtons() {
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

    document.querySelectorAll('.btn-reject').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            document.getElementById('reject-lead-name').textContent = this.dataset.name;
            document.getElementById('reject-form').action = `/leads/${id}/reject`;
            new bootstrap.Modal(document.getElementById('rejectModal')).show();
        });
    });

    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('delete-lead-name').textContent = this.dataset.name;
            document.getElementById('delete-form').action = `/leads/${this.dataset.id}`;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        });
    });
}

bindVisitRowButtons();

// Create project toggle — in the modal, outside the swapped region.
document.getElementById('chkCreateProject')?.addEventListener('change', function() {
    document.getElementById('project-name-wrap').classList.toggle('d-none', !this.checked);
});

// Pick a branch or a status and the list follows straight away: there is no
// Filter button to remember. The server still renders every row and only the
// region is replaced, so the markup has one source of truth and cannot drift
// between the two.
const visitRegion = document.getElementById('visit-list-region');
const visitForm = document.getElementById('filter-form');

if (visitRegion && visitForm) {
    let searchTimer;

    const applyFilters = () => {
        const url = visitForm.action + '?' + new URLSearchParams(new FormData(visitForm)).toString();

        fetch(url)
            .then((res) => {
                if (!res.ok) throw new Error(res.status);
                return res.text();
            })
            .then((html) => {
                const next = new DOMParser().parseFromString(html, 'text/html')
                    .getElementById('visit-list-region');
                if (!next) throw new Error('list region missing');

                visitRegion.innerHTML = next.innerHTML;
                bindVisitRowButtons();
                visitRegion.classList.remove('is-loading');
                history.pushState({ visitFilters: true }, '', url);
            })
            // Rather than leave a stale list on screen, fall back to a plain
            // page load — exactly what pressing Filter would have done.
            .catch(() => visitForm.submit());
    };

    visitForm.querySelectorAll('select').forEach((el) => {
        el.addEventListener('change', () => {
            visitRegion.classList.add('is-loading');
            applyFilters();
        });
    });

    // Typing needs a pause, or every keystroke would fetch a fresh list.
    visitForm.querySelector('input[name="search"]')?.addEventListener('input', () => {
        clearTimeout(searchTimer);
        visitRegion.classList.add('is-loading');
        searchTimer = setTimeout(applyFilters, 400);
    });

    window.addEventListener('popstate', () => location.reload());
}
</script>
@endpush
