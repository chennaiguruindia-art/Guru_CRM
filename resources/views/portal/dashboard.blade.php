@extends('layouts.portal')
@section('title', 'Employee Dashboard')

@section('content')
<!-- Header Banner -->
<div class="card border-0 shadow-sm mb-4 bg-white overflow-hidden">
    <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                Welcome, {{ $employee ? $employee->name : auth()->user()->name }}! 👋
            </h4>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                @if($employee)
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                        <i class="bi bi-person-badge me-1"></i> ID: {{ $employee->employee_code }}
                    </span>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                        <i class="bi bi-geo-alt me-1"></i> {{ $employee->branch?->name ?? 'Headquarters' }}
                    </span>
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">
                        <i class="bi bi-briefcase me-1"></i> {{ $employee->designation }}
                    </span>
                @else
                    <span class="badge bg-warning-subtle text-warning border px-2 py-1">
                        Profile Pending Link
                    </span>
                @endif
            </div>
        </div>

        <div class="text-end">
            <div class="text-muted small">{{ now()->format('l, d F Y') }}</div>
            <div id="liveClock" class="fs-4 fw-bold text-success font-monospace">--:--:--</div>
        </div>
    </div>
</div>

@if(auth()->user()->hasAnyRole(['fieldmarketer', 'admin', 'super-admin']))
    <!-- Sales / Field Marketer Quick CRM Access Banner -->
    <div class="card border-0 shadow-sm mb-4 p-3 d-flex flex-row justify-content-between align-items-center flex-wrap gap-2 rounded-3 text-white" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
        <div>
            <h6 class="fw-bold mb-1"><i class="bi bi-briefcase-fill me-2"></i>Sales & Field Marketing Desk</h6>
            <p class="small text-white-50 mb-0">You have access to log client visits, cold calls, and leads in the CRM.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-light fw-bold text-primary shadow-sm px-4 py-2">
            <span>Move to CRM</span> <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
@endif

<div class="row g-4">
    <!-- Punch In / Punch Out Widget -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold text-dark">
                    <i class="bi bi-clock-history text-success me-2"></i>Daily Attendance Punch
                </h6>
                <span class="badge {{ $todayAttendance ? 'bg-success' : 'bg-secondary' }}">
                    {{ $todayAttendance ? $todayAttendance->status : 'Not Punched Today' }}
                </span>
            </div>

            <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                @if(!$employee)
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        No employee profile is linked to your user account. Please contact your Branch Admin.
                    </div>
                @elseif(!$todayAttendance || !$todayAttendance->check_in)
                    {{-- Not punched in yet --}}
                    <div class="py-3">
                        <div class="mb-3">
                            <i class="bi bi-box-arrow-in-right text-success" style="font-size: 3.5rem;"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">Ready to start work?</h5>
                        <p class="text-muted small mb-4">Click below to record your punch-in timestamp for today.</p>

                        <form method="POST" action="{{ route('portal.punch_in') }}">
                            @csrf
                            <button type="submit" class="btn punch-btn-in btn-lg px-5 py-3 rounded-pill fw-semibold shadow">
                                <i class="bi bi-fingerprint me-2"></i>Punch In Now
                            </button>
                        </form>
                    </div>
                @elseif($todayAttendance->check_in && !$todayAttendance->check_out)
                    {{-- Punched in, waiting for punch out --}}
                    <div class="py-3">
                        <div class="mb-3">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 3.5rem;"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">You are currently working</h5>
                        <p class="text-muted small mb-3">
                            Checked in at: <strong class="text-success">{{ \Carbon\Carbon::parse($todayAttendance->check_in)->format('h:i A') }}</strong>
                        </p>

                        <form method="POST" action="{{ route('portal.punch_out') }}">
                            @csrf
                            <button type="submit" class="btn punch-btn-out btn-lg px-5 py-3 rounded-pill fw-semibold shadow">
                                <i class="bi bi-box-arrow-right me-2"></i>Punch Out (End Day)
                            </button>
                        </form>
                    </div>
                @else
                    {{-- Both In and Out completed --}}
                    <div class="py-3">
                        <div class="mb-3">
                            <i class="bi bi-shield-check text-success" style="font-size: 3.5rem;"></i>
                        </div>
                        <h5 class="fw-bold text-success mb-2">Today's Attendance Completed!</h5>
                        <div class="p-3 bg-light rounded-3 d-inline-block border text-start small">
                            <div class="mb-1"><i class="bi bi-box-arrow-in-right text-success me-2"></i><strong>Punch In:</strong> {{ \Carbon\Carbon::parse($todayAttendance->check_in)->format('h:i A') }}</div>
                            <div><i class="bi bi-box-arrow-left text-danger me-2"></i><strong>Punch Out:</strong> {{ \Carbon\Carbon::parse($todayAttendance->check_out)->format('h:i A') }}</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Nature of Work & Employee Profile -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold text-dark">
                    <i class="bi bi-person-lines-fill text-success me-2"></i>Employee Profile & Job Nature
                </h6>
            </div>
            <div class="card-body p-4">
                @if($employee)
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-muted small">Employee ID</div>
                            <div class="fw-bold text-success font-monospace fs-5">{{ $employee->employee_code }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">Branch</div>
                            <div class="fw-semibold text-dark">{{ $employee->branch?->name ?? 'Headquarters' }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">Nature of Work / Role</div>
                            <div class="fw-semibold text-dark">{{ $employee->designation }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">Department</div>
                            <div class="fw-semibold text-dark">{{ $employee->department }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">Employment Type</div>
                            <div class="fw-semibold text-dark">{{ $employee->employee_type }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">Date of Joining</div>
                            <div class="fw-semibold text-dark">{{ $employee->joining_date ? $employee->joining_date->format('d M Y') : 'N/A' }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">Mobile Number</div>
                            <div class="fw-semibold text-dark">{{ $employee->phone }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">Email Address</div>
                            <div class="fw-semibold text-dark">{{ $employee->email ?? 'Not specified' }}</div>
                        </div>
                    </div>
                @else
                    <p class="text-muted">No linked employee details found.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Monthly Attendance Stats -->
<div class="row g-3 my-3">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm p-3 bg-white border-start border-success border-4">
            <div class="text-muted small fw-semibold">Days Present</div>
            <h3 class="fw-bold mb-0 text-success">{{ $presentDays }}</h3>
            <span class="text-muted small">{{ now()->format('F Y') }}</span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm p-3 bg-white border-start border-warning border-4">
            <div class="text-muted small fw-semibold">Half-Days</div>
            <h3 class="fw-bold mb-0 text-warning">{{ $halfDays }}</h3>
            <span class="text-muted small">{{ now()->format('F Y') }}</span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm p-3 bg-white border-start border-info border-4">
            <div class="text-muted small fw-semibold">Leaves</div>
            <h3 class="fw-bold mb-0 text-info">{{ $leaveDays }}</h3>
            <span class="text-muted small">{{ now()->format('F Y') }}</span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm p-3 bg-white border-start border-danger border-4">
            <div class="text-muted small fw-semibold">Absents</div>
            <h3 class="fw-bold mb-0 text-danger">{{ $absentDays }}</h3>
            <span class="text-muted small">{{ now()->format('F Y') }}</span>
        </div>
    </div>
</div>

<!-- Recent Attendance Records -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold text-dark">
            <i class="bi bi-calendar3 text-success me-2"></i>This Month's Attendance History
        </h6>
        <a href="{{ route('portal.attendance') }}" class="btn btn-sm btn-outline-success">
            View Full Calendar <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Day</th>
                    <th>Punch In</th>
                    <th>Punch Out</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($monthAttendances->take(10) as $row)
                    <tr>
                        <td class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($row->date)->format('d M Y') }}</td>
                        <td class="text-muted">{{ \Carbon\Carbon::parse($row->date)->format('l') }}</td>
                        <td>
                            @if($row->check_in)
                                <span class="badge bg-light text-dark border">
                                    <i class="bi bi-clock me-1 text-success"></i>{{ \Carbon\Carbon::parse($row->check_in)->format('h:i A') }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($row->check_out)
                                <span class="badge bg-light text-dark border">
                                    <i class="bi bi-clock me-1 text-danger"></i>{{ \Carbon\Carbon::parse($row->check_out)->format('h:i A') }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($row->status === 'Present')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Present</span>
                            @elseif($row->status === 'Half-Day')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Half-Day</span>
                            @elseif($row->status === 'Leave')
                                <span class="badge bg-info-subtle text-info border border-info-subtle">Leave</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Absent</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            No attendance records found for this month yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Live Digital Clock
    function updateClock() {
        const now = new Date();
        const timeStr = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        const clockEl = document.getElementById('liveClock');
        if (clockEl) {
            clockEl.textContent = timeStr;
        }
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>
@endpush
