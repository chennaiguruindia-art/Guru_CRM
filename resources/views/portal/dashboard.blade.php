@extends('layouts.portal')
@section('title', 'Employee Dashboard')

@section('content')
<!-- Header Welcome Banner (Compact & Crisp) -->
<div class="card border-0 shadow-sm mb-3 bg-white">
    <div class="card-body p-3 p-md-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                <span>Welcome back, {{ $employee ? $employee->name : auth()->user()->name }}!</span>
                <span style="font-size: 1.3rem;">👋</span>
            </h4>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                @if($employee)
                    <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-2 py-1">
                        <i class="bi bi-person-badge me-1"></i>{{ $employee->employee_code }}
                    </span>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                        <i class="bi bi-geo-alt me-1"></i>{{ $employee->branch?->name ?? 'Headquarters' }}
                    </span>
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">
                        <i class="bi bi-briefcase me-1"></i>{{ $employee->designation }}
                    </span>
                    <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">
                        <i class="bi bi-building me-1"></i>{{ $employee->department ?? 'General' }}
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
            <div id="liveClock" class="fs-4 fw-bold text-success font-monospace lh-1 mt-1">--:--:--</div>
        </div>
    </div>
</div>

<!-- 4 Key Stat Metric Cards -->
<div class="row g-3 mb-3">
    <!-- 1. Today's Status -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">TODAY'S STATUS</span>
                        <span class="badge {{ $todayAttendance ? ($todayAttendance->check_out ? 'bg-secondary' : 'bg-success') : 'bg-warning text-dark' }}">
                            {{ $todayAttendance ? ($todayAttendance->check_out ? 'Day Done' : 'Punched In') : 'Not Punched' }}
                        </span>
                    </div>
                    <div class="fs-4 fw-bold text-dark mb-1">
                        @if($todayAttendance && $todayAttendance->check_in)
                            {{ \Carbon\Carbon::parse($todayAttendance->check_in)->format('h:i A') }}
                        @else
                            <span class="text-secondary fs-5">Pending Check-in</span>
                        @endif
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-2 small">
                    <span class="text-muted text-truncate" style="max-width: 140px;">
                        @if($todayAttendance && $todayAttendance->check_out)
                            Out: {{ \Carbon\Carbon::parse($todayAttendance->check_out)->format('h:i A') }}
                        @else
                            {{ $todayAttendance ? 'Working Now' : 'Start your shift' }}
                        @endif
                    </span>
                    <a href="{{ route('portal.punch') }}" class="text-success fw-semibold text-decoration-none text-nowrap">
                        Punch Desk &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Monthly Attendance -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">THIS MONTH ({{ now()->format('M') }})</span>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                            {{ $attendancePercentage }}% Rate
                        </span>
                    </div>
                    <div class="fs-4 fw-bold text-dark mb-1">
                        <span class="text-success">{{ $presentDays }}</span> <span class="text-muted fs-6 fw-normal">Present</span>
                        @if($halfDays > 0)
                            <span class="text-muted fs-6">&middot;</span> <span class="text-warning fs-6">{{ $halfDays }} Half</span>
                        @endif
                        @if($absentDays > 0)
                            <span class="text-muted fs-6">&middot;</span> <span class="text-danger fs-6">{{ $absentDays }} Absent</span>
                        @endif
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-2 small">
                    <span class="text-muted">Leaves: {{ $leaveDays }}</span>
                    <a href="{{ route('portal.attendance') }}" class="text-primary fw-semibold text-decoration-none">
                        View Log &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Current Monthly Salary & Payslip -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">NET SALARY ({{ now()->format('M') }})</span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                            <i class="bi bi-shield-check me-1"></i>Payable
                        </span>
                    </div>
                    <div class="fs-4 fw-bold text-success mb-1">
                        ₹{{ number_format($currentPayslip?->net_salary ?? ($employee?->salary ?? 0), 2) }}
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-2 small">
                    <span class="text-muted">Gross: ₹{{ number_format($employee?->salary ?? 0, 0) }}</span>
                    <a href="{{ route('portal.payslips') }}" class="text-success fw-semibold text-decoration-none">
                        Get Payslip &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Profile & Emergency Completeness -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">PROFILE & EMERGENCY</span>
                        <span class="badge {{ $profileCompletion >= 80 ? 'bg-success' : 'bg-warning text-dark' }}">
                            {{ $profileCompletion }}% Done
                        </span>
                    </div>
                    <div class="progress mb-2" style="height: 6px;">
                        <div class="progress-bar {{ $profileCompletion >= 80 ? 'bg-success' : 'bg-warning' }}" role="progressbar" style="width: {{ $profileCompletion }}%;"></div>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-2 small">
                    <span class="text-muted text-truncate" style="max-width: 140px;">
                        {{ $employee?->emergency_contact ? 'Emergency contact set' : 'Add emergency contact' }}
                    </span>
                    <a href="{{ route('portal.profile') }}" class="text-info fw-semibold text-decoration-none">
                        Edit Profile &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
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

            <div class="card-body p-3 p-md-4 text-center d-flex flex-column justify-content-center">
                @if(!$employee)
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        No employee profile is linked to your user account. Please contact your Branch Admin.
                    </div>
                @elseif(!$todayAttendance || !$todayAttendance->check_in)
                    {{-- Not punched in yet --}}
                    @php
                        $windowCheck = $checkInWindow ?? app(\App\Services\AttendanceRuleService::class)->isCheckInAllowed();
                    @endphp
                    <div class="py-2">
                        @if($windowCheck['allowed'])
                            <div class="mb-2">
                                <i class="bi bi-box-arrow-in-right text-success" style="font-size: 3rem;"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">Ready to start work?</h5>
                            <p class="text-muted small mb-3">Record your daily punch-in timestamp and live location.</p>

                            <form method="POST" action="{{ route('portal.punch_in') }}" id="quickPunchInForm">
                                @csrf
                                <input type="hidden" name="latitude" id="quickInLat">
                                <input type="hidden" name="longitude" id="quickInLng">
                                <button type="submit" class="btn punch-btn-in btn-lg px-4 py-2 rounded-pill fw-semibold shadow-sm">
                                    <i class="bi bi-fingerprint me-2"></i>Punch In Now
                                </button>
                            </form>
                        @else
                            <div class="mb-2">
                                <i class="bi bi-lock-fill text-secondary" style="font-size: 3rem;"></i>
                            </div>
                            <h5 class="fw-bold text-secondary mb-1">Check-in Window Closed</h5>
                            <p class="text-muted small mb-3">{{ $windowCheck['message'] }}</p>
                            <button type="button" class="btn btn-secondary btn-lg px-4 py-2 rounded-pill fw-semibold shadow-sm" disabled style="opacity: 0.7; cursor: not-allowed;">
                                <i class="bi bi-lock-fill me-2"></i>Punch In Locked
                            </button>
                        @endif
                    </div>
                @elseif($todayAttendance->check_in && !$todayAttendance->check_out)
                    {{-- Punched in, waiting for punch out --}}
                    @php
                        $inTime = \Carbon\Carbon::parse($todayAttendance->check_in);
                        $elapsedMinutes = max(0, $inTime->diffInMinutes(now()));
                        $elapsedHours = floor($elapsedMinutes / 60);
                        $elapsedRemainingMinutes = $elapsedMinutes % 60;
                    @endphp
                    <div class="py-2">
                        <div class="d-inline-flex align-items-center justify-content-center bg-success-subtle text-success rounded-circle mb-3" style="width: 60px; height: 60px;">
                            <i class="bi bi-check-lg fs-1"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">You are currently working</h5>
                        <div class="text-muted small mb-2">
                            Checked in at: <strong class="text-success font-monospace fs-6">{{ $inTime->format('h:i A') }}</strong>
                        </div>
                        <div class="mb-3">
                            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill font-monospace">
                                <i class="bi bi-stopwatch me-1 text-primary"></i>Working: <strong>{{ $elapsedHours }}h {{ $elapsedRemainingMinutes }}m</strong>
                            </span>
                        </div>

                        <form method="POST" action="{{ route('portal.punch_out') }}" id="quickPunchOutForm">
                            @csrf
                            <input type="hidden" name="latitude" id="quickOutLat">
                            <input type="hidden" name="longitude" id="quickOutLng">
                            <button type="submit" class="btn punch-btn-out btn-lg px-4 py-2 rounded-pill fw-semibold shadow-sm">
                                <i class="bi bi-box-arrow-right me-2"></i>Punch Out (End Day)
                            </button>
                        </form>
                    </div>
                @else
                    {{-- Punched out for the day --}}
                    @if($todayAttendance && $todayAttendance->is_auto_checkout)
                        <div class="py-2">
                            <div class="d-inline-flex align-items-center justify-content-center bg-warning-subtle text-warning-emphasis rounded-circle mb-3" style="width: 60px; height: 60px;">
                                <i class="bi bi-clock-history fs-2"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">Auto Logged Out</h5>
                            <div class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2 text-wrap mb-2">
                                <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>
                                Employee did not logout — System auto logged out at 07:00 PM
                            </div>
                            <div class="d-flex justify-content-center gap-3 my-2 small">
                                <div class="p-2 px-3 bg-light rounded border">
                                    <span class="text-muted d-block" style="font-size: 0.72rem;">CHECK IN</span>
                                    <strong class="text-success font-monospace">{{ \Carbon\Carbon::parse($todayAttendance->check_in)->format('h:i A') }}</strong>
                                </div>
                                <div class="p-2 px-3 bg-light rounded border">
                                    <span class="text-muted d-block" style="font-size: 0.72rem;">CHECK OUT (AUTO)</span>
                                    <strong class="text-warning-emphasis font-monospace">{{ \Carbon\Carbon::parse($todayAttendance->check_out)->format('h:i A') }}</strong>
                                </div>
                            </div>
                            <div class="badge bg-light text-dark border px-3 py-1 mt-1 font-monospace">
                                Status: <strong>{{ $todayAttendance->status }}</strong> ({{ $todayAttendance->worked_hours }} hrs)
                            </div>
                        </div>
                    @else
                        <div class="py-2">
                            <div class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle mb-3" style="width: 60px; height: 60px;">
                                <i class="bi bi-moon-stars-fill fs-2"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">Work Day Completed!</h5>
                            <div class="d-flex justify-content-center gap-3 my-2 small">
                                <div class="p-2 px-3 bg-light rounded border">
                                    <span class="text-muted d-block" style="font-size: 0.72rem;">CHECK IN</span>
                                    <strong class="text-success font-monospace">{{ \Carbon\Carbon::parse($todayAttendance->check_in)->format('h:i A') }}</strong>
                                </div>
                                <div class="p-2 px-3 bg-light rounded border">
                                    <span class="text-muted d-block" style="font-size: 0.72rem;">CHECK OUT</span>
                                    <strong class="text-danger font-monospace">{{ \Carbon\Carbon::parse($todayAttendance->check_out)->format('h:i A') }}</strong>
                                </div>
                            </div>
                            <div class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 mt-2">
                                <i class="bi bi-check-all me-1"></i>Attendance Recorded for Today
                            </div>
                        </div>
                    @endif
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
                        <tr class="small text-muted" style="font-size: 0.75rem;">
                            <th>DATE</th>
                            <th>STATUS</th>
                            <th>IN TIME</th>
                            <th>OUT TIME</th>
                            <th>LOCATION / NOTES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentAttendances as $att)
                            <tr>
                                <td class="fw-semibold small">
                                    {{ \Carbon\Carbon::parse($att->date)->format('d M Y') }}
                                    <div class="text-muted" style="font-size: 0.7rem;">{{ \Carbon\Carbon::parse($att->date)->format('l') }}</div>
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
                                <td class="small font-monospace">{{ $att->check_in ? \Carbon\Carbon::parse($att->check_in)->format('h:i A') : '—' }}</td>
                                <td class="small font-monospace">
                                    @if($att->check_out)
                                        <span>{{ \Carbon\Carbon::parse($att->check_out)->format('h:i A') }}</span>
                                        @if($att->is_auto_checkout)
                                            <span class="badge bg-warning text-dark border ms-1" style="font-size: 0.65rem;" title="Employee did not logout — Auto logged out at 07:00 PM">
                                                Auto
                                            </span>
                                        @endif
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="small text-muted">
                                    @if($att->check_in_address)
                                        <span title="{{ $att->check_in_address }}"><i class="bi bi-geo-alt text-success me-1"></i>{{ Str::limit($att->check_in_address, 22) }}</span>
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

<!-- Bottom Section: Emergency & Personal Info + Latest Payslip -->
<div class="row g-3">
    <!-- Emergency & Personal Info Card -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold text-dark">
                    <i class="bi bi-shield-exclamation text-danger me-2"></i>Emergency Contact & Personal Info
                </h6>
                <a href="{{ route('portal.profile') }}" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-pencil me-1"></i>Edit Profile
                </a>
            </div>
            <div class="card-body p-3 p-md-4">
                <div class="row g-3 small">
                    <div class="col-sm-6">
                        <label class="text-muted d-block" style="font-size: 0.72rem;">EMERGENCY PHONE</label>
                        <span class="fw-bold text-danger fs-6">{{ $employee?->emergency_contact ?: '— Not Provided' }}</span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted d-block" style="font-size: 0.72rem;">RELATIONSHIP / CONTACT</label>
                        <span class="fw-semibold text-dark">{{ $employee?->emergency_contact_relation ?: '— Not Set' }}</span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted d-block" style="font-size: 0.72rem;">PRIMARY MOBILE</label>
                        <span class="text-dark fw-semibold">{{ $employee?->phone ?: '—' }}</span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted d-block" style="font-size: 0.72rem;">EMAIL ADDRESS</label>
                        <span class="text-dark">{{ $employee?->email ?: '—' }}</span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted d-block" style="font-size: 0.72rem;">BLOOD GROUP</label>
                        @if($employee?->blood_group)
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                <i class="bi bi-droplet-fill me-1"></i>{{ $employee->blood_group }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted d-block" style="font-size: 0.72rem;">DATE OF BIRTH</label>
                        <span class="text-dark">{{ $employee?->dob?->format('d M Y') ?? '—' }}</span>
                    </div>
                    @if($employee?->address)
                        <div class="col-12">
                            <label class="text-muted d-block" style="font-size: 0.72rem;">RESIDENTIAL ADDRESS</label>
                            <p class="small bg-light p-2 rounded mb-0 text-dark">{{ $employee->address }}</p>
                        </div>
                    @endif
                </div>

                @if(!$employee?->emergency_contact)
                    <div class="alert alert-warning mt-3 mb-0 small d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                        <div>
                            Emergency contact is not set. <a href="{{ route('portal.profile') }}" class="alert-link">Click here to add emergency contact</a>.
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
            <div class="card-body p-3 p-md-4">
                @if($currentPayslip)
                    <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded-3 mb-3 border">
                        <div>
                            <span class="text-muted small d-block">Estimated Net Take-Home</span>
                            <h3 class="fw-bold text-success mb-0 font-monospace">₹{{ number_format($currentPayslip->net_salary, 2) }}</h3>
                        </div>
                        <span class="badge bg-success px-3 py-2">
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
                        <a href="{{ route('portal.payslip.show', $currentPayslip->salary_month) }}" class="btn btn-success btn-sm flex-grow-1 shadow-sm" target="_blank">
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
            console.log('Location:', err.message);
        });
    }
</script>
@endpush
