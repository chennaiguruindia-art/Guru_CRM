@extends('layouts.app')
@section('title', 'Task — ' . $task->title)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-check2-square text-success me-2"></i>{{ $task->title }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-0"><li class="breadcrumb-item"><a href="{{ route('tasks.index') }}" class="text-success text-decoration-none">Tasks</a></li><li class="breadcrumb-item active">Task #{{ $task->id }}</li></ol></nav>
    </div>
    <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle text-success me-2"></i>Task Details</h6>
        <span class="badge {{ $task->status === 'Completed' ? 'bg-success' : 'bg-primary' }}">{{ $task->status }}</span>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-sm-6"><label class="text-muted small d-block">Project</label><span>{{ $task->project?->name ?? '—' }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Assigned To</label><span>{{ $task->assignedTo?->name ?? 'Unassigned' }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Priority</label><span class="badge bg-light text-dark border">{{ $task->priority }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Due Date</label><span>{{ $task->due_date?->format('d M Y') ?? '—' }}</span></div>
            @if($task->description)
            <div class="col-12"><label class="text-muted small d-block">Description</label><div class="bg-light p-3 rounded small">{!! nl2br(e($task->description)) !!}</div></div>
            @endif
        </div>
    </div>
</div>
@endsection
