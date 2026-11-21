@extends('layouts.portal')
@section('title', 'Employee Dashboard')

@section('content')
<!-- Header Welcome Banner -->
<div class="card border-0 shadow-sm mb-4 bg-white overflow-hidden">
    <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                Welcome back, {{ $employee ? $employee->name : auth()->user()->name }}! 👋
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
                    <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">
                        <i class="bi bi-building me-1"></i> {{ $employee->department ?? 'General' }}
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

<!-- 4 Key Stat Cards Row -->
<div class="row g-3 mb-4">
    <!-- 1. Punch Status -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">TODAY'S STATUS</span>
                    <span class="badge {{ $todayAttendance ? ($todayAttendance->check_out ? 'bg-secondary' : 'bg-success') : 'bg-warning text-dark' }}">
                        {{ $todayAttendance ? ($todayAttendance->check_out ? 'Day Completed' : 'Punched In') : 'Not Punched' }}
                    </span>
                </div>
                <h5 class="fw-bold mb-1 text-dark">
                    @if($todayAttendance && $todayAttendance->check_in)
                        {{ \Carbon\Carbon::parse($todayAttendance->check_in)->format('h:i A') }}
                    @else
                        Pending Check-in
                    @endif
                </h5>
                <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                    <small class="text-muted">
                        @if($todayAttendance && $todayAttendance->check_out)
                            Out: {{ \Carbon\Carbon::parse($todayAttendance->check_out)->format('h:i A') }}
                        @else
                            {{ $todayAttendance ? 'Currently Working' : 'Tap to punch in' }}
                        @endif
                    </small>
                    <a href="{{ route('portal.punch') }}" class="text-success text-decoration-none small fw-semibold">
                        Punch Desk &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Monthly Attendance -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">THIS MONTH ({{ now()->format('M') }})</span>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                        {{ $attendancePercentage }}% Attendance
                    </span>
                </div>
                <h5 class="fw-bold mb-1 text-dark">
                    <span class="text-success">{{ $presentDays }}</span>
                    <span class="text-muted fs-6 fw-normal">Present</span>
                    @if($halfDays > 0)
                        &middot; <span class="text-warning fs-6">{{ $halfDays }} Half</span>
                    @endif
                    @if($absentDays > 0)
                        &middot; <span class="text-danger fs-6">{{ $absentDays }} Absent</span>
                    @endif
                </h5>
                <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                    <small class="text-muted">Leaves: {{ $leaveDays }}</small>
                    <a href="{{ route('portal.attendance') }}" class="text-primary text-decoration-none small fw-semibold">
                        View Log &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Current Monthly Salary & Payslip -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">NET SALARY ({{ now()->format('M') }})</span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                        <i class="bi bi-shield-check me-1"></i>Payable
                    </span>
                </div>
                <h5 class="fw-bold mb-1 text-success">
                    ₹{{ number_format($currentPayslip?->net_salary ?? ($employee?->salary ?? 0), 2) }}
                </h5>
                <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                    <small class="text-muted">Gross: ₹{{ number_format($employee?->salary ?? 0, 0) }}</small>
                    <a href="{{ route('portal.payslips') }}" class="text-success text-decoration-none small fw-semibold">
                        Get Payslip &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Profile & Family Completeness -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">PROFILE & FAMILY</span>
                    <span class="badge {{ $profileCompletion >= 80 ? 'bg-success' : 'bg-warning text-dark' }}">
                        {{ $profileCompletion }}% Complete
                    </span>
                </div>
                <div class="progress mb-2" style="height: 6px;">
                    <div class="progress-bar {{ $profileCompletion >= 80 ? 'bg-success' : 'bg-warning' }}" role="progressbar" style="width: {{ $profileCompletion }}%;"></div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                    <small class="text-muted">
                        {{ $employee?->father_name ? 'Parents details added' : 'Add parents details' }}
                    </small>
                    <a href="{{ route('portal.family') }}" class="text-info text-decoration-none small fw-semibold">
                        Manage &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Daily Attendance Punch Widget -->
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold text-dark">
                    <i class="bi bi-clock-history text-success me-2"></i>Daily Punch Desk
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
                    <div class="py-2">
                        <div class="mb-3">
                            <i class="bi bi-box-arrow-in-right text-success" style="font-size: 3.5rem;"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">Ready to start work?</h5>
                        <p class="text-muted small mb-3">Record your daily punch-in timestamp and live geolocation.</p>

                        <form method="POST" action="{{ route('portal.punch_in') }}" id="quickPunchInForm">
                            @csrf
                            <input type="hidden" name="latitude" id="quickInLat">
                            <input type="hidden" name="longitude" id="quickInLng">
                            <button type="submit" class="btn punch-btn-in btn-lg px-5 py-3 rounded-pill fw-semibold shadow">
                                <i class="bi bi-fingerprint me-2"></i>Punch In Now
                            </button>
                        </form>
                    </div>
                @elseif($todayAttendance->check_in && !$todayAttendance->check_out)
                    {{-- Punched in, waiting for punch out --}}
                    <div class="py-2">
                        <div class="mb-3">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 3.5rem;"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">You are currently working</h5>
                        <p class="text-muted small mb-3">
                            Checked in at: <strong class="text-success">{{ \Carbon\Carbon::parse($todayAttendance->check_in)->format('h:i A') }}</strong>
                        </p>

                        <form method="POST" action="{{ route('portal.punch_out') }}" id="quickPunchOutForm">
                            @csrf
                            <input type="hidden" name="latitude" id="quickOutLat">
                            <input type="hidden" name="longitude" id="quickOutLng">
                            <button type="submit" class="btn punch-btn-out btn-lg px-5 py-3 rounded-pill fw-semibold shadow">
                                <i class="bi bi-box-arrow-right me-2"></i>Punch Out (End Day)
                            </button>
                        </form>
                    </div>
                @else
                    {{-- Punched out for the day --}}
                    <div class="py-2">
                        <div class="mb-3">
                            <i class="bi bi-moon-stars-fill text-primary" style="font-size: 3.5rem;"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">Work Day Completed!</h5>
                        <p class="text-muted small mb-3">
                            In: <strong>{{ \Carbon\Carbon::parse($todayAttendance->check_in)->format('h:i A') }}</strong> &middot;
                            Out: <strong>{{ \Carbon\Carbon::parse($todayAttendance->check_out)->format('h:i A') }}</strong>
                        </p>
                        <div class="alert alert-success d-inline-block px-4 py-2 mb-0 small">
                            <i class="bi bi-check-all me-1"></i>Attendance recorded successfully for today.
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Attendance Log -->
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold text-dark">
                    <i class="bi bi-calendar2-range text-primary me-2"></i>Recent Attendance (Last 7 Days)
                </h6>
                <a href="{{ route('portal.attendance') }}" class="btn btn-sm btn-outline-secondary">
                    View Full Month
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small text-muted">
                            <th>Date</th>
                            <th>Status</th>
                            <th>In Time</th>
                            <th>Out Time</th>
                            <th>Location / Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentAttendances as $att)
                            <tr>
                                <td class="fw-semibold small">
                                    {{ \Carbon\Carbon::parse($att->date)->format('d M Y') }}
                                    <div class="text-muted" style="font-size: 0.72rem;">{{ \Carbon\Carbon::parse($att->date)->format('l') }}</div>
                                </td>
                                <td>
                                    @if($att->status === 'Present')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Present</span>
                                    @elseif($att->status === 'Half-Day')
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Half-Day</span>
                                    @elseif($att->status === 'Leave')
                                        <span class="badge bg-info-subtle text-info border border-info-subtle">Leave</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Absent</span>
                                    @endif
                                </td>
                                <td class="small">{{ $att->check_in ? \Carbon\Carbon::parse($att->check_in)->format('h:i A') : '—' }}</td>
                                <td class="small">{{ $att->check_out ? \Carbon\Carbon::parse($att->check_out)->format('h:i A') : '—' }}</td>
                                <td class="small text-muted">
                                    @if($att->check_in_address)
                                        <span title="{{ $att->check_in_address }}"><i class="bi bi-geo-alt text-success me-1"></i>{{ Str::limit($att->check_in_address, 25) }}</span>
                                    @elseif($att->notes)
                                        {{ $att->notes }}
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted small">
                                    No recent attendance records found. Use the Punch Desk to start logging!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Bottom Section: Quick Profile / Parents Snapshot & Payslip Card -->
<div class="row g-4">
    <!-- Parents & Family Snapshot Card -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold text-dark">
                    <i class="bi bi-people-fill text-info me-2"></i>Parents & Family Summary
                </h6>
                <a href="{{ route('portal.family') }}" class="btn btn-sm btn-outline-info">
                    <i class="bi bi-pencil me-1"></i>Edit Family
                </a>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="text-muted small d-block">Father's Name</label>
                        <span class="fw-semibold text-dark">{{ $employee?->father_name ?: '— Not Provided' }}</span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small d-block">Mother's Name</label>
                        <span class="fw-semibold text-dark">{{ $employee?->mother_name ?: '— Not Provided' }}</span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small d-block">Father's Contact</label>
                        <span class="text-dark">{{ $employee?->father_phone ?: '—' }}</span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small d-block">Mother's Contact</label>
                        <span class="text-dark">{{ $employee?->mother_phone ?: '—' }}</span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small d-block">Emergency Contact</label>
                        <span class="text-dark fw-semibold">
                            {{ $employee?->emergency_contact ?: '—' }}
                            @if($employee?->emergency_contact_relation)
                                <small class="text-muted">({{ $employee->emergency_contact_relation }})</small>
                            @endif
                        </span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small d-block">Blood Group</label>
                        @if($employee?->blood_group)
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                <i class="bi bi-droplet-fill me-1"></i>{{ $employee->blood_group }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>
                    @if($employee?->parent_address)
                        <div class="col-12">
                            <label class="text-muted small d-block">Permanent / Parents Address</label>
                            <p class="small bg-light p-2 rounded mb-0">{{ $employee->parent_address }}</p>
                        </div>
                    @endif
                </div>

                @if(!$employee?->father_name && !$employee?->mother_name)
                    <div class="alert alert-info mt-3 mb-0 small d-flex align-items-center gap-2">
                        <i class="bi bi-info-circle-fill fs-5"></i>
                        <div>
                            Parents details are not added yet. <a href="{{ route('portal.family') }}" class="alert-link">Click here to add parents details</a>.
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Latest Payslip Card -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold text-dark">
                    <i class="bi bi-receipt-cutoff text-success me-2"></i>Latest Payslip ({{ now()->format('F Y') }})
                </h6>
                <a href="{{ route('portal.payslips') }}" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-arrow-right me-1"></i>All Payslips
                </a>
            </div>
            <div class="card-body p-4">
                @if($currentPayslip)
                    <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded-3 mb-3">
                        <div>
                            <span class="text-muted small d-block">Estimated Net Salary</span>
                            <h3 class="fw-bold text-success mb-0">₹{{ number_format($currentPayslip->net_salary, 2) }}</h3>
                        </div>
                        <span class="badge bg-success fs-6 px-3 py-2">
                            <i class="bi bi-check-circle me-1"></i>{{ $currentPayslip->payment_status }}
                        </span>
                    </div>

                    <div class="row g-2 mb-3 small">
                        <div class="col-6 d-flex justify-content-between border-bottom pb-1">
                            <span class="text-muted">Basic Salary:</span>
                            <span class="fw-semibold">₹{{ number_format($currentPayslip->basic_salary, 2) }}</span>
                        </div>
                        <div class="col-6 d-flex justify-content-between border-bottom pb-1">
                            <span class="text-muted">HRA:</span>
                            <span class="fw-semibold">₹{{ number_format($currentPayslip->hra, 2) }}</span>
                        </div>
                        <div class="col-6 d-flex justify-content-between border-bottom pb-1">
                            <span class="text-muted">Conveyance:</span>
                            <span class="fw-semibold">₹{{ number_format($currentPayslip->conveyance_allowance, 2) }}</span>
                        </div>
                        <div class="col-6 d-flex justify-content-between border-bottom pb-1">
                            <span class="text-muted">Special Allowance:</span>
                            <span class="fw-semibold">₹{{ number_format($currentPayslip->special_allowance, 2) }}</span>
                        </div>
                        <div class="col-6 d-flex justify-content-between border-bottom pb-1 text-danger">
                            <span>PF & ESI Deductions:</span>
                            <span class="fw-semibold">-₹{{ number_format($currentPayslip->pf_deduction + $currentPayslip->esi_deduction, 2) }}</span>
                        </div>
                        <div class="col-6 d-flex justify-content-between border-bottom pb-1 text-danger">
                            <span>Professional Tax:</span>
                            <span class="fw-semibold">-₹{{ number_format($currentPayslip->professional_tax, 2) }}</span>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('portal.payslip.show', $currentPayslip->salary_month) }}" class="btn btn-success btn-sm flex-grow-1" target="_blank">
                            <i class="bi bi-printer me-1"></i>View & Print Official Payslip
                        </a>
                        <a href="{{ route('portal.payslips') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-eye me-1"></i>Details
                        </a>
                    </div>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-file-earmark-text fs-1 d-block mb-2 text-secondary"></i>
                        No payslip available yet for this month.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Live Clock Update
    function updateLiveClock() {
        const now = new Date();
        const timeStr = now.toLocaleTimeString('en-US', { hour12: true, hour: '2-digit', minute: '2-digit', second: '2-digit' });
        const clockEl = document.getElementById('liveClock');
        if (clockEl) {
            clockEl.textContent = timeStr;
        }
    }
    setInterval(updateLiveClock, 1000);
    updateLiveClock();

    // Geolocation for Quick Punch
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(pos) {
            const inLat = document.getElementById('quickInLat');
            const inLng = document.getElementById('quickInLng');
            const outLat = document.getElementById('quickOutLat');
            const outLng = document.getElementById('quickOutLng');
            if (inLat && inLng) {
                inLat.value = pos.coords.latitude;
                inLng.value = pos.coords.longitude;
            }
            if (outLat && outLng) {
                outLat.value = pos.coords.latitude;
                outLng.value = pos.coords.longitude;
            }
        }, function(err) {
            console.log('Location access not granted:', err.message);
        });
    }
</script>
@endpush
