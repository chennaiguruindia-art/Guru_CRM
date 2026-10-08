@extends('layouts.app')
@section('title', 'Tasks')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-check2-square text-success me-2"></i>Task Management</h4>
        <p class="text-muted small mb-0">Operational assignments, site activities, delivery, planting and supervision tasks.</p>
    </div>
    <a href="{{ route('tasks.create') }}" class="btn btn-success shadow-sm">
        <i class="bi bi-plus-lg me-1"></i> New Task
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-crm align-middle mb-0">
            <thead>
                <tr>
                    <th>Task Title</th>
                    <th>Project</th>
                    <th>Assigned To</th>
                    <th>Priority</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tasks as $task)
                <tr>
                    <td class="fw-semibold">{{ $task->title }}</td>
                    <td class="small">{{ $task->project?->name ?? '—' }}</td>
                    <td class="small">{{ $task->assignedTo?->name ?? 'Unassigned' }}</td>
                    <td>
                        <span class="badge {{ $task->priority === 'Urgent' ? 'bg-danger' : ($task->priority === 'High' ? 'bg-warning text-dark' : 'bg-light text-dark border') }}">{{ $task->priority }}</span>
                    </td>
                    <td class="small">{{ $task->due_date?->format('d M Y') ?? '—' }}</td>
                    <td>
                        <span class="badge {{ $task->status === 'Completed' ? 'bg-success' : 'bg-primary' }}">{{ $task->status }}</span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('tasks.show', $task) }}" class="btn btn-sm btn-outline-success"><i class="bi bi-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="bi bi-check2-square fs-1 text-muted d-block mb-2"></i>
                        <span class="text-muted">No tasks found. <a href="{{ route('tasks.create') }}">Create a task</a>.</span>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($tasks->hasPages())
    <div class="card-footer bg-white py-3">{{ $tasks->withQueryString()->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
