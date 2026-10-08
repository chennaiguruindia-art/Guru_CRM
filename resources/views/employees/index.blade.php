@extends('layouts.app')
@section('title', 'Employees')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-person-badge-fill text-success me-2"></i>Employees & Staff</h4>
        <p class="text-muted small mb-0">Manage garden supervisors, landscape designers, gardeners and staff.</p>
    </div>
    <a href="{{ route('employees.create') }}" class="btn btn-success shadow-sm">
        <i class="bi bi-plus-lg me-1"></i> Add Employee
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-crm align-middle mb-0">
            <thead>
                <tr>
                    <th>Emp Code</th>
                    <th>Name</th>
                    <th>Designation</th>
                    <th>Department</th>
                    <th>Phone</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $emp)
                <tr>
                    <td><a href="{{ route('employees.show', $emp) }}" class="fw-semibold text-success text-decoration-none">{{ $emp->employee_code }}</a></td>
                    <td class="fw-semibold">{{ $emp->name }}</td>
                    <td>{{ $emp->designation }}</td>
                    <td><span class="badge bg-light text-dark border">{{ $emp->department }}</span></td>
                    <td class="small">{{ $emp->phone }}</td>
                    <td class="small">{{ $emp->employee_type }}</td>
                    <td>
                        <span class="badge {{ $emp->status === 'active' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($emp->status) }}</span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('employees.show', $emp) }}" class="btn btn-sm btn-outline-success"><i class="bi bi-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <i class="bi bi-person-badge fs-1 text-muted d-block mb-2"></i>
                        <span class="text-muted">No employees registered. <a href="{{ route('employees.create') }}">Add an employee</a>.</span>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($employees->hasPages())
    <div class="card-footer bg-white py-3">{{ $employees->withQueryString()->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
