@extends('layouts.app')
@section('title', 'Create Project')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-kanban-fill text-success me-2"></i>New Project</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-0"><li class="breadcrumb-item"><a href="{{ route('projects.index') }}" class="text-success text-decoration-none">Projects</a></li><li class="breadcrumb-item active">Create</li></ol></nav>
    </div>
    <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<form method="POST" action="{{ route('projects.store') }}">
    @csrf
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle text-success me-2"></i>Project Information</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold text-secondary">Project Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Villa Landscaping & Irrigation" value="{{ old('name') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                @foreach(['Planning', 'Approved', 'In Progress', 'On Hold', 'Completed', 'Cancelled'] as $st)
                                    <option value="{{ $st }}" @selected(old('status', 'Planning') === $st)>{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Client <span class="text-danger">*</span></label>
                            <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                                <option value="">— Select Client —</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}" @selected(old('customer_id', request('customer_id')) == $c->id)>{{ $c->name }} ({{ $c->customer_code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Project Manager</label>
                            <select name="project_manager_id" class="form-select">
                                <option value="">— Select Manager —</option>
                                @foreach($managers as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-secondary">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ old('start_date', date('Y-m-d')) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-secondary">Expected End Date</label>
                            <input type="date" name="expected_end_date" class="form-control" value="{{ old('expected_end_date') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Project deliverables, specifications…">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">Financials & Progress</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Contract Value (₹)</label>
                        <input type="number" step="0.01" name="contract_value" class="form-control" value="{{ old('contract_value', 0) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Budget (₹)</label>
                        <input type="number" step="0.01" name="budget" class="form-control" value="{{ old('budget', 0) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Progress (%)</label>
                        <input type="number" name="progress" class="form-control" min="0" max="100" value="{{ old('progress', 0) }}">
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success py-2 fw-semibold">
                            <i class="bi bi-check2-circle me-1"></i> Save Project
                        </button>
                        <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
