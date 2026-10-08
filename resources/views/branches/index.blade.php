@extends('layouts.app')
@section('title', 'Branches')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-building text-success me-2"></i>Branches</h4>
        <p class="text-muted small mb-0">Offices and locations your people are assigned to.</p>
    </div>
    @can('branches.create')
        <a href="{{ route('branches.create') }}" class="btn btn-success shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Add Branch
        </a>
    @endcan
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('branches.index') }}" class="d-flex gap-2 flex-wrap">
            <div class="flex-grow-1" style="min-width: 200px;">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Search name, code or location…"
                       value="{{ request('search') }}">
            </div>
            <div style="min-width: 140px;">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn btn-sm btn-outline-success">
                <i class="bi bi-search me-1"></i> Filter
            </button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('branches.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            @endif
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-crm align-middle mb-0">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Branch Name</th>
                    <th>Location</th>
                    <th>Phone</th>
                    <th class="text-end">People</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($branches as $branch)
                <tr>
                    <td class="fw-semibold text-success">{{ $branch->code }}</td>
                    <td class="fw-semibold">{{ $branch->name }}</td>
                    <td class="small">{{ $branch->location ?: '—' }}</td>
                    <td class="small">{{ $branch->phone ?: '—' }}</td>
                    <td class="text-end">
                        <span class="badge bg-soft-primary text-primary">{{ $branch->employees_count }}</span>
                    </td>
                    <td>
                        <span class="badge {{ $branch->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                            {{ ucfirst($branch->status) }}
                        </span>
                    </td>
                    <td class="text-end">
                        @can('branches.edit')
                            <a href="{{ route('branches.edit', $branch) }}" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-pencil"></i>
                            </a>
                        @endcan
                        @can('branches.delete')
                            <form method="POST" action="{{ route('branches.destroy', $branch) }}"
                                  class="d-inline" onsubmit="return confirm('Delete branch {{ $branch->code }}? People assigned to it will be unassigned, not deleted.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="bi bi-building fs-1 text-muted d-block mb-2"></i>
                        <span class="text-muted">
                            No branches yet.
                            @can('branches.create')
                                <a href="{{ route('branches.create') }}">Create the first branch</a>.
                            @endcan
                        </span>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($branches->hasPages())
    <div class="card-footer bg-white py-3">{{ $branches->withQueryString()->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
