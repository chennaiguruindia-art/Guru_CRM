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
                <select name="branch_id" class="form-select form-select-sm fw-semibold" onchange="this.form.submit()">
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ $selectedBranchId == $b->id ? 'selected' : '' }}>🏢 {{ $b->name }}</option>
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

        <a href="{{ route('hrm.monthly_attendance', array_filter(['month' => $selectedMonth, 'branch_id' => $selectedBranchId, 'search' => request('search'), 'export' => 'excel'])) }}"
           class="btn btn-primary btn-sm shadow-sm">
            <i class="bi bi-file-earmark-excel-fill me-1"></i> Download Excel
        </a>

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
                    <th style="width: 75px;">User ID</th>
                    <th>Employee</th>
                    <th>Emp Code</th>
                    <th class="text-center text-success">Present</th>
                    <th class="text-center text-danger">Absent / Leave Dates</th>
                    <th class="text-center text-warning">Permissions (Dates)</th>
                    <th class="text-center text-primary">Monthly Work Hrs</th>
                    <th>Branch</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $row)
                    @php
                        $det = $detailsByEmployee[$row->employee_id] ?? [
                            'monthly_work_hours' => 0,
                            'leave_count' => 0,
                            'half_day_count' => 0,
                            'leave_dates' => [],
                            'absent_dates' => [],
                            'half_day_dates' => [],
                            'permission_dates' => [],
                            'days' => [],
                        ];
                    @endphp
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
                        <td class="text-center">
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fs-6 fw-bold">
                                {{ fmod($row->total_present, 1) == 0 ? (int)$row->total_present : $row->total_present }}
                            </span>
                            @if($det['half_day_count'] > 0)
                                <div class="small text-warning-emphasis mt-1" style="font-size: 0.72rem;">
                                    Half-Day: {{ implode(', ', $det['half_day_dates']) }}
                                </div>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill fs-6 fw-bold">
                                {{ fmod($row->total_absent, 1) == 0 ? (int)$row->total_absent : $row->total_absent }}
                            </span>
                            @if(!empty($det['leave_dates']))
                                <div class="small text-info-emphasis mt-1" style="font-size: 0.72rem;">
                                    <strong>Leave:</strong> {{ implode(', ', $det['leave_dates']) }}
                                </div>
                            @endif
                            @if(!empty($det['absent_dates']))
                                <div class="small text-danger mt-1" style="font-size: 0.72rem; max-width: 200px; margin: 0 auto;">
                                    <strong>Absent:</strong> {{ implode(', ', $det['absent_dates']) }}
                                </div>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2 rounded-pill fs-6 fw-bold">
                                {{ $row->no_of_permission }}
                            </span>
                            @if(!empty($det['permission_dates']))
                                <div class="small text-dark mt-1" style="font-size: 0.72rem;">
                                    {{ implode(', ', $det['permission_dates']) }}
                                </div>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="fw-bold text-primary font-monospace fs-6">
                                {{ number_format($det['monthly_work_hours'], 2) }} hrs
                            </div>
                            <button type="button"
                                    class="btn btn-outline-primary btn-xs rounded-pill px-2 py-0 mt-1 view-daily-hrs-btn"
                                    style="font-size: 0.72rem;"
                                    data-name="{{ $row->user_name }}"
                                    data-code="{{ $row->employee_code }}"
                                    data-total-hrs="{{ number_format($det['monthly_work_hours'], 2) }}"
                                    data-days='@json(array_values($det["days"]))'>
                                <i class="bi bi-clock-history me-1"></i>Daily Hrs
                            </button>
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

<!-- Daily Work Hours Breakdown Modal -->
<div class="modal fade" id="dailyHoursModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-bottom bg-light">
                <div>
                    <h6 class="modal-title fw-bold text-dark mb-0" id="dailyModalTitle">Daily Work Hours</h6>
                    <small class="text-muted" id="dailyModalSubtitle"></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr class="small text-uppercase text-muted">
                                <th class="ps-3">Date</th>
                                <th>Day</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th class="text-end">Daily Work Hrs</th>
                                <th class="text-center">Status</th>
                                <th class="pe-3 text-center">Permission</th>
                            </tr>
                        </thead>
                        <tbody id="dailyHoursModalBody"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light border-top d-flex justify-content-between">
                <span class="fw-bold text-primary font-monospace" id="dailyModalTotalHrs"></span>
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('dailyHoursModal');
    if (!modalEl) return;
    const modal = new bootstrap.Modal(modalEl);

    document.querySelectorAll('.view-daily-hrs-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const name = this.dataset.name;
            const code = this.dataset.code;
            const totalHrs = this.dataset.totalHrs;
            const days = JSON.parse(this.dataset.days || '[]');

            document.getElementById('dailyModalTitle').textContent = `${name} (${code}) — Daily Work Hours`;
            document.getElementById('dailyModalSubtitle').textContent = `Month: {{ $monthCarbon->format('F Y') }}`;
            document.getElementById('dailyModalTotalHrs').textContent = `Monthly Total Work Hours: ${totalHrs} hrs`;

            const tbody = document.getElementById('dailyHoursModalBody');
            tbody.innerHTML = days.map(d => {
                let statusBadge = `<span class="badge bg-secondary-subtle text-secondary">${d.status}</span>`;
                if (d.status === 'Present') statusBadge = '<span class="badge bg-success-subtle text-success">Present</span>';
                else if (d.status === 'Half-Day') statusBadge = '<span class="badge bg-warning-subtle text-warning-emphasis">Half-Day</span>';
                else if (d.status === 'Leave') statusBadge = '<span class="badge bg-info-subtle text-info-emphasis">Leave</span>';
                else if (d.status === 'Absent') statusBadge = '<span class="badge bg-danger-subtle text-danger">Absent</span>';
                else if (d.is_sunday) statusBadge = '<span class="badge bg-primary-subtle text-primary">Sunday (Paid)</span>';

                const permBadge = d.has_permission
                    ? '<span class="badge bg-warning text-dark">Yes</span>'
                    : '<span class="text-muted">—</span>';

                const autoTag = d.is_auto_checkout
                    ? ' <span class="badge bg-warning-subtle text-warning-emphasis" style="font-size:0.65rem;">Auto</span>'
                    : '';

                return `
                    <tr class="${d.is_sunday ? 'table-light' : ''}">
                        <td class="ps-3 fw-semibold">${d.date}</td>
                        <td class="text-muted small">${d.day_name}</td>
                        <td class="font-monospace small">${d.check_in}</td>
                        <td class="font-monospace small">${d.check_out}${autoTag}</td>
                        <td class="text-end font-monospace fw-bold text-primary">${Number(d.worked_hours).toFixed(2)} hrs</td>
                        <td class="text-center">${statusBadge}</td>
                        <td class="pe-3 text-center">${permBadge}</td>
                    </tr>
                `;
            }).join('');

            modal.show();
        });
    });
});
</script>
@endpush
@endsection
