@extends('layouts.app')
@section('title', 'Attendance')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-calendar-check-fill text-success me-2"></i>Staff Attendance</h4>
        <p class="text-muted small mb-0">Record and monitor daily staff and labour attendance.</p>
    </div>
    <form method="GET" action="{{ route('attendance.index') }}" class="d-flex gap-2">
        <input type="date" name="date" class="form-control form-control-sm" value="{{ $date }}" onchange="this.form.submit()">
    </form>
</div>

{{-- KPI cards --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-4">
        <div class="card border-0 shadow-sm p-3">
            <span class="text-muted small">Present Today</span>
            <h3 class="fw-bold mb-0 text-success">{{ $presentCount }}</h3>
        </div>
    </div>
    <div class="col-12 col-sm-4">
        <div class="card border-0 shadow-sm p-3">
            <span class="text-muted small">Absent</span>
            <h3 class="fw-bold mb-0 text-danger">{{ $absentCount }}</h3>
        </div>
    </div>
    <div class="col-12 col-sm-4">
        <div class="card border-0 shadow-sm p-3">
            <span class="text-muted small">On Leave</span>
            <h3 class="fw-bold mb-0 text-warning">{{ $leaveCount }}</h3>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('attendance.store') }}">
    @csrf
    <input type="hidden" name="date" value="{{ $date }}">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold">Attendance for {{ \Carbon\Carbon::parse($date)->format('d F Y (l)') }}</h6>
            <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-check2 me-1"></i>Save Attendance</button>
        </div>
        <div class="table-responsive">
            <table class="table table-crm align-middle mb-0">
                <thead>
                    <tr>
                        <th>Emp Code</th>
                        <th>Employee Name</th>
                        <th>Designation</th>
                        <th>Department</th>
                        <th style="width: 320px;">Mark Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $emp)
                    @php $curr = $attendances->get($emp->id)?->status ?? 'Present'; @endphp
                    <tr>
                        <td class="fw-semibold text-success">{{ $emp->employee_code }}</td>
                        <td class="fw-semibold">{{ $emp->name }}</td>
                        <td>{{ $emp->designation }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $emp->department }}</span></td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <input type="radio" class="btn-check" name="attendance[{{ $emp->id }}]" id="pres_{{ $emp->id }}" value="Present" @checked($curr === 'Present')>
                                <label class="btn btn-outline-success" for="pres_{{ $emp->id }}">Present</label>

                                <input type="radio" class="btn-check" name="attendance[{{ $emp->id }}]" id="half_{{ $emp->id }}" value="Half-Day" @checked($curr === 'Half-Day')>
                                <label class="btn btn-outline-warning" for="half_{{ $emp->id }}">Half-Day</label>

                                <input type="radio" class="btn-check" name="attendance[{{ $emp->id }}]" id="abs_{{ $emp->id }}" value="Absent" @checked($curr === 'Absent')>
                                <label class="btn btn-outline-danger" for="abs_{{ $emp->id }}">Absent</label>

                                <input type="radio" class="btn-check" name="attendance[{{ $emp->id }}]" id="leave_{{ $emp->id }}" value="Leave" @checked($curr === 'Leave')>
                                <label class="btn btn-outline-secondary" for="leave_{{ $emp->id }}">Leave</label>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            No active employees found. <a href="{{ route('employees.create') }}">Add employees first</a>.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($employees->count())
        <div class="card-footer bg-white py-3 text-end">
            <button type="submit" class="btn btn-success"><i class="bi bi-check2 me-1"></i>Save Attendance</button>
        </div>
        @endif
    </div>
</form>
@endsection
