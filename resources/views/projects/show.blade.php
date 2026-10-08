@extends('layouts.app')
@section('title', 'Project — ' . $project->project_code)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-kanban-fill text-success me-2"></i>{{ $project->name }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-0"><li class="breadcrumb-item"><a href="{{ route('projects.index') }}" class="text-success text-decoration-none">Projects</a></li><li class="breadcrumb-item active">{{ $project->project_code }}</li></ol></nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('projects.edit', $project) }}" class="btn btn-primary btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
        <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle text-success me-2"></i>Project Details</h6>
                <span class="badge {{ $project->status === 'Completed' ? 'bg-success' : 'bg-primary' }}">{{ $project->status }}</span>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-sm-6"><label class="text-muted small d-block">Project Code</label><span class="fw-semibold text-success">{{ $project->project_code }}</span></div>
                    <div class="col-sm-6"><label class="text-muted small d-block">Client</label><a href="{{ route('customers.show', $project->customer) }}" class="fw-semibold text-decoration-none text-success">{{ $project->customer?->name }}</a></div>
                    <div class="col-sm-6"><label class="text-muted small d-block">Project Manager</label><span>{{ $project->projectManager?->name ?? '—' }}</span></div>
                    <div class="col-sm-6"><label class="text-muted small d-block">Start Date</label><span>{{ $project->start_date?->format('d M Y') ?? '—' }}</span></div>
                    <div class="col-sm-6"><label class="text-muted small d-block">Expected End Date</label><span>{{ $project->expected_end_date?->format('d M Y') ?? '—' }}</span></div>
                    <div class="col-12">
                        <label class="text-muted small d-block mb-1">Progress ({{ $project->progress ?? 0 }}%)</label>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" style="width: {{ $project->progress ?? 0 }}%"></div>
                        </div>
                    </div>
                    @if($project->description)
                    <div class="col-12"><label class="text-muted small d-block">Description</label><p class="bg-light p-3 rounded mb-0 small">{{ $project->description }}</p></div>
                    @endif
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-check2-square text-info me-2"></i>Tasks & Activities</h6>
                <a href="{{ route('tasks.create') }}?project_id={{ $project->id }}" class="btn btn-outline-success btn-sm"><i class="bi bi-plus me-1"></i>Add Task</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>Task</th><th>Priority</th><th>Due Date</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($project->tasks as $t)
                        <tr>
                            <td class="fw-semibold">{{ $t->title }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $t->priority }}</span></td>
                            <td class="small">{{ $t->due_date?->format('d M Y') ?? '—' }}</td>
                            <td><span class="badge bg-info text-dark">{{ $t->status }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-4 text-muted">No tasks assigned to this project yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">Financials</h6>
            </div>
            <div class="card-body p-4">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted">Contract Value</td><td class="text-end fw-semibold">₹{{ number_format($project->contract_value, 2) }}</td></tr>
                    <tr><td class="text-muted">Budget</td><td class="text-end">₹{{ number_format($project->budget, 2) }}</td></tr>
                    <tr><td class="text-muted">Actual Cost</td><td class="text-end text-danger">₹{{ number_format($project->actual_cost, 2) }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
