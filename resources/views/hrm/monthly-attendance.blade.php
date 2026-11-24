@extends('layouts.app')
@section('title', 'Employee Monthly Attendance Data')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1 text-dark">
            <i class="bi bi-calendar3-range-fill text-success me-2"></i>Employee Monthly Attendance Data
        </h4>
        <p class="text-muted small mb-0">
            Attendance data table for <span class="badge bg-success-subtle text-success border border-success-subtle">{{ auth()->user()->branchLabel() }}</span> &middot; 
            <strong>{{ $monthCarbon->format('F Y') }}</strong>
        </p>
    </div>

    <div class="d-flex align-items-center gap-2 flex-wrap">
        <form method="GET" action="{{ route('hrm.monthly_attendance') }}" class="d-flex align-items-center gap-2 flex-wrap">
            @if($canSeeAll && $branches->isNotEmpty())
                <select name="branch_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Branches</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ $selectedBranchId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            @endif

            <input type="month" name="month" class="form-control form-control-sm" value="{{ $selectedMonth }}" onchange="this.form.submit()">

            <div class="input-group input-group-sm" style="width: 200px;">
                <input type="text" name="search" class="form-control" placeholder="Search staff..." value="{{ request('search') }}">
                <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
            </div>
        </form>

        <form method="POST" action="{{ route('hrm.monthly_attendance.sync') }}" class="d-inline">
            @csrf
            <input type="hidden" name="month" value="{{ $selectedMonth }}">
            @if($selectedBranchId)
                <input type="hidden" name="branch_id" value="{{ $selectedBranchId }}">
            @endif
            <button type="submit" class="btn btn-success btn-sm">
                <i class="bi bi-arrow-clockwise me-1"></i> Sync / Refresh Table
            </button>
        </form>

        <a href="{{ route('hrm.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to HRM
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <span class="fw-bold text-dark">
            <i class="bi bi-table me-2 text-primary"></i>Monthly Attendance Records ({{ $records->count() }})
        </span>
        <span class="badge bg-light text-secondary border">Month: {{ $monthCarbon->format('M Y') }}</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="text-uppercase small fw-bold">
                    <th style="width: 80px;">User ID</th>
                    <th>User Name</th>
                    <th>Employee ID / Code</th>
                    <th class="text-center">Month</th>
                    <th class="text-center text-success">Total of Present</th>
                    <th class="text-center text-danger">Total of Absent</th>
                    <th class="text-center text-warning">No. of Permission</th>
                    <th>Branch</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $row)
                    <tr>
                        <td class="font-monospace fw-semibold text-secondary">
                            #{{ $row->user_id ?? '—' }}
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $row->user_name }}</div>
                            <small class="text-muted">{{ $row->employee?->designation ?? 'Staff' }}</small>
                        </td>
                        <td>
                            <a href="{{ route('employees.show', $row->employee_id) }}" class="fw-bold text-success font-monospace text-decoration-none">
                                {{ $row->employee_code }}
                            </a>
                        </td>
                        <td class="text-center font-monospace fw-semibold text-secondary">
                            {{ $row->month }}
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fs-6 fw-bold">
                                {{ fmod($row->total_present, 1) == 0 ? (int)$row->total_present : $row->total_present }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill fs-6 fw-bold">
                                {{ fmod($row->total_absent, 1) == 0 ? (int)$row->total_absent : $row->total_absent }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2 rounded-pill fs-6 fw-bold">
                                {{ $row->no_of_permission }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                <i class="bi bi-geo-alt-fill me-1"></i>{{ $row->branch ?: '—' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                            No monthly attendance records found for <strong>{{ $monthCarbon->format('F Y') }}</strong>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
