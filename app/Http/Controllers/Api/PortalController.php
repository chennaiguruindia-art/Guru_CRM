<?php

namespace App\Http\Controllers\Api;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Payslip;
use App\Services\AttendanceRuleService;
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

        $today = now('Asia/Kolkata')->toDateString();
        $todayAttendance = null;
        $monthAttendances = collect();
        $presentDays = 0;
        $halfDays = 0;
        $absentDays = 0;
        $leaveDays = 0;

        if ($employee) {
            AttendanceRuleService::autoCheckOutPendingAttendances($employee->id);

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

        return $this->successResponse([
            'employee' => $employee ? $employee->load('branch') : null,
            'today_attendance' => $todayAttendance,
            'has_checked_in' => $todayAttendance && !empty($todayAttendance->check_in),
            'has_checked_out' => $todayAttendance && !empty($todayAttendance->check_out),
            'stats' => [
                'month' => now('Asia/Kolkata')->format('F Y'),
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

        $today = $request->input('date') ?? now('Asia/Kolkata')->toDateString();
        $nowTime = $request->input('punch_time') ?? $request->input('time') ?? now('Asia/Kolkata')->format('H:i:s');

        $attendance = Attendance::firstOrNew([
            'employee_id' => $employee->id,
            'date' => $today,
        ]);

        if ($attendance->check_in) {
            return $this->errorResponse('You have already punched in today at ' . Carbon::parse($attendance->check_in, 'Asia/Kolkata')->format('h:i A'), 422, [
                'attendance' => $attendance,
            ]);
        }

        // Validate Check-In time windows (Up to 10:30 AM OR 01:30 PM - 02:00 PM)
        $windowCheck = AttendanceRuleService::isCheckInAllowed($nowTime);
        if (! $windowCheck['allowed']) {
            return $this->errorResponse($windowCheck['message'], 422, [
                'window' => $windowCheck['window'],
                'current_time' => $windowCheck['current_time'],
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
            'message' => 'Punched in successfully at ' . now('Asia/Kolkata')->format('h:i A') . '! Location captured.',
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

        $today = $request->input('date') ?? now('Asia/Kolkata')->toDateString();
        $nowTime = $request->input('punch_time') ?? $request->input('time') ?? now('Asia/Kolkata')->format('H:i:s');

        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if (! $attendance || ! $attendance->check_in) {
            return $this->errorResponse('You must punch in first before punching out.', 422);
        }

        if ($attendance->check_out) {
            return $this->errorResponse('You have already punched out today at ' . Carbon::parse($attendance->check_out, 'Asia/Kolkata')->format('h:i A'), 422, [
                'attendance' => $attendance,
            ]);
        }

        // Calculate worked duration and evaluate status & permission consumption
        $eval = AttendanceRuleService::evaluateCheckOut($attendance, $nowTime);

        $attendance->check_out = $nowTime;
        $attendance->worked_hours = $eval['worked_hours'];
        $attendance->status = $eval['status'];
        $attendance->has_permission = $eval['has_permission'];
        $attendance->permission_reason = $eval['permission_reason'];

        if ($request->filled('latitude')) {
            $attendance->check_out_latitude = $request->input('latitude');
        }
        if ($request->filled('longitude')) {
            $attendance->check_out_longitude = $request->input('longitude');
        }
        if ($request->filled('address')) {
            $attendance->check_out_address = $request->input('address');
        }
        $combinedNotes = $eval['notes'];
        if ($request->filled('notes')) {
            $combinedNotes .= ' | ' . $request->input('notes');
        }
        $attendance->notes = ($attendance->notes ? $attendance->notes . ' | ' : '') . $combinedNotes;

        $attendance->save();

        AuditLogger::log('punch_out', 'attendances', $attendance->id, null, [
            'employee' => $employee->name,
            'time' => $nowTime,
            'worked_hours' => $eval['worked_hours'],
            'status' => $eval['status'],
            'has_permission' => $eval['has_permission'],
            'latitude' => $attendance->check_out_latitude,
            'longitude' => $attendance->check_out_longitude,
        ]);

        $feedbackMsg = 'Punched out successfully at ' . now('Asia/Kolkata')->format('h:i A') . '!';
        $feedbackMsg .= " Worked: {$eval['worked_hours']} hrs &middot; Marked as: {$eval['status']}";
        if ($eval['has_permission']) {
            $feedbackMsg .= ' (Permission applied: ' . ($eval['used_permissions_before'] + 1) . '/2 used this month).';
        } elseif ($eval['permission_reason']) {
            $feedbackMsg .= " ({$eval['permission_reason']}).";
        }

        return $this->successResponse([
            'attendance' => $attendance->fresh(),
            'evaluation' => $eval,
            'message' => $feedbackMsg,
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

    /**
     * Get employee profile (official company-controlled + personal info).
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        $employee = $user->employee ?? Employee::with('branch')->where('user_id', $user->id)->first();

        if (! $employee) {
            return $this->errorResponse('No active employee profile linked to this account.', 422);
        }

        return $this->successResponse([
            'user' => $user,
            'employee' => $employee->load('branch'),
        ], 'Employee profile retrieved successfully');
    }

    /**
     * Update employee editable personal details.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        $employee = $user->employee ?? Employee::where('user_id', $user->id)->first();

        if (! $employee) {
            return $this->errorResponse('No active employee profile linked to this account.', 422);
        }

        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'dob' => ['nullable', 'date'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'emergency_contact' => ['nullable', 'string', 'max:50'],
            'emergency_contact_relation' => ['nullable', 'string', 'max:50'],
            'marital_status' => ['nullable', 'string', 'in:Single,Married,Other'],
            'spouse_name' => ['nullable', 'string', 'max:100'],
        ]);

        $employee->update($validated);

        if (! empty($validated['phone']) && $user->phone !== $validated['phone']) {
            $user->phone = $validated['phone'];
        }
        if (! empty($validated['email']) && $user->email !== $validated['email']) {
            $user->email = $validated['email'];
        }
        $user->save();

        AuditLogger::log('api_update_profile', 'employees', $employee->id, null, [
            'employee' => $employee->name,
            'updated_by' => $user->name,
        ]);

        return $this->successResponse([
            'user' => $user->fresh(),
            'employee' => $employee->fresh()->load('branch'),
        ], 'Personal profile updated successfully');
    }

    /**
     * Get employee payslips list and monthly statement calculation.
     */
    public function payslips(Request $request): JsonResponse
    {
        $user = $request->user();
        $employee = $user->employee ?? Employee::with('branch')->where('user_id', $user->id)->first();

        if (! $employee) {
            return $this->errorResponse('No active employee profile linked to this account.', 422);
        }

        $selectedMonth = $request->input('month', now('Asia/Kolkata')->format('Y-m'));
        $currentPayslip = \App\Http\Controllers\PortalController::getPayslipForMonth($employee, $selectedMonth);

        // Build list of recent 12 months for selector
        $availableMonths = [];
        $currentDate = now('Asia/Kolkata')->startOfMonth();
        for ($i = 0; $i < 12; $i++) {
            $mKey = $currentDate->format('Y-m');
            $mLabel = $currentDate->format('F Y');
            $availableMonths[] = [
                'key' => $mKey,
                'label' => $mLabel,
                'is_current' => ($mKey === now('Asia/Kolkata')->format('Y-m')),
            ];
            $currentDate->subMonth();
        }

        $allPayslips = Payslip::where('employee_id', $employee->id)
            ->orderBy('salary_month', 'desc')
            ->get();

        return $this->successResponse([
            'employee' => $employee->load('branch'),
            'selected_month' => $selectedMonth,
            'available_months' => $availableMonths,
            'current_payslip' => $currentPayslip ? array_merge($currentPayslip->toArray(), [
                'net_salary_in_words' => $currentPayslip->net_salary_in_words,
            ]) : null,
            'all_payslips' => $allPayslips,
        ], 'Employee payslips loaded successfully');
    }

    /**
     * Get standalone printable HTML payslip for mobile app / browser view.
     */
    public function downloadPayslip(Request $request): JsonResponse
    {
        $user = $request->user();
        $employee = $user->employee ?? Employee::with('branch')->where('user_id', $user->id)->first();

        if (! $employee) {
            return $this->errorResponse('No active employee profile linked to this account.', 422);
        }

        $month = $request->input('month', now('Asia/Kolkata')->format('Y-m'));
        $payslip = \App\Http\Controllers\PortalController::getPayslipForMonth($employee, $month);

        if (! $payslip) {
            return $this->errorResponse('Payslip not available for ' . $month, 404);
        }

        $html = view('portal.payslip-print', compact('employee', 'payslip'))->render();

        return $this->successResponse([
            'filename' => 'Payslip_' . ($employee->employee_code ?: 'EMP') . '_' . $month . '.html',
            'html' => $html,
            'month' => $month,
            'month_name' => $payslip->month_name,
        ], 'Printable payslip HTML generated');
    }
}
