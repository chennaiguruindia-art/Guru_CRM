@extends('layouts.app')
@section('title','Projects')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-kanban-fill text-success me-2"></i>Projects</h4>
        <p class="text-muted small mb-0">Manage horticulture projects from planning to completion.</p>
    </div>
    <a href="{{ route('projects.create') }}" class="btn btn-success shadow-sm"><i class="bi bi-plus-lg me-1"></i>New Project</a>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('projects.index') }}">
            <div class="row g-2">
                <div class="col-12 col-md-4">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search project name, client…" value="{{ request('search') }}">
                </div>
                <div class="col-6 col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        @foreach(['Planning','Approved','In Progress','On Hold','Completed','Cancelled'] as $s)
                            <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-success btn-sm flex-fill"><i class="bi bi-search me-1"></i>Filter</button>
                    <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-lg"></i></a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-crm align-middle mb-0">
            <thead>
                <tr>
                    <th>Project ID</th>
                    <th>Project Name</th>
                    <th>Client</th>
                    <th>Manager</th>
                    <th>Start Date</th>
                    <th>Progress</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($projects as $project)
                <tr>
                    <td><a href="{{ route('projects.show',$project) }}" class="fw-semibold text-success text-decoration-none">{{ $project->project_code }}</a></td>
                    <td class="fw-semibold">{{ $project->name }}</td>
                    <td class="small"><a href="{{ route('customers.show',$project->customer) }}" class="text-decoration-none text-success">{{ $project->customer?->name }}</a></td>
                    <td class="small">{{ $project->projectManager?->name ?? '—' }}</td>
                    <td class="small">{{ $project->start_date?->format('d M Y') ?? '—' }}</td>
                    <td style="min-width:120px">
                        <div class="progress" style="height:8px">
                            <div class="progress-bar bg-success" style="width:{{ $project->progress ?? 0 }}%"></div>
                        </div>
                        <small class="text-muted">{{ $project->progress ?? 0 }}%</small>
                    </td>
                    <td>
                        <span class="badge
                            @if($project->status==='Completed') bg-success
                            @elseif($project->status==='In Progress') bg-primary
                            @elseif($project->status==='On Hold') bg-warning text-dark
                            @elseif($project->status==='Cancelled') bg-danger
                            @else bg-secondary @endif">
                            {{ $project->status }}
                        </span>
                    </td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-1">
                            <a href="{{ route('projects.show',$project) }}" class="btn btn-sm btn-outline-success" title="View"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('projects.edit',$project) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-5"><i class="bi bi-kanban fs-1 text-muted d-block mb-2"></i><span class="text-muted">No projects yet. <a href="{{ route('projects.create') }}">Create your first project</a>.</span></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($projects->hasPages())
    <div class="card-footer bg-white py-3">{{ $projects->withQueryString()->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
