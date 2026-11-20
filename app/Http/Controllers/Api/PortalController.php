<?php

namespace App\Http\Controllers\Api;

use App\Models\Attendance;
use App\Models\Employee;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortalController extends BaseApiController
{
    /**
     * Get employee HRM portal dashboard summary.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $employee = $user->employee ?? Employee::with('branch')->where('user_id', $user->id)->first();

        $today = now()->toDateString();
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

            $startOfMonth = now()->startOfMonth()->toDateString();
            $endOfMonth = now()->endOfMonth()->toDateString();

            $monthAttendances = Attendance::where('employee_id', $employee->id)
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->orderBy('date', 'desc')
                ->get();

            $presentDays = $monthAttendances->where('status', 'Present')->count();
            $halfDays = $monthAttendances->where('status', 'Half-Day')->count();
            $absentDays = $monthAttendances->where('status', 'Absent')->count();
            $leaveDays = $monthAttendances->where('status', 'Leave')->count();
        }

        return $this->successResponse([
            'employee' => $employee ? $employee->load('branch') : null,
            'today_attendance' => $todayAttendance,
            'has_checked_in' => $todayAttendance && !empty($todayAttendance->check_in),
            'has_checked_out' => $todayAttendance && !empty($todayAttendance->check_out),
            'stats' => [
                'month' => now()->format('F Y'),
                'present_days' => $presentDays,
                'half_days' => $halfDays,
                'absent_days' => $absentDays,
                'leave_days' => $leaveDays,
                'total_working_days' => $monthAttendances->count(),
            ],
            'recent_attendances' => $monthAttendances->take(10)->values(),
        ], 'Employee portal dashboard loaded successfully');
    }

    /**
     * Punch In (Start Shift)
     */
    public function punchIn(Request $request): JsonResponse
    {
        $user = $request->user();
        $employee = $user->employee ?? Employee::where('user_id', $user->id)->first();

        if (! $employee) {
            return $this->errorResponse('No active employee profile is linked to your user account. Please contact your Branch Admin.', 422);
        }

        $today = now()->toDateString();
        $nowTime = now()->format('H:i:s');

        $attendance = Attendance::firstOrNew([
            'employee_id' => $employee->id,
            'date' => $today,
        ]);

        if ($attendance->check_in) {
            return $this->errorResponse('You have already punched in today at ' . Carbon::parse($attendance->check_in)->format('h:i A'), 422, [
                'attendance' => $attendance,
            ]);
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
        if ($request->filled('notes')) {
            $attendance->notes = $request->input('notes');
        }

        $attendance->save();

        AuditLogger::log('punch_in', 'attendances', $attendance->id, null, [
            'employee' => $employee->name,
            'time' => $nowTime,
            'latitude' => $attendance->check_in_latitude,
            'longitude' => $attendance->check_in_longitude,
        ]);

        return $this->successResponse([
            'attendance' => $attendance->fresh(),
            'message' => 'Punched in successfully at ' . now()->format('h:i A') . '! Location captured.',
        ], 'Punched in successfully');
    }

    /**
     * Punch Out (End Shift)
     */
    public function punchOut(Request $request): JsonResponse
    {
        $user = $request->user();
        $employee = $user->employee ?? Employee::where('user_id', $user->id)->first();

        if (! $employee) {
            return $this->errorResponse('No active employee profile is linked to your user account.', 422);
        }

        $today = now()->toDateString();
        $nowTime = now()->format('H:i:s');

        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if (! $attendance || ! $attendance->check_in) {
            return $this->errorResponse('You must punch in first before punching out.', 422);
        }

        if ($attendance->check_out) {
            return $this->errorResponse('You have already punched out today at ' . Carbon::parse($attendance->check_out)->format('h:i A'), 422, [
                'attendance' => $attendance,
            ]);
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
        if ($request->filled('notes')) {
            $attendance->notes = ($attendance->notes ? $attendance->notes . ' | ' : '') . $request->input('notes');
        }

        $attendance->save();

        AuditLogger::log('punch_out', 'attendances', $attendance->id, null, [
            'employee' => $employee->name,
            'time' => $nowTime,
            'latitude' => $attendance->check_out_latitude,
            'longitude' => $attendance->check_out_longitude,
        ]);

        return $this->successResponse([
            'attendance' => $attendance->fresh(),
            'message' => 'Punched out successfully at ' . now()->format('h:i A') . '! Location captured.',
        ], 'Punched out successfully');
    }

    /**
     * Full attendance history for this employee with pagination.
     */
    public function attendance(Request $request): JsonResponse
    {
        $user = $request->user();
        $employee = $user->employee ?? Employee::with('branch')->where('user_id', $user->id)->first();

        if (! $employee) {
            return $this->errorResponse('No active employee profile linked to this account.', 422);
        }

        $selectedMonth = $request->input('month', now()->format('Y-m'));
        $dateObj = Carbon::createFromFormat('Y-m', $selectedMonth) ?: now();
        $startOfMonth = $dateObj->copy()->startOfMonth()->toDateString();
        $endOfMonth = $dateObj->copy()->endOfMonth()->toDateString();

        $query = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->orderBy('date', 'desc');

        $perPage = min((int) $request->input('per_page', 31), 100);
        $paginator = $query->paginate($perPage);

        // Also calculate monthly summary
        $allMonth = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Personal attendance history retrieved',
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'summary' => [
                'month' => $selectedMonth,
                'present' => $allMonth->where('status', 'Present')->count(),
                'half_day' => $allMonth->where('status', 'Half-Day')->count(),
                'absent' => $allMonth->where('status', 'Absent')->count(),
                'leave' => $allMonth->where('status', 'Leave')->count(),
            ],
        ]);
    }
}
