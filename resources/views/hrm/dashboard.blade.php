@extends('layouts.app')
@section('title', 'HRM Operations Dashboard')

@section('content')
<div class="container-fluid px-0">

    {{-- 1. Modern Clean Header Bar --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3 pb-3 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h4 class="fw-bold mb-0 text-dark" style="letter-spacing: -0.4px;">
                    HRM Operations Dashboard
                </h4>
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small fw-semibold">
                    <i class="bi bi-geo-alt-fill me-1"></i>{{ auth()->user()->branchLabel() }}
                </span>
            </div>
            <p class="text-muted small mb-0 d-flex align-items-center gap-2 flex-wrap">
                <span><i class="bi bi-calendar3 me-1 text-secondary"></i>{{ now()->format('l, d F Y') }}</span>
                <span>•</span>
                <span><i class="bi bi-broadcast text-success me-1"></i>Live GPS Attendance Tracking</span>
                <span>•</span>
                <span class="text-secondary">{{ auth()->user()->roles->first()?->name ?? 'Admin' }}</span>
            </p>
        </div>

        {{-- Action Buttons --}}
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('employees.create') }}" class="btn btn-success btn-sm rounded-pill px-3 shadow-xs d-inline-flex align-items-center gap-1.5 fw-semibold">
                <i class="bi bi-person-plus-fill"></i>
                <span>Add Employee</span>
            </a>

            <a href="{{ route('attendance.index') }}" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-xs d-inline-flex align-items-center gap-1.5 fw-medium bg-white">
                <i class="bi bi-calendar-check-fill"></i>
                <span>Daily Attendance</span>
            </a>

            <a href="{{ route('hrm.report') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs d-inline-flex align-items-center gap-1.5 fw-medium bg-white">
                <i class="bi bi-file-earmark-bar-graph"></i>
                <span>Monthly Report</span>
            </a>
        </div>
    </div>

    {{-- Filter Toolbar: Branch & Date/Week/Month Scoping --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white" style="border: 1px solid #eef2f6 !important;">
        <div class="card-body p-2.5 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <form method="GET" action="{{ route('hrm.dashboard') }}" id="hrm-filter-form" class="d-flex align-items-center gap-2 flex-wrap">
                @if(isset($branches) && $branches->isNotEmpty())
                    <div class="d-flex align-items-center bg-light border rounded-pill px-2.5 py-1">
                        <i class="bi bi-building text-muted me-1.5 small"></i>
                        <select name="branch_id" class="form-select form-select-sm border-0 bg-transparent fw-semibold text-dark shadow-none p-0 pe-4" style="cursor: pointer;" onchange="this.form.submit()">
                            <option value="">🏢 All Branches</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ (string)($selectedBranchId ?? '') === (string)$b->id ? 'selected' : '' }}>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="d-flex align-items-center bg-light border rounded-pill px-2.5 py-1">
                    <i class="bi bi-clock-history text-muted me-1.5 small"></i>
                    <select name="period" id="hrm-filter-period" class="form-select form-select-sm border-0 bg-transparent fw-semibold text-dark shadow-none p-0 pe-4" style="cursor: pointer;" onchange="handleHrmPeriodChange(this.value)">
                        <option value="today" {{ ($period ?? 'today') === 'today' ? 'selected' : '' }}>⚡ Today</option>
                        <option value="date" {{ ($period ?? '') === 'date' ? 'selected' : '' }}>🎯 Specific Date</option>
                        <option value="week" {{ ($period ?? '') === 'week' ? 'selected' : '' }}>📊 This Week</option>
                        <option value="month" {{ ($period ?? '') === 'month' ? 'selected' : '' }}>📆 This Month</option>
                    </select>
                </div>

                <div id="hrm-date-col" style="{{ ($period ?? '') === 'date' ? '' : 'display: none;' }}">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0 rounded-start-pill"><i class="bi bi-calendar-event"></i></span>
                        <input type="date" name="date" class="form-control form-control-sm rounded-end-pill" value="{{ $selectedDate ?? now()->toDateString() }}" onchange="this.form.submit()">
                    </div>
                </div>

                <div id="hrm-month-col" style="{{ ($period ?? '') === 'month' ? '' : 'display: none;' }}">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0 rounded-start-pill"><i class="bi bi-calendar-month"></i></span>
                        <input type="month" name="month" class="form-control form-control-sm rounded-end-pill" value="{{ $selectedMonth ?? now()->format('Y-m') }}" onchange="this.form.submit()">
                    </div>
                </div>

                @if(($selectedBranchId ?? null) || ($period ?? 'today') !== 'today')
                    <a href="{{ route('hrm.dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-2.5 py-1 text-nowrap d-inline-flex align-items-center gap-1" title="Reset all filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        <span>Reset</span>
                    </a>
                @endif
            </form>

            @if(($selectedBranchId ?? null) || ($period ?? 'today') !== 'today')
                <div class="d-none d-lg-flex align-items-center gap-1.5 text-nowrap ms-auto">
                    @if($selectedBranchId ?? null)
                        @php $activeBranch = $branches->firstWhere('id', $selectedBranchId); @endphp
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1">
                            <i class="bi bi-geo-alt me-1"></i>{{ $activeBranch?->name ?? 'Branch #' . $selectedBranchId }}
                        </span>
                    @else
                        <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1">All Branches</span>
                    @endif
                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2.5 py-1">
                        <i class="bi bi-calendar-event me-1"></i>
                        @if(($period ?? '') === 'today') Today ({{ \Carbon\Carbon::parse($targetDate ?? now())->format('d M') }})
                        @elseif(($period ?? '') === 'week') Week ({{ \Carbon\Carbon::parse($startDate ?? now())->format('d M') }} - {{ \Carbon\Carbon::parse($endDate ?? now())->format('d M Y') }})
                        @elseif(($period ?? '') === 'month') Month: {{ \Carbon\Carbon::parse(($selectedMonth ?? now()->format('Y-m')) . '-01')->format('M Y') }}
                        @elseif(($period ?? '') === 'date') Date: {{ \Carbon\Carbon::parse($selectedDate ?? now())->format('d M Y') }}
                        @endif
                    </span>
                </div>
            @endif
        </div>
    </div>

    <script>
    function handleHrmPeriodChange(val) {
        const dateCol = document.getElementById('hrm-date-col');
        const monthCol = document.getElementById('hrm-month-col');
        if (val === 'date') {
            dateCol.style.display = '';
            monthCol.style.display = 'none';
        } else if (val === 'month') {
            dateCol.style.display = 'none';
            monthCol.style.display = '';
        } else {
            dateCol.style.display = 'none';
            monthCol.style.display = 'none';
            document.getElementById('hrm-filter-form').submit();
        }
    }
    </script>

    {{-- 2. Modern Metric KPI Cards (Sleek SaaS Style) --}}
    <div class="row g-3 mb-4">
        
        {{-- Total Staff --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100" style="border: 1px solid #eef2f6 !important;">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Total Staff</span>
                    <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: #eef2ff; color: #4f46e5;">
                        <i class="bi bi-people-fill fs-6"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mb-2">
                    <h3 class="fw-bold mb-0 text-dark" style="font-size: 1.85rem; letter-spacing: -0.5px;">{{ $totalStaff }}</h3>
                    <span class="text-muted small">employees</span>
                </div>
                <div class="pt-2 border-top d-flex align-items-center justify-content-between text-muted small">
                    <span>Active in this branch</span>
                    @if($totalPayrollEstimate > 0)
                        <span class="fw-semibold text-dark">₹{{ number_format($totalPayrollEstimate / 1000, 1) }}k / mo</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Present Today / Period --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100" style="border: 1px solid #eef2f6 !important;">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">{{ ($isRangePeriod ?? false) ? 'Present (Shifts)' : 'Present Today' }}</span>
                    <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: #ecfdf5; color: #059669;">
                        <i class="bi bi-check2-circle fs-6"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mb-2">
                    <h3 class="fw-bold mb-0 text-success" style="font-size: 1.85rem; letter-spacing: -0.5px;">{{ $presentToday }}</h3>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small">{{ $attendanceRate }}% Rate</span>
                </div>
                <div class="pt-2 border-top">
                    <div class="progress" style="height: 5px; border-radius: 10px; background: #f1f5f9;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $attendanceRate }}%;" aria-valuenow="{{ $attendanceRate }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pending Punch / Half-Day Shifts --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100" style="border: 1px solid #eef2f6 !important;">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">{{ ($isRangePeriod ?? false) ? 'Half-Day Shifts' : 'Pending Punch' }}</span>
                    <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: #fffbeb; color: #d97706;">
                        <i class="bi bi-hourglass-split fs-6"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mb-2">
                    <h3 class="fw-bold mb-0 text-warning" style="font-size: 1.85rem; letter-spacing: -0.5px;">{{ ($isRangePeriod ?? false) ? $halfDayToday : $pendingPunch }}</h3>
                    <span class="text-muted small">{{ ($isRangePeriod ?? false) ? 'half-day records' : 'awaiting in-punch' }}</span>
                </div>
                <div class="pt-2 border-top d-flex align-items-center justify-content-between text-muted small">
                    <span>{{ ($isRangePeriod ?? false) ? 'In Selected Period' : 'Expected today' }}</span>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill">{{ ($isRangePeriod ?? false) ? 'Half-Day' : 'Pending' }}</span>
                </div>
            </div>
        </div>

        {{-- Absent & Leaves --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100" style="border: 1px solid #eef2f6 !important;">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Absent & Leaves</span>
                    <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: #fff1f2; color: #e11d48;">
                        <i class="bi bi-person-x-fill fs-6"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mb-2">
                    <h3 class="fw-bold mb-0 text-danger" style="font-size: 1.85rem; letter-spacing: -0.5px;">{{ $absentToday }}</h3>
                    <span class="text-muted small">absent / {{ $leaveToday }} leave</span>
                </div>
                <div class="pt-2 border-top d-flex align-items-center justify-content-between text-muted small">
                    <span>Workforce attrition</span>
                    <span class="text-danger fw-semibold">{{ $absentToday + $leaveToday }} total</span>
                </div>
            </div>
        </div>

    </div>

    {{-- 3. Full-Width Today's Live Attendance & GPS Telemetry Table (Spacious, Single-Line, No Clutter) --}}
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4" style="border: 1px solid #eef2f6 !important;">
        
        {{-- Card Header --}}
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-3 bg-success-subtle text-success p-2 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0 text-dark">Today's Live GPS Attendance</h6>
                    <span class="text-muted small">Real-time punch activity for {{ now()->format('d M Y') }}</span>
                </div>
            </div>
            <a href="{{ route('attendance.index') }}" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-xs fw-medium">
                <i class="bi bi-calendar-check me-1"></i>Open Daily Attendance Sheet
            </a>
        </div>

        {{-- Table Body (Spacious with white-space nowrap and clean text) --}}
        <div class="table-responsive">
            <table class="table align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
                <thead>
                    <tr class="text-secondary small fw-bold" style="background: #fafafa; border-bottom: 2px solid #f1f5f9;">
                        <th class="ps-4 py-3" style="width: 280px;">STAFF MEMBER</th>
                        <th class="py-3" style="width: 140px;">IN PUNCH</th>
                        <th class="py-3" style="width: 140px;">OUT PUNCH</th>
                        <th class="py-3">PUNCH LOCATION</th>
                        <th class="py-3 text-end pe-4" style="width: 130px;">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($livePunches as $att)
                        @php 
                            $emp = $att->employee; 
                            $payload = [
                                'id' => $emp->id,
                                'name' => $emp->name,
                                'code' => $emp->employee_code,
                                'designation' => $emp->designation ?? 'Staff',
                                'department' => $emp->department ?? 'General',
                                'phone' => $emp->phone ?? 'N/A',
                                'branch' => $emp->branch?->name ?? 'All Branches',
                                'date' => now()->format('d M Y'),
                                'status' => $att->status,
                                'check_in' => $att->check_in ? \Carbon\Carbon::parse($att->check_in)->format('h:i:s A') : null,
                                'check_in_address' => $att->check_in_address ?: 'GPS location recorded',
                                'check_in_lat' => $att->check_in_latitude,
                                'check_in_lng' => $att->check_in_longitude,
                                'check_in_map' => $att->checkInMapUrl(),
                                'check_out' => $att->check_out ? \Carbon\Carbon::parse($att->check_out)->format('h:i:s A') : null,
                                'check_out_address' => $att->check_out_address ?: 'GPS location recorded',
                                'check_out_lat' => $att->check_out_latitude,
                                'check_out_lng' => $att->check_out_longitude,
                                'check_out_map' => $att->checkOutMapUrl(),
                            ];
                        @endphp
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            
                            {{-- Staff Name & Code (Clean text, no wrapping) --}}
                            <td class="ps-4 py-3">
                                <div class="fw-bold text-dark text-nowrap" style="font-size: 0.95rem;">
                                    <a href="{{ route('employees.show', $emp) }}" class="text-dark text-decoration-none">
                                        {{ $emp->name }}
                                    </a>
                                </div>
                                <div class="text-muted small text-nowrap mt-0.5">
                                    <span class="text-success fw-semibold">{{ $emp->employee_code }}</span> 
                                    • {{ $emp->designation }}
                                </div>
                            </td>

                            {{-- In Punch --}}
                            <td class="py-3">
                                @if($att->check_in)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill fw-semibold text-nowrap">
                                        <i class="bi bi-box-arrow-in-right me-1"></i>{{ \Carbon\Carbon::parse($att->check_in)->format('h:i A') }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- Out Punch --}}
                            <td class="py-3">
                                @if($att->check_out)
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 rounded-pill fw-semibold text-nowrap">
                                        <i class="bi bi-box-arrow-right me-1"></i>{{ \Carbon\Carbon::parse($att->check_out)->format('h:i A') }}
                                    </span>
                                @else
                                    <span class="badge bg-light text-secondary border px-3 py-1.5 rounded-pill small text-nowrap">
                                        <i class="bi bi-circle-fill text-success me-1" style="font-size: 0.4rem;"></i>On Duty
                                    </span>
                                @endif
                            </td>

                            {{-- Clean Punch Location Text (No giant wrapped button!) --}}
                            <td class="py-3">
                                @if($att->checkInMapUrl())
                                    <a href="{{ $att->checkInMapUrl() }}" target="_blank" 
                                       class="text-decoration-none text-dark d-inline-flex align-items-center gap-1.5 text-nowrap hover-link"
                                       title="Open in Google Maps: {{ $att->check_in_address }}">
                                        <i class="bi bi-geo-alt-fill text-danger fs-6"></i>
                                        <span class="fw-medium text-dark text-decoration-underline-hover">{{ $att->check_in_address ?: 'GPS Location Pin' }}</span>
                                        <i class="bi bi-box-arrow-up-right text-muted small ms-1" style="font-size: 0.7rem;"></i>
                                    </a>
                                @else
                                    <span class="text-muted small"><i class="bi bi-slash-circle me-1"></i>No GPS record</span>
                                @endif
                            </td>

                            {{-- Action: Clean View Details Button --}}
                            <td class="py-3 text-end pe-4">
                                <button type="button" 
                                        class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-xs view-details-btn text-nowrap fw-medium"
                                        data-employee='@json($payload)'
                                        title="View Full Punch Details">
                                    <i class="bi bi-eye-fill me-1"></i>View
                                </button>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <div class="mb-2"><i class="bi bi-clock-history fs-2 text-secondary"></i></div>
                                <div class="fw-semibold">No punches recorded today yet</div>
                                <p class="small text-muted mb-0">Staff punches will appear here live when checked in.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($livePunches->isNotEmpty())
            <div class="card-footer bg-white border-top py-3 px-4 d-flex justify-content-between align-items-center small text-muted">
                <span>Showing {{ $livePunches->count() }} real-time punches today</span>
                <a href="{{ route('attendance.index') }}" class="text-success fw-semibold text-decoration-none">
                    View Full Attendance Roster <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        @endif
    </div>

    {{-- 4. Bottom Analytics & Shortcuts (2 Balanced Cards Side by Side) --}}
    <div class="row g-4">
        
        {{-- Left: Attendance Breakdown Donut Chart --}}
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100" style="border: 1px solid #eef2f6 !important;">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark small text-uppercase" style="letter-spacing: 0.5px;">Attendance Health</h6>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill small">
                        {{ $attendanceRate }}% Present
                    </span>
                </div>

                <div class="position-relative d-flex justify-content-center align-items-center my-3" style="height: 180px;">
                    <canvas id="attendanceDonutChart"></canvas>
                </div>

                <div class="row g-2 mt-2 pt-2 border-top text-center small">
                    <div class="col-3">
                        <div class="text-muted" style="font-size: 0.75rem;">Present</div>
                        <div class="fw-bold text-success fs-6">{{ $presentToday }}</div>
                    </div>
                    <div class="col-3">
                        <div class="text-muted" style="font-size: 0.75rem;">Half-Day</div>
                        <div class="fw-bold text-warning fs-6">{{ $halfDayToday }}</div>
                    </div>
                    <div class="col-3">
                        <div class="text-muted" style="font-size: 0.75rem;">Pending</div>
                        <div class="fw-bold text-secondary fs-6">{{ $pendingPunch }}</div>
                    </div>
                    <div class="col-3">
                        <div class="text-muted" style="font-size: 0.75rem;">Absent</div>
                        <div class="fw-bold text-danger fs-6">{{ $absentToday }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Quick HR Shortcuts & Branch Staff Directory --}}
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100" style="border: 1px solid #eef2f6 !important;">
                <h6 class="fw-bold mb-3 text-dark small text-uppercase" style="letter-spacing: 0.5px;">Quick HR Management Shortcuts</h6>
                
                <div class="row g-3 mb-4">
                    <div class="col-6 col-sm-3">
                        <a href="{{ route('employees.create') }}" class="card p-3 border rounded-3 text-decoration-none text-center h-100 bg-light hover-shortcut">
                            <i class="bi bi-person-plus-fill fs-3 text-success mb-1"></i>
                            <div class="fw-bold text-dark small">Add Staff</div>
                            <span class="text-muted" style="font-size: 0.75rem;">New employee</span>
                        </a>
                    </div>
                    <div class="col-6 col-sm-3">
                        <a href="{{ route('attendance.index') }}" class="card p-3 border rounded-3 text-decoration-none text-center h-100 bg-light hover-shortcut">
                            <i class="bi bi-calendar-check-fill fs-3 text-primary mb-1"></i>
                            <div class="fw-bold text-dark small">Daily Sheet</div>
                            <span class="text-muted" style="font-size: 0.75rem;">Verify & mark</span>
                        </a>
                    </div>
                    <div class="col-6 col-sm-3">
                        <a href="{{ route('hrm.report') }}" class="card p-3 border rounded-3 text-decoration-none text-center h-100 bg-light hover-shortcut">
                            <i class="bi bi-file-earmark-spreadsheet-fill fs-3 text-warning mb-1"></i>
                            <div class="fw-bold text-dark small">Reports</div>
                            <span class="text-muted" style="font-size: 0.75rem;">Monthly matrix</span>
                        </a>
                    </div>
                    <div class="col-6 col-sm-3">
                        <a href="{{ route('employees.index') }}" class="card p-3 border rounded-3 text-decoration-none text-center h-100 bg-light hover-shortcut">
                            <i class="bi bi-people-fill fs-3 text-info mb-1"></i>
                            <div class="fw-bold text-dark small">Staff Roster</div>
                            <span class="text-muted" style="font-size: 0.75rem;">View directory</span>
                        </a>
                    </div>
                </div>

                {{-- Department headcount progress bars --}}
                <div class="pt-2 border-top">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold small text-muted text-uppercase" style="font-size: 0.75rem;">Headcount by Department</span>
                        <span class="text-muted small">{{ $totalStaff }} staff total</span>
                    </div>
                    <div class="row g-2">
                        @foreach($deptCounts as $dept => $count)
                            @php $pct = $totalStaff > 0 ? round(($count / $totalStaff) * 100) : 0; @endphp
                            <div class="col-6 col-md-4">
                                <div class="p-2 rounded bg-light border">
                                    <div class="d-flex justify-content-between small fw-medium mb-1">
                                        <span>{{ $dept ?: 'General' }}</span>
                                        <strong>{{ $count }}</strong>
                                    </div>
                                    <div class="progress" style="height: 4px;">
                                        <div class="progress-bar bg-success" style="width: {{ $pct }}%;"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>

    </div>

</div>

{{-- MODAL: Full Login & Location Details Modal --}}
<div class="modal fade" id="attendanceDetailModal" tabindex="-1" aria-labelledby="attendanceDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
            <div class="modal-header border-bottom px-4 py-3 bg-light">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success-subtle text-success p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-person-badge fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="modalEmpName">Employee Details</h5>
                        <div class="text-muted small" id="modalEmpSub">EMP-0000 • Department</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <div class="p-3 rounded-3 bg-light border mb-4">
                    <div class="row g-3 text-center text-sm-start">
                        <div class="col-6 col-sm-3 border-end">
                            <div class="small text-muted">Date</div>
                            <div class="fw-bold text-dark" id="modalDate">-</div>
                        </div>
                        <div class="col-6 col-sm-3 border-end">
                            <div class="small text-muted">Status</div>
                            <div id="modalStatusBadge"><span class="badge bg-secondary">Unknown</span></div>
                        </div>
                        <div class="col-6 col-sm-3 border-end">
                            <div class="small text-muted">Branch</div>
                            <div class="fw-semibold text-dark" id="modalBranch">-</div>
                        </div>
                        <div class="col-6 col-sm-3">
                            <div class="small text-muted">Contact Phone</div>
                            <div class="fw-semibold text-dark" id="modalPhone">-</div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    {{-- Check-In Card --}}
                    <div class="col-12 col-md-6">
                        <div class="card h-100 border rounded-3 p-3 shadow-none bg-white">
                            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="badge bg-success-subtle text-success p-2 rounded-circle">
                                        <i class="bi bi-box-arrow-in-right fs-6"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-0">Check-In Details</h6>
                                </div>
                                <span class="badge bg-success" id="modalInBadge">Logged In</span>
                            </div>

                            <div class="mb-3">
                                <label class="small text-muted d-block">Punch Timestamp</label>
                                <div class="fs-5 fw-bold text-dark" id="modalInTime">--:--</div>
                            </div>

                            <div class="mb-3">
                                <label class="small text-muted d-block"><i class="bi bi-geo-alt-fill text-danger me-1"></i>Location Address</label>
                                <div class="small fw-semibold text-dark p-2 bg-light rounded border" id="modalInAddress">
                                    No GPS address captured
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="small text-muted d-block">GPS Coordinates</label>
                                <div class="small text-secondary font-monospace" id="modalInCoords">
                                    Lat: -- | Lng: --
                                </div>
                            </div>

                            <div class="mt-auto pt-2" id="modalInMapBtnContainer">
                                <a href="#" target="_blank" id="modalInMapBtn" class="btn btn-outline-success btn-sm w-100 rounded-pill">
                                    <i class="bi bi-map-fill me-1"></i>View on Google Maps
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Check-Out Card --}}
                    <div class="col-12 col-md-6">
                        <div class="card h-100 border rounded-3 p-3 shadow-none bg-white">
                            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="badge bg-primary-subtle text-primary p-2 rounded-circle">
                                        <i class="bi bi-box-arrow-right fs-6"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-0">Check-Out Details</h6>
                                </div>
                                <span class="badge bg-secondary" id="modalOutBadge">Pending</span>
                            </div>

                            <div class="mb-3">
                                <label class="small text-muted d-block">Punch Timestamp</label>
                                <div class="fs-5 fw-bold text-dark" id="modalOutTime">--:--</div>
                            </div>

                            <div class="mb-3">
                                <label class="small text-muted d-block"><i class="bi bi-geo-alt-fill text-danger me-1"></i>Location Address</label>
                                <div class="small fw-semibold text-dark p-2 bg-light rounded border" id="modalOutAddress">
                                    Not checked out yet
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="small text-muted d-block">GPS Coordinates</label>
                                <div class="small text-secondary font-monospace" id="modalOutCoords">
                                    Lat: -- | Lng: --
                                </div>
                            </div>

                            <div class="mt-auto pt-2" id="modalOutMapBtnContainer">
                                <a href="#" target="_blank" id="modalOutMapBtn" class="btn btn-outline-primary btn-sm w-100 rounded-pill">
                                    <i class="bi bi-map-fill me-1"></i>View on Google Maps
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light px-4 py-2 border-top">
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.hover-shortcut {
    transition: all 0.15s ease-in-out;
}
.hover-shortcut:hover {
    transform: translateY(-2px);
    border-color: #16a34a !important;
    background-color: #f0fdf4 !important;
}
.hover-link:hover span {
    color: #16a34a !important;
    text-decoration: underline !important;
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Donut Chart
    const ctx = document.getElementById('attendanceDonutChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Present', 'Half-Day', 'Pending', 'Absent', 'On Leave'],
                datasets: [{
                    data: [
                        {{ $presentToday }},
                        {{ $halfDayToday }},
                        {{ $pendingPunch }},
                        {{ $absentToday }},
                        {{ $leaveToday }}
                    ],
                    backgroundColor: [
                        '#16a34a',
                        '#f59e0b',
                        '#94a3b8',
                        '#ef4444',
                        '#06b6d4'
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ` ${context.label}: ${context.raw} staff`;
                            }
                        }
                    }
                }
            }
        });
    }

    // Modal Details Handler
    const detailModalEl = document.getElementById('attendanceDetailModal');
    const detailModal = new bootstrap.Modal(detailModalEl);

    document.querySelectorAll('.view-details-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const data = JSON.parse(this.getAttribute('data-employee'));

            document.getElementById('modalEmpName').textContent = data.name;
            document.getElementById('modalEmpSub').textContent = `${data.code} • ${data.designation} (${data.department})`;
            document.getElementById('modalDate').textContent = data.date;
            document.getElementById('modalBranch').textContent = data.branch;
            document.getElementById('modalPhone').textContent = data.phone;

            const statusEl = document.getElementById('modalStatusBadge');
            if (data.status === 'Present') {
                statusEl.innerHTML = '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill"><i class="bi bi-check-circle-fill me-1"></i>Present</span>';
            } else if (data.status === 'Half-Day') {
                statusEl.innerHTML = '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 rounded-pill"><i class="bi bi-clock-fill me-1"></i>Half-Day</span>';
            } else if (data.status === 'Absent') {
                statusEl.innerHTML = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill"><i class="bi bi-x-circle-fill me-1"></i>Absent</span>';
            } else {
                statusEl.innerHTML = '<span class="badge bg-secondary-subtle text-secondary border px-2 py-1 rounded-pill">Pending</span>';
            }

            // In Punch
            const inTimeEl = document.getElementById('modalInTime');
            const inBadgeEl = document.getElementById('modalInBadge');
            const inAddrEl = document.getElementById('modalInAddress');
            const inCoordsEl = document.getElementById('modalInCoords');
            const inMapBtn = document.getElementById('modalInMapBtn');
            const inMapContainer = document.getElementById('modalInMapBtnContainer');

            if (data.check_in) {
                inTimeEl.textContent = data.check_in;
                inTimeEl.className = 'fs-5 fw-bold text-success';
                inBadgeEl.textContent = 'Punched In';
                inBadgeEl.className = 'badge bg-success';
                inAddrEl.textContent = data.check_in_address;
                
                if (data.check_in_lat && data.check_in_lng) {
                    inCoordsEl.textContent = `Lat: ${data.check_in_lat} | Lng: ${data.check_in_lng}`;
                    inMapBtn.href = data.check_in_map || `https://www.google.com/maps?q=${data.check_in_lat},${data.check_in_lng}`;
                    inMapContainer.style.display = 'block';
                } else {
                    inCoordsEl.textContent = 'No GPS coordinates recorded';
                    inMapContainer.style.display = 'none';
                }
            } else {
                inTimeEl.textContent = 'Not Checked In';
                inTimeEl.className = 'fs-5 fw-bold text-muted';
                inBadgeEl.textContent = 'Pending';
                inBadgeEl.className = 'badge bg-secondary';
                inAddrEl.textContent = 'No punch recorded today';
                inCoordsEl.textContent = 'Lat: -- | Lng: --';
                inMapContainer.style.display = 'none';
            }

            // Out Punch
            const outTimeEl = document.getElementById('modalOutTime');
            const outBadgeEl = document.getElementById('modalOutBadge');
            const outAddrEl = document.getElementById('modalOutAddress');
            const outCoordsEl = document.getElementById('modalOutCoords');
            const outMapBtn = document.getElementById('modalOutMapBtn');
            const outMapContainer = document.getElementById('modalOutMapBtnContainer');

            if (data.check_out) {
                outTimeEl.textContent = data.check_out;
                outTimeEl.className = 'fs-5 fw-bold text-primary';
                outBadgeEl.textContent = 'Checked Out';
                outBadgeEl.className = 'badge bg-primary';
                outAddrEl.textContent = data.check_out_address;
                
                if (data.check_out_lat && data.check_out_lng) {
                    outCoordsEl.textContent = `Lat: ${data.check_out_lat} | Lng: ${data.check_out_lng}`;
                    outMapBtn.href = data.check_out_map || `https://www.google.com/maps?q=${data.check_out_lat},${data.check_out_lng}`;
                    outMapContainer.style.display = 'block';
                } else {
                    outCoordsEl.textContent = 'No GPS coordinates recorded';
                    outMapContainer.style.display = 'none';
                }
            } else {
                outTimeEl.textContent = 'Not Checked Out';
                outTimeEl.className = 'fs-5 fw-bold text-muted';
                outBadgeEl.textContent = 'Active / On-Duty';
                outBadgeEl.className = 'badge bg-light text-dark border';
                outAddrEl.textContent = 'Punch out not registered yet';
                outCoordsEl.textContent = 'Lat: -- | Lng: --';
                outMapContainer.style.display = 'none';
            }

            detailModal.show();
        });
    });
});
</script>
@endpush
@endsection