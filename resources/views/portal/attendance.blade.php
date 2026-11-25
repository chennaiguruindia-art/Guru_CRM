@extends('layouts.portal')
@section('title', 'My Attendance')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark">
            <i class="bi bi-calendar2-check-fill text-success me-2"></i>My Attendance Records
        </h4>
        <p class="text-muted small mb-0">View your daily attendance logs and punch history.</p>
    </div>

    <!-- Month Filter -->
    <form method="GET" action="{{ route('portal.attendance') }}" class="d-flex align-items-center gap-2">
        <label for="monthSelect" class="small fw-semibold text-secondary text-nowrap">Filter Month:</label>
        <input type="month" id="monthSelect" name="month" class="form-control form-control-sm" value="{{ $selectedMonth }}" onchange="this.form.submit()">
    </form>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold text-dark">
            Attendance Log for {{ \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->format('F Y') }}
        </h6>
        @if($employee)
            <span class="badge bg-light text-dark border">
                {{ $employee->employee_code }} &middot; {{ $employee->name }}
            </span>
        @endif
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Day</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Total Hours</th>
                    <th>Status</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $item)
                    @php
                        $duration = '—';
                        if ($item->check_in && $item->check_out) {
                            $in = \Carbon\Carbon::parse($item->check_in);
                            $out = \Carbon\Carbon::parse($item->check_out);
                            $mins = $in->diffInMinutes($out);
                            $hours = floor($mins / 60);
                            $remainder = $mins % 60;
                            $duration = "{$hours}h {$remainder}m";
                        }
                    @endphp
                    <tr>
                        <td class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($item->date)->format('d M Y') }}</td>
                        <td class="text-muted">{{ \Carbon\Carbon::parse($item->date)->format('l') }}</td>
                        <td>
                            @if($item->check_in)
                                <span class="badge bg-light text-dark border">
                                    <i class="bi bi-clock me-1 text-success"></i>{{ \Carbon\Carbon::parse($item->check_in)->format('h:i A') }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($item->check_out)
                                <span class="badge bg-light text-dark border">
                                    <i class="bi bi-clock me-1 text-danger"></i>{{ \Carbon\Carbon::parse($item->check_out)->format('h:i A') }}
                                </span>
                                @if($item->is_auto_checkout)
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle ms-1" style="font-size: 0.72rem;" title="Employee did not logout — Auto logged out at 07:00 PM">
                                        <i class="bi bi-clock-history me-1"></i>Auto Out
                                    </span>
                                @endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="fw-semibold text-secondary">{{ $duration }}</td>
                        <td>
                            @if($item->status === 'Present')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Present</span>
                            @elseif($item->status === 'Half-Day')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Half-Day</span>
                            @elseif($item->status === 'Leave')
                                <span class="badge bg-info-subtle text-info border border-info-subtle">Leave</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Absent</span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $item->notes ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-calendar-x fs-3 d-block mb-2 text-secondary"></i>
                            No attendance records found for this period.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($attendances, 'hasPages') && $attendances->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $attendances->links() }}
        </div>
    @endif
</div>
@endsection
