@extends('layouts.app')

@section('title', 'Ticket: ' . $complaint->ticket_number)

@section('content')
<div class="container-fluid">

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @php
        $priorityClass = match($complaint->priority) {
            'Critical' => 'danger',
            'High'     => 'warning',
            'Medium'   => 'info',
            'Low'      => 'secondary',
            default    => 'secondary',
        };
        $statusClass = match($complaint->status) {
            'Open'        => 'danger',
            'Assigned'    => 'warning',
            'In Progress' => 'info',
            'Resolved'    => 'success',
            'Closed'      => 'secondary',
            default       => 'secondary',
        };
    @endphp

    {{-- Page Header --}}
    <div class="row align-items-center mb-4">
        <div class="col">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h1 class="h3 mb-0 fw-bold text-dark">
                    <i class="bi bi-ticket-perforated me-2 text-danger"></i>
                    {{ $complaint->ticket_number }}
                </h1>
                <span class="badge bg-{{ $priorityClass }} fs-6">{{ $complaint->priority }}</span>
                <span class="badge bg-{{ $statusClass }} fs-6">{{ $complaint->status }}</span>
            </div>
            <p class="text-muted mb-0 small mt-1">{{ $complaint->subject }}</p>
        </div>
        <div class="col-auto d-flex gap-2 flex-wrap">
            <a href="{{ route('complaints.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to Complaints
            </a>
        </div>
    </div>

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-1 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- 2-Column Main Layout --}}
    <div class="row g-4">

        {{-- LEFT: Ticket Info Card --}}
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-info-circle me-2 text-muted"></i>Ticket Information
                    </h6>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">

                        <dt class="col-sm-4 text-muted fw-normal small text-uppercase">Ticket Number</dt>
                        <dd class="col-sm-8 fw-semibold font-monospace">
                            {{ $complaint->ticket_number }}
                        </dd>

                        <dt class="col-sm-4 text-muted fw-normal small text-uppercase">Client</dt>
                        <dd class="col-sm-8">
                            {{ optional($complaint->customer)->name ?? '—' }}
                        </dd>

                        <dt class="col-sm-4 text-muted fw-normal small text-uppercase">Project</dt>
                        <dd class="col-sm-8">
                            {{ optional($complaint->project)->name ?? '—' }}
                        </dd>

                        <dt class="col-sm-4 text-muted fw-normal small text-uppercase">Priority</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-{{ $priorityClass }}">{{ $complaint->priority }}</span>
                        </dd>

                        <dt class="col-sm-4 text-muted fw-normal small text-uppercase">Status</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-{{ $statusClass }}">{{ $complaint->status }}</span>
                        </dd>

                        <dt class="col-sm-4 text-muted fw-normal small text-uppercase">Assigned To</dt>
                        <dd class="col-sm-8">
                            @if($complaint->assignedTo)
                                <i class="bi bi-person-circle me-1 text-muted"></i>{{ $complaint->assignedTo->name }}
                            @else
                                <span class="text-muted fst-italic">Unassigned</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4 text-muted fw-normal small text-uppercase">Created By</dt>
                        <dd class="col-sm-8">
                            {{ optional($complaint->createdBy)->name ?? '—' }}
                        </dd>

                        <dt class="col-sm-4 text-muted fw-normal small text-uppercase">Created At</dt>
                        <dd class="col-sm-8 text-muted small">
                            {{ $complaint->created_at->format('d M Y, g:i A') }}
                            <span class="ms-1 text-secondary fst-italic">({{ $complaint->created_at->diffForHumans() }})</span>
                        </dd>

                        <dt class="col-sm-4 text-muted fw-normal small text-uppercase">Resolved At</dt>
                        <dd class="col-sm-8">
                            @if($complaint->resolved_at)
                                <span class="text-success">
                                    <i class="bi bi-check-circle me-1"></i>
                                    {{ \Carbon\Carbon::parse($complaint->resolved_at)->format('d M Y, g:i A') }}
                                </span>
                            @else
                                <span class="text-muted fst-italic">Not yet resolved</span>
                            @endif
                        </dd>

                    </dl>
                </div>
            </div>
        </div>

        {{-- RIGHT: Status Update & Resolution Card --}}
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-arrow-repeat me-2 text-muted"></i>Update Status & Resolution
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('complaints.update', $complaint) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        {{-- Status --}}
                        <div class="mb-3">
                            <label for="status" class="form-label fw-semibold">
                                Status <span class="text-danger">*</span>
                            </label>
                            <select name="status"
                                    id="status"
                                    class="form-select @error('status') is-invalid @enderror"
                                    required>
                                <option value="Open"
                                    {{ old('status', $complaint->status) === 'Open' ? 'selected' : '' }}>
                                    Open
                                </option>
                                <option value="Assigned"
                                    {{ old('status', $complaint->status) === 'Assigned' ? 'selected' : '' }}>
                                    Assigned
                                </option>
                                <option value="In Progress"
                                    {{ old('status', $complaint->status) === 'In Progress' ? 'selected' : '' }}>
                                    In Progress
                                </option>
                                <option value="Resolved"
                                    {{ old('status', $complaint->status) === 'Resolved' ? 'selected' : '' }}>
                                    Resolved
                                </option>
                                <option value="Closed"
                                    {{ old('status', $complaint->status) === 'Closed' ? 'selected' : '' }}>
                                    Closed
                                </option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Resolution Notes --}}
                        <div class="mb-4">
                            <label for="resolution_notes" class="form-label fw-semibold">
                                Resolution Notes
                            </label>
                            <textarea name="resolution_notes"
                                      id="resolution_notes"
                                      rows="5"
                                      class="form-control @error('resolution_notes') is-invalid @enderror"
                                      placeholder="Describe how the complaint was resolved or any actions taken…">{{ old('resolution_notes', $complaint->resolution_notes) }}</textarea>
                            @error('resolution_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Setting status to <strong>Resolved</strong> will automatically record the resolution timestamp.
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-arrow-repeat me-1"></i>Update Complaint
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

    </div>

    {{-- Full-Width Description Card --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-file-text me-2 text-muted"></i>Complaint Description
                    </h6>
                </div>
                <div class="card-body">
                    @if($complaint->description)
                        <div class="text-dark" style="white-space: pre-wrap; line-height: 1.7;">{{ $complaint->description }}</div>
                    @else
                        <p class="text-muted fst-italic mb-0">No description provided.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Danger Zone --}}
    <div class="row mt-4 mb-5">
        <div class="col-12">
            <div class="card border-danger border-opacity-50 shadow-sm">
                <div class="card-header bg-danger bg-opacity-10 border-bottom border-danger border-opacity-25 py-3">
                    <h6 class="mb-0 fw-semibold text-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>Danger Zone
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-start align-items-md-center flex-column flex-md-row gap-3">
                        <div class="flex-grow-1">
                            <h6 class="fw-semibold mb-1">Delete This Complaint</h6>
                            <p class="text-muted small mb-0">
                                Permanently delete ticket <strong>{{ $complaint->ticket_number }}</strong>.
                                This action <strong>cannot be undone</strong> and will remove all associated data.
                            </p>
                        </div>
                        <div class="flex-shrink-0">
                            <button type="button"
                                    class="btn btn-outline-danger"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteComplaintModal">
                                <i class="bi bi-trash3 me-1"></i>Delete Complaint
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteComplaintModal" tabindex="-1" aria-labelledby="deleteComplaintModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-semibold text-danger" id="deleteComplaintModalLabel">
                    <i class="bi bi-trash3 me-2"></i>Delete Complaint
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Are you sure you want to permanently delete ticket:</p>
                <div class="alert alert-danger py-2 mb-3">
                    <strong>{{ $complaint->ticket_number }}</strong> — {{ $complaint->subject }}
                </div>
                <p class="text-muted small mb-0">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    This action is irreversible. All data associated with this complaint will be permanently removed.
                </p>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Cancel
                </button>
                <form action="{{ route('complaints.destroy', $complaint) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash3 me-1"></i>Yes, Delete Permanently
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
