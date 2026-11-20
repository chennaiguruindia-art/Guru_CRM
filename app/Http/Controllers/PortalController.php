<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalController extends Controller
{
    /**
     * Dedicated Punch Screen: Check In / Check Out
     */
    public function punch(Request $request): View
    {
        $user = $request->user();
        $employee = $user->employee ?? Employee::with('branch')->where('user_id', $user->id)->first();

        $today = now('Asia/Kolkata')->toDateString();
        $todayAttendance = null;
        $hasCheckedIn = false;
        $hasCheckedOut = false;

        if ($employee) {
            $todayAttendance = Attendance::where('employee_id', $employee->id)
                ->where('date', $today)
                ->first();

            $hasCheckedIn = $todayAttendance && !empty($todayAttendance->check_in);
            $hasCheckedOut = $todayAttendance && !empty($todayAttendance->check_out);
        }

        return view('portal.punch', compact(
            'employee',
            'todayAttendance',
            'hasCheckedIn',
            'hasCheckedOut'
        ));
    }

    /**
     * Employee HRM Portal Dashboard
     */
    public function dashboard(Request $request): View
    {
        $user = $request->user();
        $employee = $user->employee ?? Employee::with('branch')->where('user_id', $user->id)->first();

        $today = now('Asia/Kolkata')->toDateString();
        $todayAttendance = null;
        $monthAttendances = collect();
        $presentDays = 0;
        $halfDays = 0;
        $absentDays = 0;
        $leaveDays = 0;

        if ($employee) {
            $todayAttendance = Attendance::where('employee_id', $employee->id)
                ->where('date', $today)
                ->first();

            $startOfMonth = now('Asia/Kolkata')->startOfMonth()->toDateString();
            $endOfMonth = now('Asia/Kolkata')->endOfMonth()->toDateString();

            $monthAttendances = Attendance::where('employee_id', $employee->id)
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->orderBy('date', 'desc')
                ->get();

            $presentDays = $monthAttendances->where('status', 'Present')->count();
            $halfDays = $monthAttendances->where('status', 'Half-Day')->count();
            $absentDays = $monthAttendances->where('status', 'Absent')->count();
            $leaveDays = $monthAttendances->where('status', 'Leave')->count();
        }

        return view('portal.dashboard', compact(
            'employee',
            'todayAttendance',
            'monthAttendances',
            'presentDays',
            'halfDays',
            'absentDays',
            'leaveDays'
        ));
    }

    /**
     * Self Punch In
     */
    public function punchIn(Request $request): RedirectResponse
    {
        $user = $request->user();
        $employee = $user->employee ?? Employee::where('user_id', $user->id)->first();

        if (! $employee) {
            return back()->with('error', 'No active employee profile linked to your account.');
        }

        $today = now('Asia/Kolkata')->toDateString();
        $nowTime = now('Asia/Kolkata')->format('H:i:s');

        $attendance = Attendance::firstOrNew([
            'employee_id' => $employee->id,
            'date' => $today,
        ]);

        if ($attendance->check_in) {
            return back()->with('info', 'You have already punched in today at ' . Carbon::parse($attendance->check_in, 'Asia/Kolkata')->format('h:i A'));
        }

        $attendance->status = 'Present';
        $attendance->check_in = $nowTime;

        if ($request->filled('latitude')) {
            $attendance->check_in_latitude = $request->input('latitude');
        }
        if ($request->filled('longitude')) {
            $attendance->check_in_longitude = $request->input('longitude');
        }
        if ($request->filled('address')) {
            $attendance->check_in_address = $request->input('address');
        }

        $attendance->save();

        AuditLogger::log('punch_in', 'attendances', $attendance->id, null, [
            'employee' => $employee->name,
            'time' => $nowTime,
            'latitude' => $attendance->check_in_latitude,
            'longitude' => $attendance->check_in_longitude,
        ]);

        return back()->with('success', 'Punched in successfully at ' . now('Asia/Kolkata')->format('h:i A') . '! Live location captured.');
    }

    /**
     * Self Punch Out
     */
    public function punchOut(Request $request): RedirectResponse
    {
        $user = $request->user();
        $employee = $user->employee ?? Employee::where('user_id', $user->id)->first();

        if (! $employee) {
            return back()->with('error', 'No active employee profile linked to your account.');
        }

        $today = now('Asia/Kolkata')->toDateString();
        $nowTime = now('Asia/Kolkata')->format('H:i:s');

        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if (! $attendance || ! $attendance->check_in) {
            return back()->with('error', 'You must punch in first before punching out.');
        }

        $attendance->check_out = $nowTime;

        if ($request->filled('latitude')) {
            $attendance->check_out_latitude = $request->input('latitude');
        }
        if ($request->filled('longitude')) {
            $attendance->check_out_longitude = $request->input('longitude');
        }
        if ($request->filled('address')) {
            $attendance->check_out_address = $request->input('address');
        }

        $attendance->save();

        AuditLogger::log('punch_out', 'attendances', $attendance->id, null, [
            'employee' => $employee->name,
            'time' => $nowTime,
            'latitude' => $attendance->check_out_latitude,
            'longitude' => $attendance->check_out_longitude,
        ]);

        return back()->with('success', 'Punched out successfully at ' . now('Asia/Kolkata')->format('h:i A') . '! Live location captured.');
    }

    /**
     * Full attendance history for this employee
     */
    public function attendance(Request $request): View
    {
        $user = $request->user();
        $employee = $user->employee ?? Employee::with('branch')->where('user_id', $user->id)->first();

        $selectedMonth = $request->input('month', now()->format('Y-m'));
        $dateObj = Carbon::createFromFormat('Y-m', $selectedMonth) ?: now();
        $startOfMonth = $dateObj->copy()->startOfMonth()->toDateString();
        $endOfMonth = $dateObj->copy()->endOfMonth()->toDateString();

        $attendances = collect();
        if ($employee) {
            $attendances = Attendance::where('employee_id', $employee->id)
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->orderBy('date', 'desc')
                ->paginate(31)
                ->withQueryString();
        }

        return view('portal.attendance', compact('employee', 'attendances', 'selectedMonth'));
    }
}
