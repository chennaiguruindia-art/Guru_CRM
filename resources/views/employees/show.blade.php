@extends('layouts.app')
@section('title', 'Employee — ' . $employee->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-person-badge-fill text-success me-2"></i>{{ $employee->name }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-0"><li class="breadcrumb-item"><a href="{{ route('employees.index') }}" class="text-success text-decoration-none">Employees</a></li><li class="breadcrumb-item active">{{ $employee->employee_code }}</li></ol></nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('employees.edit', $employee) }}" class="btn btn-success btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
        <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle text-success me-2"></i>Employee Profile</h6>
        <span class="badge {{ $employee->status === 'active' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($employee->status) }}</span>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-sm-6"><label class="text-muted small d-block">Employee Code (Portal Login ID)</label><span class="fw-semibold text-success font-monospace fs-6">{{ $employee->employee_code }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Branch</label><span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">{{ $employee->branch?->name ?? 'No Branch' }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Designation / Role</label><span class="fw-semibold text-dark">{{ $employee->designation }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Phone</label><span class="fw-semibold">{{ $employee->phone }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Email</label><span>{{ $employee->email ?: '—' }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Date of Birth (DOB)</label><span>{{ $employee->dob?->format('d M Y') ?? '—' }}</span></div>
            <div class="col-sm-6">
                <label class="text-muted small d-block">Blood Group</label>
                @if($employee->blood_group)
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="bi bi-droplet-fill me-1"></i>{{ $employee->blood_group }}</span>
                @else
                    <span>—</span>
                @endif
            </div>
            <div class="col-sm-6"><label class="text-muted small d-block">Emergency Contact</label><span class="fw-semibold text-dark">{{ $employee->emergency_contact ?: '—' }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Employment Type</label><span>{{ $employee->employee_type }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Joining Date</label><span>{{ $employee->joining_date?->format('d M Y') ?? '—' }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Monthly Salary</label><span class="fw-semibold text-dark">₹{{ number_format($employee->salary, 2) }}</span></div>
            <div class="col-sm-6">
                <label class="text-muted small d-block">System Login Account</label>
                @if($employee->user)
                    <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-check-circle me-1"></i>Active (Username: {{ $employee->user->username ?? $employee->employee_code }})</span>
                @else
                    <span class="badge bg-secondary-subtle text-secondary border">No system login</span>
                @endif
            </div>
            @if($employee->address)
            <div class="col-12"><label class="text-muted small d-block">Address</label><p class="bg-light p-3 rounded mb-0 small">{{ $employee->address }}</p></div>
            @endif
        </div>
    </div>
</div>

{{-- Recent Attendance History --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="mb-0 fw-semibold text-dark"><i class="bi bi-clock-history text-success me-2"></i>Attendance Log</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employee->attendances->sortByDesc('date')->take(15) as $att)
                    <tr>
                        <td class="fw-semibold">{{ \Carbon\Carbon::parse($att->date)->format('d M Y (l)') }}</td>
                        <td>
                            @if($att->status === 'Present')
                                <span class="badge bg-success-subtle text-success">Present</span>
                            @elseif($att->status === 'Half-Day')
                                <span class="badge bg-warning-subtle text-warning">Half-Day</span>
                            @elseif($att->status === 'Leave')
                                <span class="badge bg-info-subtle text-info">Leave</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger">Absent</span>
                            @endif
                        </td>
                        <td>{{ $att->check_in ? \Carbon\Carbon::parse($att->check_in)->format('h:i A') : '—' }}</td>
                        <td>{{ $att->check_out ? \Carbon\Carbon::parse($att->check_out)->format('h:i A') : '—' }}</td>
                        <td class="text-muted small">{{ $att->notes ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No attendance records found yet for this employee.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
