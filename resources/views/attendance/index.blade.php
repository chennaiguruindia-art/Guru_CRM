@extends('layouts.app')
@section('title', 'Daily Attendance View')

@section('content')
<div class="container-fluid px-0">

    {{-- Main View Card --}}
    <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-4 bg-white">
        
        {{-- Header row --}}
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="d-flex align-items-center justify-content-center rounded-3 shadow-xs" 
                     style="width: 44px; height: 44px; background: #f3e8ff; color: #7e22ce;">
                    <i class="bi bi-person-badge fs-4"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-0 text-dark" style="letter-spacing: -0.3px;">Daily Attendance View</h4>
                    <p class="text-muted small mb-0">Monitor staff punches, GPS locations, and daily activity</p>
                </div>
            </div>

            {{-- Branch pill for scoped admin or selector for super admin --}}
            <div class="d-flex align-items-center gap-2">
                @if(auth()->user()->canSeeAllBranches() && !empty($branches) && $branches->isNotEmpty())
                    <form method="GET" action="{{ route('attendance.index') }}" class="d-inline-block">
                        <input type="hidden" name="date" value="{{ $date }}">
                        <select name="branch_id" class="form-select form-select-sm rounded-pill border-secondary-subtle" onchange="this.form.submit()">
                            <option value="">🏢 All Branches</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </form>
                @else
                    <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-medium">
                        <i class="bi bi-geo-alt-fill text-success me-1"></i>{{ auth()->user()->branchLabel() }}
                    </span>
                @endif

                <button class="btn btn-outline-success btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#bulkMarkModal">
                    <i class="bi bi-pencil-square me-1"></i>Bulk Mark / Edit
                </button>
            </div>
        </div>

        {{-- Toolbar matching reference design --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-2 border-bottom">
            
            {{-- Left Side: Search + Filter --}}
            <div class="d-flex align-items-center gap-2 flex-grow-1 flex-md-grow-0" style="min-width: 280px; max-width: 460px;">
                <form method="GET" action="{{ route('attendance.index') }}" class="d-flex align-items-center gap-2 w-100">
                    <input type="hidden" name="date" value="{{ $date }}">
                    @if(request('department'))
                        <input type="hidden" name="department" value="{{ request('department') }}">
                    @endif
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    @if(request('branch_id'))
                        <input type="hidden" name="branch_id" value="{{ request('branch_id') }}">
                    @endif

                    <div class="input-group input-group-sm rounded-pill overflow-hidden border">
                        <span class="input-group-text bg-white border-0 text-muted ps-3">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" 
                               name="search" 
                               class="form-control border-0 py-2 ps-1 shadow-none" 
                               placeholder="Search by Name or Emp ID" 
                               value="{{ request('search') }}">
                        @if(request('search'))
                            <a href="{{ route('attendance.index', ['date' => $date]) }}" class="input-group-text bg-white border-0 text-muted text-decoration-none pe-3" title="Clear Search">
                                <i class="bi bi-x-circle-fill"></i>
                            </a>
                        @endif
                    </div>

                    {{-- Filter Dropdown --}}
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2 d-flex align-items-center gap-1 dropdown-toggle shadow-none" 
                                type="button" 
                                data-bs-toggle="dropdown" 
                                aria-expanded="false"
                                style="border-color: #cbd5e1; background: #f8fafc;">
                            <i class="bi bi-funnel text-primary"></i>
                            <span class="fw-semibold text-dark">Filter</span>
                            @if(request('department') || request('status'))
                                <span class="badge bg-primary rounded-circle p-1" style="width: 8px; height: 8px;"></span>
                            @endif
                        </button>
                        <div class="dropdown-menu dropdown-menu-end shadow border-0 p-3" style="width: 270px; border-radius: 12px;">
                            <h6 class="fw-bold mb-2 small text-uppercase text-muted">Filter Employees</h6>
                            
                            {{-- Department filter --}}
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted mb-1">Department</label>
                                <select name="department" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">All Departments</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Status filter --}}
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted mb-1">Attendance Status</label>
                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">All Statuses</option>
                                    <option value="Present" {{ request('status') == 'Present' ? 'selected' : '' }}>Present</option>
                                    <option value="Half-Day" {{ request('status') == 'Half-Day' ? 'selected' : '' }}>Half-Day</option>
                                    <option value="Absent" {{ request('status') == 'Absent' ? 'selected' : '' }}>Absent</option>
                                    <option value="Leave" {{ request('status') == 'Leave' ? 'selected' : '' }}>Leave</option>
                                    <option value="Not Marked" {{ request('status') == 'Not Marked' ? 'selected' : '' }}>Not Marked / Pending</option>
                                </select>
                            </div>

                            @if(request('department') || request('status') || request('search'))
                                <div class="text-center pt-2 border-top">
                                    <a href="{{ route('attendance.index', ['date' => $date]) }}" class="small text-danger text-decoration-none fw-semibold">
                                        <i class="bi bi-x-circle me-1"></i>Reset All Filters
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            {{-- Right Side: Date Navigator + Download Button --}}
            <div class="d-flex align-items-center gap-2 flex-wrap">
                
                {{-- Date Navigator Pill --}}
                <div class="d-flex align-items-center bg-white border rounded-pill px-3 py-1 shadow-sm gap-2" style="border-color: #e2e8f0;">
                    <a href="{{ route('attendance.index', array_merge(request()->query(), ['date' => $prevDate])) }}" 
                       class="text-decoration-none text-secondary px-1 py-1 hover-opacity" 
                       title="Previous Day ({{ $prevDate }})">
                        <i class="bi bi-chevron-left fw-bold"></i>
                    </a>

                    <div class="d-flex align-items-center gap-2 px-2 border-start border-end">
                        <span class="fw-semibold text-dark text-nowrap" style="min-width: 95px; text-align: center; font-size: 0.92rem;">
                            {{ $formattedDate }}
                        </span>
                        
                        {{-- Hidden Date picker triggered by calendar icon --}}
                        <label for="dateNavInput" class="mb-0 text-muted cursor-pointer" title="Pick Date">
                            <i class="bi bi-calendar-event text-primary fs-6"></i>
                        </label>
                        <form id="dateNavForm" method="GET" action="{{ route('attendance.index') }}" style="display: none;">
                            @foreach(request()->except('date') as $key => $val)
                                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                            @endforeach
                            <input type="date" id="dateNavInput" name="date" value="{{ $date }}" onchange="document.getElementById('dateNavForm').submit()">
                        </form>
                    </div>

                    <a href="{{ route('attendance.index', array_merge(request()->query(), ['date' => $nextDate])) }}" 
                       class="text-decoration-none text-secondary px-1 py-1 hover-opacity" 
                       title="Next Day ({{ $nextDate }})">
                        <i class="bi bi-chevron-right fw-bold"></i>
                    </a>
                </div>

                {{-- Download Button --}}
                <a href="{{ route('attendance.index', array_merge(request()->query(), ['export' => 'csv'])) }}" 
                   class="btn btn-outline-primary btn-sm rounded-pill px-3 py-2 d-flex align-items-center gap-1 shadow-sm"
                   title="Export to CSV">
                    <i class="bi bi-download"></i>
                    <span class="fw-semibold">Download</span>
                </a>

                {{-- Quick refresh / today --}}
                @if($date !== now()->toDateString())
                    <a href="{{ route('attendance.index') }}" class="btn btn-light btn-sm rounded-pill px-3 py-2 text-primary border" title="Go to Today">
                        <i class="bi bi-arrow-clockwise me-1"></i>Today
                    </a>
                @endif
            </div>

        </div>

        {{-- KPI Quick Badges --}}
        <div class="row g-2 mb-3">
            <div class="col-6 col-md-3">
                <div class="p-2 px-3 rounded-3 bg-light border d-flex align-items-center justify-content-between">
                    <span class="small text-muted"><i class="bi bi-check-circle-fill text-success me-1"></i>Present</span>
                    <span class="fw-bold text-success">{{ $presentCount }}</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-2 px-3 rounded-3 bg-light border d-flex align-items-center justify-content-between">
                    <span class="small text-muted"><i class="bi bi-clock-fill text-warning me-1"></i>Half-Day</span>
                    <span class="fw-bold text-warning">{{ $halfDayCount }}</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-2 px-3 rounded-3 bg-light border d-flex align-items-center justify-content-between">
                    <span class="small text-muted"><i class="bi bi-x-circle-fill text-danger me-1"></i>Absent</span>
                    <span class="fw-bold text-danger">{{ $absentCount }}</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-2 px-3 rounded-3 bg-light border d-flex align-items-center justify-content-between">
                    <span class="small text-muted"><i class="bi bi-dash-circle text-secondary me-1"></i>Not Marked</span>
                    <span class="fw-bold text-secondary">{{ max(0, $employees->count() - ($presentCount + $halfDayCount + $absentCount + $leaveCount)) }}</span>
                </div>
            </div>
        </div>

        {{-- Attendance Table matching image mockup --}}
        <div class="table-responsive rounded-3">
            <table class="table align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
                <thead>
                    <tr class="text-secondary small fw-bold" style="border-bottom: 2px solid #f1f5f9; background: #fafafa;">
                        <th class="ps-3 py-3" style="width: 280px;">NAME</th>
                        <th class="py-3" style="width: 180px;">DEPARTMENT</th>
                        <th class="py-3" style="width: 170px;">ATTENDANCE</th>
                        <th class="py-3" style="width: 140px;">IN TIME</th>
                        <th class="py-3" style="width: 140px;">OUT TIME</th>
                        <th class="py-3 text-end pe-3" style="width: 140px;">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $emp)
                    @php 
                        $att = $attendances->get($emp->id);
                        $hasIn = $att && !empty($att->check_in);
                        $hasOut = $att && !empty($att->check_out);
                        $status = $att ? $att->status : 'Not Marked';

                        // Data payload for modal view
                        $payload = [
                            'id' => $emp->id,
                            'name' => $emp->name,
                            'code' => $emp->employee_code,
                            'designation' => $emp->designation ?? 'Staff',
                            'department' => $emp->department ?? 'General',
                            'phone' => $emp->phone ?? 'N/A',
                            'email' => $emp->email ?? 'N/A',
                            'branch' => $emp->branch?->name ?? 'Head Office',
                            'date' => $formattedDate,
                            'status' => $status,
                            'check_in' => $hasIn ? \Carbon\Carbon::parse($att->check_in)->format('h:i:s A') : null,
                            'check_in_time' => $hasIn ? \Carbon\Carbon::parse($att->check_in)->format('H:i') : null,
                            'check_in_lat' => $att?->check_in_latitude,
                            'check_in_lng' => $att?->check_in_longitude,
                            'check_in_address' => $att?->check_in_address ?: 'GPS location recorded',
                            'check_in_map' => $att?->checkInMapUrl(),
                            'check_in_image' => $att?->checkInImageUrl(),
                            'check_out' => $hasOut ? \Carbon\Carbon::parse($att->check_out)->format('h:i:s A') : null,
                            'check_out_time' => $hasOut ? \Carbon\Carbon::parse($att->check_out)->format('H:i') : null,
                            'is_auto_checkout' => (bool) ($att?->is_auto_checkout),
                            'check_out_lat' => $att?->check_out_latitude,
                            'check_out_lng' => $att?->check_out_longitude,
                            'check_out_address' => ($att?->is_auto_checkout ? 'Employee did not logout — System auto logged out at 07:00 PM' : ($att?->check_out_address ?: 'GPS location recorded')),
                            'check_out_map' => $att?->checkOutMapUrl(),
                            'check_out_image' => $att?->checkOutImageUrl(),
                            'is_selfie_expired' => $att && \Carbon\Carbon::parse($att->date)->lt(now('Asia/Kolkata')->subDays(45)),
                        ];
                    @endphp
                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;" class="attendance-row">
                        
                        {{-- Name Column --}}
                        <td class="ps-3 py-3">
                            <div class="fw-bold text-dark mb-0" style="font-size: 0.95rem;">
                                {{ $emp->name }}
                            </div>
                            <div class="text-muted small">
                                <span class="text-success fw-semibold">{{ $emp->employee_code }}</span>
                                @if($emp->designation)
                                    • {{ $emp->designation }}
                                @endif
                            </div>
                        </td>

                        {{-- Department Column --}}
                        <td class="py-3 text-secondary">
                            @if(!empty($emp->department))
                                <span class="fw-medium text-dark">{{ $emp->department }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        {{-- Attendance Status Column --}}
                        <td class="py-3">
                            @if($status === 'Present')
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill fw-semibold">
                                    <i class="bi bi-check-circle-fill me-1"></i>Present
                                </span>
                            @elseif($status === 'Half-Day')
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-1 rounded-pill fw-semibold">
                                    <i class="bi bi-clock-fill me-1"></i>Half-Day
                                </span>
                            @elseif($status === 'Absent')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 rounded-pill fw-semibold">
                                    <i class="bi bi-x-circle-fill me-1"></i>Absent
                                </span>
                            @elseif($status === 'Leave')
                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-3 py-1 rounded-pill fw-semibold">
                                    <i class="bi bi-calendar-minus me-1"></i>Leave
                                </span>
                            @else
                                <span class="text-muted fw-normal" style="font-size: 0.9rem;">
                                    Pending
                                </span>
                            @endif
                        </td>

                        {{-- In Time Column --}}
                        <td class="py-3">
                            @if($hasIn)
                                <div class="d-flex align-items-center gap-1">
                                    <span class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($att->check_in)->format('H:i') }}</span>
                                    @if($att->check_in_latitude)
                                        <i class="bi bi-geo-alt-fill text-danger small" title="GPS Captured"></i>
                                    @endif
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        {{-- Out Time Column --}}
                        <td class="py-3">
                            @if($hasOut)
                                <div class="d-flex align-items-center gap-1">
                                    <span class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($att->check_out)->format('H:i') }}</span>
                                    @if($att->is_auto_checkout)
                                        <span class="badge bg-warning text-dark px-1 py-0 border" style="font-size: 0.68rem;" title="Employee did not logout — System auto logged out at 07:00 PM">
                                            <i class="bi bi-clock-history me-1"></i>Auto
                                        </span>
                                    @elseif($att->check_out_latitude)
                                        <i class="bi bi-geo-alt-fill text-danger small" title="GPS Captured"></i>
                                    @endif
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        {{-- Action Column: View Details Button --}}
                        <td class="py-3 text-end pe-3">
                            <button type="button" 
                                    class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-xs view-details-btn"
                                    data-employee='@json($payload)'
                                    title="View Full Login Details & GPS Location">
                                <i class="bi bi-eye-fill me-1"></i>View
                            </button>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <div class="mb-2"><i class="bi bi-inbox fs-2 text-secondary"></i></div>
                            <div class="fw-semibold">No attendance records found</div>
                            <p class="small text-muted mb-0">Try adjusting your filters or date selection.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

</div>

{{-- MODAL: Full Login & Location Details Modal --}}
<div class="modal fade" id="attendanceDetailModal" tabindex="-1" aria-labelledby="attendanceDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
            
            {{-- Modal Header --}}
            <div class="modal-header border-bottom px-4 py-3 bg-light">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-person-badge fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="modalEmpName">Employee Details</h5>
                        <div class="text-muted small" id="modalEmpSub">EMP-0000 • Department</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Modal Body --}}
            <div class="modal-body p-4">
                
                {{-- Quick Overview Strip --}}
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

                {{-- Two-column punch cards: Check-In vs Check-Out --}}
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

                            <div class="mb-3" id="modalInSelfieContainer">
                                <label class="small text-muted d-block mb-1"><i class="bi bi-camera-fill text-success me-1"></i>Check-In Selfie</label>
                                <div id="modalInSelfieBody" class="small text-muted">—</div>
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

                            <div class="mb-3" id="modalOutSelfieContainer">
                                <label class="small text-muted d-block mb-1"><i class="bi bi-camera-fill text-primary me-1"></i>Check-Out Selfie</label>
                                <div id="modalOutSelfieBody" class="small text-muted">—</div>
                            </div>

                            <div class="mt-auto pt-2" id="modalOutMapBtnContainer">
                                <a href="#" target="_blank" id="modalOutMapBtn" class="btn btn-outline-primary btn-sm w-100 rounded-pill">
                                    <i class="bi bi-map-fill me-1"></i>View on Google Maps
                                </a>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Admin Quick Edit Form for this employee --}}
                <div class="mt-4 pt-3 border-top">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="fw-bold small text-muted text-uppercase"><i class="bi bi-shield-lock me-1"></i>Admin Attendance Override</span>
                        <button class="btn btn-link btn-sm text-decoration-none p-0 text-primary" type="button" data-bs-toggle="collapse" data-bs-target="#editAttendanceCollapse">
                            <i class="bi bi-sliders me-1"></i>Edit Attendance
                        </button>
                    </div>

                    <div class="collapse" id="editAttendanceCollapse">
                        <form method="POST" action="{{ route('attendance.store') }}" class="p-3 bg-light rounded-3 border">
                            @csrf
                            <input type="hidden" name="date" value="{{ $date }}">
                            <input type="hidden" name="attendance_employee_id" id="editEmpId" value="">

                            <div class="row g-2 align-items-end">
                                <div class="col-12 col-sm-4">
                                    <label class="form-label small fw-semibold text-muted mb-1">Status</label>
                                    <select name="attendance_status" id="editStatus" class="form-select form-select-sm">
                                        <option value="Present">Present</option>
                                        <option value="Half-Day">Half-Day</option>
                                        <option value="Absent">Absent</option>
                                        <option value="Leave">Leave</option>
                                    </select>
                                </div>
                                <div class="col-6 col-sm-3">
                                    <label class="form-label small fw-semibold text-muted mb-1">In Time</label>
                                    <input type="time" name="attendance_in" id="editInTime" class="form-control form-control-sm">
                                </div>
                                <div class="col-6 col-sm-3">
                                    <label class="form-label small fw-semibold text-muted mb-1">Out Time</label>
                                    <input type="time" name="attendance_out" id="editOutTime" class="form-control form-control-sm">
                                </div>
                                <div class="col-12 col-sm-2">
                                    <button type="button" class="btn btn-success btn-sm w-100" id="saveSingleAttendanceBtn">
                                        Save
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>

            {{-- Modal Footer --}}
            <div class="modal-footer bg-light px-4 py-2 border-top">
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>

{{-- MODAL: Bulk Mark Attendance --}}
<div class="modal fade" id="bulkMarkModal" tabindex="-1" aria-labelledby="bulkMarkModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-bottom px-4 py-3 bg-light">
                <div>
                    <h5 class="modal-title fw-bold text-dark mb-0">Bulk Attendance Marking</h5>
                    <p class="text-muted small mb-0">Record or edit attendance for {{ $formattedDate }}</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('attendance.store') }}">
                @csrf
                <input type="hidden" name="date" value="{{ $date }}">
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-muted">
                                <tr>
                                    <th class="ps-3 py-2">Emp Code</th>
                                    <th class="py-2">Employee</th>
                                    <th class="py-2">In Time</th>
                                    <th class="py-2">Out Time</th>
                                    <th class="py-2 pe-3" style="width: 320px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employees as $emp)
                                @php 
                                    $rec = $attendances->get($emp->id);
                                    $curr = $rec?->status ?? 'Present';
                                @endphp
                                <tr>
                                    <td class="ps-3 fw-semibold text-success">{{ $emp->employee_code }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $emp->name }}</div>
                                        <div class="text-muted small">{{ $emp->department }} • {{ $emp->designation }}</div>
                                    </td>
                                    <td>
                                        <input type="time" name="check_in[{{ $emp->id }}]" class="form-control form-control-sm" style="width: 105px;" value="{{ $rec?->check_in ? \Carbon\Carbon::parse($rec->check_in)->format('H:i') : '' }}">
                                    </td>
                                    <td>
                                        <input type="time" name="check_out[{{ $emp->id }}]" class="form-control form-control-sm" style="width: 105px;" value="{{ $rec?->check_out ? \Carbon\Carbon::parse($rec->check_out)->format('H:i') : '' }}">
                                    </td>
                                    <td class="pe-3">
                                        <div class="btn-group btn-group-sm w-100" role="group">
                                            <input type="radio" class="btn-check" name="attendance[{{ $emp->id }}]" id="modal_pres_{{ $emp->id }}" value="Present" @checked($curr === 'Present')>
                                            <label class="btn btn-outline-success" for="modal_pres_{{ $emp->id }}">Present</label>

                                            <input type="radio" class="btn-check" name="attendance[{{ $emp->id }}]" id="modal_half_{{ $emp->id }}" value="Half-Day" @checked($curr === 'Half-Day')>
                                            <label class="btn btn-outline-warning" for="modal_half_{{ $emp->id }}">Half-Day</label>

                                            <input type="radio" class="btn-check" name="attendance[{{ $emp->id }}]" id="modal_abs_{{ $emp->id }}" value="Absent" @checked($curr === 'Absent')>
                                            <label class="btn btn-outline-danger" for="modal_abs_{{ $emp->id }}">Absent</label>

                                            <input type="radio" class="btn-check" name="attendance[{{ $emp->id }}]" id="modal_leave_{{ $emp->id }}" value="Leave" @checked($curr === 'Leave')>
                                            <label class="btn btn-outline-secondary" for="modal_leave_{{ $emp->id }}">Leave</label>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3 border-top">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm rounded-pill px-4"><i class="bi bi-check2 me-1"></i>Save All Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.attendance-row:hover {
    background-color: #f8fafc !important;
}
.hover-opacity:hover {
    opacity: 0.7;
}
.cursor-pointer {
    cursor: pointer;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const detailModalEl = document.getElementById('attendanceDetailModal');
    const detailModal = new bootstrap.Modal(detailModalEl);

    // Click handler on View Details buttons
    document.querySelectorAll('.view-details-btn').forEach(button => {
        button.addEventListener('click', function () {
            const data = JSON.parse(this.getAttribute('data-employee'));

            // Header details
            document.getElementById('modalEmpName').textContent = data.name;
            document.getElementById('modalEmpSub').textContent = `${data.code} • ${data.designation} (${data.department})`;
            document.getElementById('modalDate').textContent = data.date;
            document.getElementById('modalBranch').textContent = data.branch;
            document.getElementById('modalPhone').textContent = data.phone;

            // Status badge
            const statusEl = document.getElementById('modalStatusBadge');
            if (data.status === 'Present') {
                statusEl.innerHTML = '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill"><i class="bi bi-check-circle-fill me-1"></i>Present</span>';
            } else if (data.status === 'Half-Day') {
                statusEl.innerHTML = '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 rounded-pill"><i class="bi bi-clock-fill me-1"></i>Half-Day</span>';
            } else if (data.status === 'Absent') {
                statusEl.innerHTML = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill"><i class="bi bi-x-circle-fill me-1"></i>Absent</span>';
            } else if (data.status === 'Leave') {
                statusEl.innerHTML = '<span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-1 rounded-pill"><i class="bi bi-calendar-minus me-1"></i>Leave</span>';
            } else {
                statusEl.innerHTML = '<span class="badge bg-secondary-subtle text-secondary border px-2 py-1 rounded-pill">Pending</span>';
            }

            // Check-In Details
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

            const inSelfieBody = document.getElementById('modalInSelfieBody');
            if (inSelfieBody) {
                if (data.check_in_image) {
                    inSelfieBody.innerHTML = `<a href="${data.check_in_image}" target="_blank" title="Click to open full size"><img src="${data.check_in_image}" alt="Check-In Selfie" class="img-thumbnail rounded-3 shadow-xs" style="max-height: 150px; object-fit: cover;"></a>`;
                } else if (data.check_in && data.is_selfie_expired) {
                    inSelfieBody.innerHTML = '<span class="badge bg-light text-muted border"><i class="bi bi-trash me-1"></i>Auto-deleted after 45 days</span>';
                } else {
                    inSelfieBody.innerHTML = '<span class="text-muted">No selfie captured</span>';
                }
            }

            // Check-Out Details
            const outTimeEl = document.getElementById('modalOutTime');
            const outBadgeEl = document.getElementById('modalOutBadge');
            const outAddrEl = document.getElementById('modalOutAddress');
            const outCoordsEl = document.getElementById('modalOutCoords');
            const outMapBtn = document.getElementById('modalOutMapBtn');
            const outMapContainer = document.getElementById('modalOutMapBtnContainer');

            if (data.check_out) {
                outTimeEl.textContent = data.check_out;
                outTimeEl.className = 'fs-5 fw-bold text-primary';
                if (data.is_auto_checkout) {
                    outBadgeEl.textContent = 'Auto Logged Out (07:00 PM)';
                    outBadgeEl.className = 'badge bg-warning text-dark';
                } else {
                    outBadgeEl.textContent = 'Checked Out';
                    outBadgeEl.className = 'badge bg-primary';
                }
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
                outBadgeEl.textContent = 'Active / Working';
                outBadgeEl.className = 'badge bg-light text-dark border';
                outAddrEl.textContent = 'Punch out not registered yet';
                outCoordsEl.textContent = 'Lat: -- | Lng: --';
                outMapContainer.style.display = 'none';
            }

            const outSelfieBody = document.getElementById('modalOutSelfieBody');
            if (outSelfieBody) {
                if (data.check_out_image) {
                    outSelfieBody.innerHTML = `<a href="${data.check_out_image}" target="_blank" title="Click to open full size"><img src="${data.check_out_image}" alt="Check-Out Selfie" class="img-thumbnail rounded-3 shadow-xs" style="max-height: 150px; object-fit: cover;"></a>`;
                } else if (data.is_auto_checkout) {
                    outSelfieBody.innerHTML = '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">Auto Logged Out (No Selfie)</span>';
                } else if (data.check_out && data.is_selfie_expired) {
                    outSelfieBody.innerHTML = '<span class="badge bg-light text-muted border"><i class="bi bi-trash me-1"></i>Auto-deleted after 45 days</span>';
                } else {
                    outSelfieBody.innerHTML = '<span class="text-muted">No selfie captured</span>';
                }
            }

            // Populate Admin override form
            document.getElementById('editEmpId').value = data.id;
            document.getElementById('editStatus').value = data.status === 'Not Marked' ? 'Present' : data.status;
            document.getElementById('editInTime').value = data.check_in_time || '';
            document.getElementById('editOutTime').value = data.check_out_time || '';

            // Show modal
            detailModal.show();
        });
    });

    // Single Attendance Override submission via AJAX or Form submit
    document.getElementById('saveSingleAttendanceBtn').addEventListener('click', function () {
        const empId = document.getElementById('editEmpId').value;
        const status = document.getElementById('editStatus').value;
        const inTime = document.getElementById('editInTime').value;
        const outTime = document.getElementById('editOutTime').value;
        const date = "{{ $date }}";
        const token = "{{ csrf_token() }}";

        const formData = new FormData();
        formData.append('_token', token);
        formData.append('date', date);
        formData.append(`attendance[${empId}]`, status);
        formData.append(`check_in[${empId}]`, inTime);
        formData.append(`check_out[${empId}]`, outTime);

        fetch("{{ route('attendance.store') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(() => {
            window.location.reload();
        })
        .catch(err => {
            console.error(err);
            window.location.reload();
        });
    });
});
</script>
@endsection