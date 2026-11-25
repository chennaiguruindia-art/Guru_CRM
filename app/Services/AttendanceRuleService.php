<?php

namespace App\Services;

use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceRuleService
{
    /**
     * Check if Check-In is permitted at the specified time.
     * Allowed:
     * 1. Morning: Up to 10:30 AM
     * 2. Afternoon: 01:30 PM (13:30) to 02:00 PM (14:00)
     */
    public static function isCheckInAllowed(?string $timeString = null): array
    {
        $now = now('Asia/Kolkata');
        if ($timeString) {
            try {
                $checkTime = Carbon::parse($timeString, 'Asia/Kolkata')->format('H:i');
            } catch (\Throwable $e) {
                $checkTime = $now->format('H:i');
            }
        } else {
            $checkTime = $now->format('H:i');
        }

        // Window 1: Morning up to 10:30 AM
        if ($checkTime <= '10:30') {
            return [
                'allowed' => true,
                'window' => 'morning',
                'current_time' => $checkTime,
                'message' => 'Morning check-in window is open (closes at 10:30 AM).',
            ];
        }

        // Window 2: Afternoon 01:30 PM to 02:00 PM (13:30 to 14:00)
        if ($checkTime >= '13:30' && $checkTime <= '14:00') {
            return [
                'allowed' => true,
                'window' => 'afternoon',
                'current_time' => $checkTime,
                'message' => 'Afternoon check-in window is open (closes at 02:00 PM).',
            ];
        }

        // Check-in blocked outside windows
        if ($checkTime > '10:30' && $checkTime < '13:30') {
            return [
                'allowed' => false,
                'window' => 'closed',
                'current_time' => $checkTime,
                'message' => 'Check-in is currently closed. Morning window ended at 10:30 AM. Next check-in window opens at 01:30 PM (until 02:00 PM).',
            ];
        }

        return [
            'allowed' => false,
            'window' => 'closed',
            'current_time' => $checkTime,
            'message' => 'Check-in is closed for today. Check-in was permitted up to 10:30 AM or between 01:30 PM and 02:00 PM.',
        ];
    }

    /**
     * Compute worked hours and determine attendance status and permission usage upon check out.
     */
    public static function evaluateCheckOut(Attendance $attendance, string $checkOutTimeString): array
    {
        $attendanceDate = Carbon::parse($attendance->date)->toDateString();

        $parseDateTime = function ($val, $defaultDate) {
            if ($val instanceof Carbon) {
                return $val->copy()->setTimezone('Asia/Kolkata');
            }
            $str = trim((string) $val);
            if (preg_match('/^\d{4}-\d{2}-\d{2}/', $str)) {
                return Carbon::parse($str, 'Asia/Kolkata');
            }
            return Carbon::parse($defaultDate . ' ' . $str, 'Asia/Kolkata');
        };

        $checkInDateTime = $parseDateTime($attendance->check_in, $attendanceDate);
        $checkOutDateTime = $parseDateTime($checkOutTimeString, $attendanceDate);

        // If checkout crossed midnight or timestamp is earlier than checkin on same day
        if ($checkOutDateTime->lt($checkInDateTime)) {
            $checkOutDateTime->addDay();
        }

        $totalMinutes = max(0, $checkInDateTime->diffInMinutes($checkOutDateTime));
        $workedHours = round($totalMinutes / 60, 2);

        // Count permissions already used by this employee in the month of this attendance record
        $monthCarbon = Carbon::parse($attendance->date, 'Asia/Kolkata');
        $startOfMonth = $monthCarbon->copy()->startOfMonth()->toDateString();
        $endOfMonth = $monthCarbon->copy()->endOfMonth()->toDateString();
        $monthName = $monthCarbon->format('F Y');

        $usedPermissions = Attendance::where('employee_id', $attendance->employee_id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->where('id', '!=', $attendance->id)
            ->where('has_permission', true)
            ->count();

        $status = 'Present';
        $hasPermission = false;
        $permissionReason = null;
        $statusNote = '';

        if ($workedHours >= 9.0) {
            // >= 9 hours = Full Day Present
            $status = 'Present';
            $hasPermission = false;
            $permissionReason = null;
            $statusNote = "Present: Completed {$workedHours} hrs (9+ hrs requirement met)";
        } elseif ($workedHours >= 8.0) {
            // 8.0 to < 9.0 hours: Permission rule (up to 2 per month)
            if ($usedPermissions < 2) {
                $status = 'Present';
                $hasPermission = true;
                $permissionNumber = $usedPermissions + 1;
                $permissionReason = "Permission #{$permissionNumber} of 2 for {$monthName}";
                $statusNote = "Present with Permission: Worked {$workedHours} hrs (Permission {$permissionNumber}/2 used)";
            } else {
                // More than 2 permissions not allowed -> Demoted to Half-Day
                $status = 'Half-Day';
                $hasPermission = false;
                $permissionReason = "Permission limit exceeded (2 of 2 already used for {$monthName})";
                $statusNote = "Half-Day: Worked {$workedHours} hrs (More than 2 permissions not allowed)";
            }
        } elseif ($workedHours >= 4.5) {
            // 4.5 to < 8.0 hours = Half-Day
            $status = 'Half-Day';
            $hasPermission = false;
            $permissionReason = null;
            $statusNote = "Half-Day: Worked {$workedHours} hrs (Between 4.5 hrs and 8.0 hrs)";
        } else {
            // < 4.5 hours = Absent
            $status = 'Absent';
            $hasPermission = false;
            $permissionReason = null;
            $statusNote = "Absent: Worked {$workedHours} hrs (Less than 4.5 hrs minimum requirement)";
        }

        return [
            'worked_hours' => $workedHours,
            'status' => $status,
            'has_permission' => $hasPermission,
            'permission_reason' => $permissionReason,
            'notes' => $statusNote,
            'used_permissions_before' => $usedPermissions,
        ];
    }

    /**
     * Auto check-out employees who haven't logged out by 7:00 PM (19:00 IST).
     * If current time is past 19:00:
     * - Automatically logs out / punches out the employee.
     * - Check-out time is recorded as 07:00 PM (19:00:00).
     * - Records is_auto_checkout = true.
     * - Sets note: "Employee did not logout — System automatically logged out at 07:00 PM."
     * - Calculates worked duration, status (Present/Half-Day/Absent) & permissions.
     * - Updates monthly attendance summary.
     */
    public static function autoCheckOutPendingAttendances(?int $employeeId = null, ?Carbon $referenceTime = null, bool $force = false): array
    {
        $now = $referenceTime ?: now('Asia/Kolkata');
        $todayDate = $now->toDateString();
        $currentTime = $now->format('H:i');

        $query = Attendance::whereNotNull('check_in')
            ->whereNull('check_out');

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        // Target attendances on or before today
        $query->where('date', '<=', $todayDate);

        $pending = $query->get();
        $processed = [];

        foreach ($pending as $attendance) {
            $attDate = Carbon::parse($attendance->date)->toDateString();

            // If it is today's attendance, only auto-logout if current time is 19:00 (7:00 PM) or later, unless forced
            if ($attDate === $todayDate && !$force && $currentTime < '19:00') {
                continue; // Still working hours; wait for employee to logout until 7:00 PM
            }

            // Auto check out at 19:00:00 (7:00 PM)
            $checkOutTime = '19:00:00';
            $eval = self::evaluateCheckOut($attendance, $checkOutTime);

            $autoNote = "Employee did not logout — System automatically logged out at 07:00 PM.";
            $combinedNotes = $attendance->notes 
                ? trim($attendance->notes . ' | ' . $autoNote) 
                : $autoNote;

            $attendance->update([
                'check_out' => $checkOutTime,
                'status' => $eval['status'],
                'worked_hours' => $eval['worked_hours'],
                'has_permission' => $eval['has_permission'],
                'permission_reason' => $eval['permission_reason'],
                'is_auto_checkout' => true,
                'auto_checkout_at' => $now,
                'check_out_address' => $attendance->check_out_address ?: 'Auto Logged Out (System at 07:00 PM)',
                'notes' => $combinedNotes,
            ]);

            // Sync monthly attendance record
            $salaryMonth = Carbon::parse($attendance->date)->format('Y-m');
            try {
                \App\Models\EmployeeMonthlyAttendance::syncForMonth($salaryMonth);
            } catch (\Throwable $e) {
                // Ignore sync errors during batch
            }

            $processed[] = [
                'attendance_id' => $attendance->id,
                'employee_id' => $attendance->employee_id,
                'date' => $attDate,
                'status' => $eval['status'],
                'worked_hours' => $eval['worked_hours'],
                'is_auto_checkout' => true,
            ];
        }

        return $processed;
    }
}
