@extends('layouts.app')
@section('title', 'Employees')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-person-badge-fill text-success me-2"></i>Employees & Staff</h4>
        <p class="text-muted small mb-0">
            @if(auth()->user()->canSeeAllBranches())
                Manage staff across all company branches.
            @else
                Manage staff for <span class="badge bg-success-subtle text-success border border-success-subtle">{{ auth()->user()->branchLabel() }}</span>.
            @endif
        </p>
    </div>
    <a href="{{ route('employees.create') }}" class="btn btn-success shadow-sm">
        <i class="bi bi-plus-lg me-1"></i> Add Employee
    </a>
</div>

@if(session('login_credentials'))
<div class="alert alert-success shadow-sm mb-4 border-0">
    <div class="d-flex align-items-center gap-2 mb-2">
        <i class="bi bi-key-fill fs-5 text-success"></i>
        <h6 class="mb-0 fw-bold text-success">HRM Portal Login Account Created!</h6>
    </div>
    <p class="small text-muted mb-2">Share these credentials with the employee so they can sign in to their HRM Portal:</p>
    <div class="p-3 bg-white rounded border d-inline-block small font-monospace">
        <div><strong class="text-secondary">Login ID (Employee Code):</strong> <span class="text-success fw-bold">{{ session('login_credentials.username') }}</span></div>
        <div><strong class="text-secondary">Mobile Number:</strong> <span>{{ session('login_credentials.phone') }}</span></div>
        <div><strong class="text-secondary">Password:</strong> <span class="text-danger fw-bold">{{ session('login_credentials.password') }}</span></div>
    </div>
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-crm align-middle mb-0">
            <thead>
                <tr>
                    <th>Emp Code</th>
                    <th>Name</th>
                    <th>Designation</th>
                    <th>Department</th>
                    <th>Branch</th>
                    <th>Phone</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $emp)
                <tr>
                    <td><a href="{{ route('employees.show', $emp) }}" class="fw-semibold text-success text-decoration-none font-monospace">{{ $emp->employee_code }}</a></td>
                    <td class="fw-semibold text-dark">{{ $emp->name }}</td>
                    <td>{{ $emp->designation }}</td>
                    <td><span class="badge bg-light text-dark border">{{ $emp->department }}</span></td>
                    <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $emp->branch?->name ?? '—' }}</span></td>
                    <td class="small">{{ $emp->phone }}</td>
                    <td class="small">{{ $emp->employee_type }}</td>
                    <td>
                        <span class="badge {{ $emp->status === 'active' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($emp->status) }}</span>
                    </td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-1">
                            <a href="{{ route('employees.show', $emp) }}" class="btn btn-sm btn-outline-success" title="View Profile"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('employees.edit', $emp) }}" class="btn btn-sm btn-outline-primary" title="Edit Employee"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('employees.destroy', $emp) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete employee {{ $emp->name }} ({{ $emp->employee_code }})?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Employee">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
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
