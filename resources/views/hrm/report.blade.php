@extends('layouts.app')
@section('title', 'HRM Monthly Attendance Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark">
            <i class="bi bi-file-earmark-bar-graph-fill text-success me-2"></i>Monthly Attendance Report
        </h4>
        <p class="text-muted small mb-0">
            Attendance summary report for <span class="badge bg-success-subtle text-success border border-success-subtle">{{ auth()->user()->branchLabel() }}</span> &middot; 
            <strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->format('F Y') }}</strong>
        </p>
    </div>

    <div class="d-flex align-items-center gap-2">
        <form method="GET" action="{{ route('hrm.report') }}" class="d-flex align-items-center gap-2">
            @if(auth()->user()->canSeeAllBranches() && $branches->isNotEmpty())
                <select name="branch_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Branches</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            @endif
            <input type="month" name="month" class="form-control form-control-sm" value="{{ $selectedMonth }}" onchange="this.form.submit()">
        </form>

        <a href="{{ route('hrm.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to HRM
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Emp Code</th>
                    <th>Staff Name</th>
                    <th>Designation & Dept</th>
                    <th>Branch</th>
                    <th class="text-center text-success">Present Days</th>
                    <th class="text-center text-warning">Half-Days</th>
                    <th class="text-center text-danger">Absent Days</th>
                    <th class="text-center text-info">Leaves</th>
                    <th class="text-end">Attendance Rate</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $emp)
                    @php
                        $records = $attendances->get($emp->id) ?? collect();
                        $present = $records->where('status', 'Present')->count();
                        $half = $records->where('status', 'Half-Day')->count();
                        $absent = $records->where('status', 'Absent')->count();
                        $leave = $records->where('status', 'Leave')->count();
                        $totalMarked = $present + $half + $absent + $leave;
                        $rate = $totalMarked > 0 ? round((($present + ($half * 0.5)) / $totalMarked) * 100) : 0;
                    @endphp
                    <tr>
                        <td>
                            <a href="{{ route('employees.show', $emp) }}" class="fw-semibold text-success text-decoration-none font-monospace">
                                {{ $emp->employee_code }}
                            </a>
                        </td>
                        <td class="fw-bold text-dark">{{ $emp->name }}</td>
                        <td>
                            <div>{{ $emp->designation }}</div>
                            <span class="badge bg-light text-dark border">{{ $emp->department }}</span>
                        </td>
                        <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $emp->branch?->name }}</span></td>
                        <td class="text-center fw-bold text-success">{{ $present }}</td>
                        <td class="text-center fw-bold text-warning">{{ $half }}</td>
                        <td class="text-center fw-bold text-danger">{{ $absent }}</td>
                        <td class="text-center fw-bold text-info">{{ $leave }}</td>
                        <td class="text-end">
                            <span class="badge {{ $rate >= 80 ? 'bg-success' : ($rate >= 50 ? 'bg-warning' : 'bg-danger') }} px-2 py-1">
                                {{ $rate }}%
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            No active staff found for this period.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
