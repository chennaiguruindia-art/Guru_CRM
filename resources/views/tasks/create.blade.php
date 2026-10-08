@extends('layouts.app')
@section('title', 'Create Task')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-check2-square text-success me-2"></i>New Task</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-0"><li class="breadcrumb-item"><a href="{{ route('tasks.index') }}" class="text-success text-decoration-none">Tasks</a></li><li class="breadcrumb-item active">Create</li></ol></nav>
    </div>
    <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<form method="POST" action="{{ route('tasks.store') }}">
    @csrf
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle text-success me-2"></i>Task Details</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Task Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Deliver 50 Areca Palms to Prestige Site" value="{{ old('title') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Project</label>
                            <select name="project_id" class="form-select">
                                <option value="">— Select Project —</option>
                                @foreach($projects as $p)
                                    <option value="{{ $p->id }}" @selected(request('project_id') == $p->id)>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Assigned To</label>
                            <select name="assigned_to_id" class="form-select">
                                <option value="">— Unassigned —</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-secondary">Priority</label>
                            <select name="priority" class="form-select">
                                @foreach(['Low', 'Medium', 'High', 'Urgent'] as $pr)
                                    <option value="{{ $pr }}" @selected($pr === 'Medium')>{{ $pr }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-secondary">Status</label>
                            <select name="status" class="form-select">
                                @foreach(['Pending', 'In Progress', 'Completed', 'Cancelled'] as $st)
                                    <option value="{{ $st }}">{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-secondary">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ old('start_date', date('Y-m-d')) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-secondary">Due Date</label>
                            <input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Task Instructions / Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success py-2 px-4 fw-semibold">
                    <i class="bi bi-check2-circle me-1"></i> Save Task
                </button>
                <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
    </div>
</form>
@endsection
