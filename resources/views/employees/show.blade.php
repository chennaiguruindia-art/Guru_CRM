@extends('layouts.app')
@section('title', 'Employee — ' . $employee->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-person-badge-fill text-success me-2"></i>{{ $employee->name }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-0"><li class="breadcrumb-item"><a href="{{ route('employees.index') }}" class="text-success text-decoration-none">Employees</a></li><li class="breadcrumb-item active">{{ $employee->employee_code }}</li></ol></nav>
    </div>
    <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle text-success me-2"></i>Employee Profile</h6>
        <span class="badge {{ $employee->status === 'active' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($employee->status) }}</span>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-sm-6"><label class="text-muted small d-block">Employee Code</label><span class="fw-semibold text-success">{{ $employee->employee_code }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Designation</label><span class="fw-semibold">{{ $employee->designation }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Department</label><span>{{ $employee->department }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Phone</label><span>{{ $employee->phone }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Email</label><span>{{ $employee->email ?: '—' }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Type</label><span>{{ $employee->employee_type }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Joining Date</label><span>{{ $employee->joining_date?->format('d M Y') ?? '—' }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Monthly Salary</label><span class="fw-semibold text-dark">₹{{ number_format($employee->salary, 2) }}</span></div>
            @if($employee->address)
            <div class="col-12"><label class="text-muted small d-block">Address</label><p class="bg-light p-3 rounded mb-0 small">{{ $employee->address }}</p></div>
            @endif
        </div>
    </div>
</div>
@endsection
