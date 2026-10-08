@extends('layouts.app')
@section('title', 'Edit Project — ' . $project->project_code)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-pencil text-warning me-2"></i>Edit Project — {{ $project->project_code }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-0"><li class="breadcrumb-item"><a href="{{ route('projects.index') }}" class="text-success text-decoration-none">Projects</a></li><li class="breadcrumb-item active">Edit</li></ol></nav>
    </div>
    <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<form method="POST" action="{{ route('projects.update', $project) }}">
    @csrf @method('PUT')
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle text-success me-2"></i>Project Details</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold text-secondary">Project Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $project->name) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                @foreach(['Planning', 'Approved', 'In Progress', 'On Hold', 'Completed', 'Cancelled'] as $st)
                                    <option value="{{ $st }}" @selected(old('status', $project->status) === $st)>{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-secondary">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-secondary">Expected End Date</label>
                            <input type="date" name="expected_end_date" class="form-control" value="{{ old('expected_end_date', $project->expected_end_date?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-secondary">Progress (%)</label>
                            <input type="number" name="progress" class="form-control" min="0" max="100" value="{{ old('progress', $project->progress ?? 0) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Description</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description', $project->description) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">Financials</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Contract Value (₹)</label>
                        <input type="number" step="0.01" name="contract_value" class="form-control" value="{{ old('contract_value', $project->contract_value) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Budget (₹)</label>
                        <input type="number" step="0.01" name="budget" class="form-control" value="{{ old('budget', $project->budget) }}">
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-warning py-2 fw-semibold text-dark">
                            <i class="bi bi-check2 me-1"></i> Update Project
                        </button>
                        <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
