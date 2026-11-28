<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Payslip;
use App\Services\AttendanceRuleService;
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
            $this->ensureProperRole($user, $employee);

            // Auto check-out if past 07:00 PM and employee did not logout
            AttendanceRuleService::autoCheckOutPendingAttendances($employee->id);

            $todayAttendance = Attendance::where('employee_id', $employee->id)
                ->where('date', $today)
                ->first();

            $hasCheckedIn = $todayAttendance && !empty($todayAttendance->check_in);
            $hasCheckedOut = $todayAttendance && !empty($todayAttendance->check_out);
        }

        $checkInWindow = AttendanceRuleService::isCheckInAllowed();

        return view('portal.punch', compact(
            'employee',
            'todayAttendance',
            'hasCheckedIn',
            'hasCheckedOut',
            'checkInWindow'
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
        $recentAttendances = collect();
        $presentDays = 0;
        $halfDays = 0;
        $absentDays = 0;
        $leaveDays = 0;
        $attendancePercentage = 100;
        $currentPayslip = null;
        $profileCompletion = 0;

        if ($employee) {
            $this->ensureProperRole($user, $employee);

            // Auto check-out if past 07:00 PM and employee did not logout
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

            $recentAttendances = Attendance::where('employee_id', $employee->id)
                ->orderBy('date', 'desc')
                ->take(7)
                ->get();

            $presentDays = $monthAttendances->where('status', 'Present')->count();
            $halfDays = $monthAttendances->where('status', 'Half-Day')->count();
            $absentDays = $monthAttendances->where('status', 'Absent')->count();
            $leaveDays = $monthAttendances->where('status', 'Leave')->count();

            $totalMarked = $presentDays + $halfDays + $absentDays + $leaveDays;
            if ($totalMarked > 0) {
                $attendancePercentage = round((($presentDays + ($halfDays * 0.5)) / $totalMarked) * 100);
            }

            // Calculate profile completeness score
            $checkFields = [
                $employee->phone,
                $employee->email,
                $employee->address,
                $employee->dob,
                $employee->blood_group,
                $employee->emergency_contact,
                $employee->emergency_contact_relation,
                $employee->bank_name,
                $employee->bank_account_number,
            ];
            $filled = count(array_filter($checkFields, fn($v) => !empty($v)));
            $profileCompletion = round(($filled / count($checkFields)) * 100);

            // Latest Month Payslip preview
            $currentMonth = now('Asia/Kolkata')->format('Y-m');
            $currentPayslip = $this->getPayslipForMonth($employee, $currentMonth);
        }

        $checkInWindow = AttendanceRuleService::isCheckInAllowed();

        return view('portal.dashboard', compact(
            'employee',
            'todayAttendance',
            'monthAttendances',
            'recentAttendances',
            'presentDays',
            'halfDays',
            'absentDays',
            'leaveDays',
            'attendancePercentage',
            'currentPayslip',
            'profileCompletion',
            'checkInWindow'
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

        // Check if Check-In time window is currently allowed (Up to 10:30 AM OR 1:30 PM - 2:00 PM)
        $windowCheck = AttendanceRuleService::isCheckInAllowed($nowTime);
        if (! $windowCheck['allowed']) {
            return back()->with('error', $windowCheck['message']);
        }

        // Capture & store mandatory live selfie
        $selfieInput = $request->file('selfie') ?? $request->input('selfie_data');
        $selfiePath = AttendanceRuleService::storeSelfieImage($employee->id, 'in', $today, $selfieInput);
        if (! $selfiePath) {
            return back()->with('error', 'Live selfie is required to Punch In. Please capture your selfie and try again.');
        }

        $attendance->status = 'Present';
        $attendance->check_in = $nowTime;
        $attendance->check_in_image = $selfiePath;

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
            'has_selfie' => true,
        ]);

        return back()->with('success', 'Punched in successfully at ' . now('Asia/Kolkata')->format('h:i A') . '! Live location & selfie captured.');
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

        // Capture & store mandatory live selfie
        $selfieInput = $request->file('selfie') ?? $request->input('selfie_data');
        $selfiePath = AttendanceRuleService::storeSelfieImage($employee->id, 'out', $today, $selfieInput);
        if (! $selfiePath) {
            return back()->with('error', 'Live selfie is required to Punch Out. Please capture your selfie and try again.');
        }

        // Calculate worked hours and determine attendance status & permission consumption
        $eval = AttendanceRuleService::evaluateCheckOut($attendance, $nowTime);

        $attendance->check_out = $nowTime;
        $attendance->check_out_image = $selfiePath;
        $attendance->worked_hours = $eval['worked_hours'];
        $attendance->status = $eval['status'];
        $attendance->has_permission = $eval['has_permission'];
        $attendance->permission_reason = $eval['permission_reason'];
        if ($eval['notes']) {
            $attendance->notes = ($attendance->notes ? $attendance->notes . ' | ' : '') . $eval['notes'];
        }

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

        return back()->with('success', $feedbackMsg);
    }

    /**
     * Full attendance history for this employee
     */
    public function attendance(Request $request): View
    {
        $user = $request->user();
        $employee = $user->employee ?? Employee::with('branch')->where('user_id', $user->id)->first();

        $selectedMonth = $request->input('month', now('Asia/Kolkata')->format('Y-m'));
        $dateObj = Carbon::createFromFormat('Y-m', $selectedMonth, 'Asia/Kolkata') ?: now('Asia/Kolkata');
        $startOfMonth = $dateObj->copy()->startOfMonth()->toDateString();
        $endOfMonth = $dateObj->copy()->endOfMonth()->toDateString();

        $attendances = collect();
        if ($employee) {
            AttendanceRuleService::autoCheckOutPendingAttendances($employee->id);

            $attendances = Attendance::where('employee_id', $employee->id)
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->orderBy('date', 'desc')
                ->paginate(31)
                ->withQueryString();
        }

        return view('portal.attendance', compact('employee', 'attendances', 'selectedMonth'));
    }

    /**
     * View Employee Profile (Company Locked vs Editable Personal Info)
     */
    public function profile(Request $request): View
    {
        $user = $request->user();
        $employee = $user->employee ?? Employee::with('branch')->where('user_id', $user->id)->first();

        return view('portal.profile', compact('user', 'employee'));
    }

    /**
     * Update Employee's Editable Personal Details Only
     * Locked fields (Employee Code, Branch, Designation, Department, Salary, Status, Joining Date) CANNOT be altered.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();
        $employee = $user->employee ?? Employee::where('user_id', $user->id)->first();

        if (! $employee) {
            return back()->with('error', 'No active employee profile linked to your account.');
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
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_ifsc' => ['nullable', 'string', 'max:20'],
            'pan_number' => ['nullable', 'string', 'max:20'],
            'uan_number' => ['nullable', 'string', 'max:30'],
        ]);

        // Update employee personal fields only
        $employee->update($validated);

        // Keep linked user's phone/email in sync if provided
        if (! empty($validated['phone']) && $user->phone !== $validated['phone']) {
            $user->phone = $validated['phone'];
        }
        if (! empty($validated['email']) && $user->email !== $validated['email']) {
            $user->email = $validated['email'];
        }
        $user->save();

        AuditLogger::log('update_profile', 'employees', $employee->id, null, [
            'employee' => $employee->name,
            'updated_by' => $user->name,
        ]);

        return back()->with('success', 'Your personal details have been updated successfully!');
    }

    /**
     * Employee Payslips List & Month Selector
     */
    public function payslips(Request $request): View
    {
        $user = $request->user();
        $employee = $user->employee ?? Employee::with('branch')->where('user_id', $user->id)->first();

        $selectedMonth = $request->input('month', now('Asia/Kolkata')->format('Y-m'));
        $currentPayslip = null;
        $allPayslips = collect();

        // Build list of recent 12 months for selector
        $availableMonths = [];
        $currentDate = now('Asia/Kolkata')->startOfMonth();
        for ($i = 0; $i < 12; $i++) {
            $mKey = $currentDate->format('Y-m');
            $mLabel = $currentDate->format('F Y');
            $availableMonths[$mKey] = $mLabel;
            $currentDate->subMonth();
        }

        if ($employee) {
            $currentPayslip = $this->getPayslipForMonth($employee, $selectedMonth);
            $allPayslips = Payslip::where('employee_id', $employee->id)
                ->orderBy('salary_month', 'desc')
                ->get();
        }

        return view('portal.payslips', compact(
            'employee',
            'currentPayslip',
            'allPayslips',
            'selectedMonth',
            'availableMonths'
        ));
    }

    /**
     * Standalone / Print view of a specific month's payslip
     */
    public function showPayslip(Request $request, string $month): View
    {
        $user = $request->user();
        $employee = $user->employee ?? Employee::with('branch')->where('user_id', $user->id)->first();

        if (! $employee) {
            abort(404, 'Employee record not found.');
        }

        $payslip = $this->getPayslipForMonth($employee, $month);
        if (! $payslip) {
            abort(404, 'Payslip could not be generated for ' . $month);
        }

        return view('portal.payslip-print', compact('employee', 'payslip'));
    }

    /**
     * Helper to retrieve or automatically generate a complete payslip for a given month.
     * Implements:
     * 1. Prorated days for mid-month joinees (effective from joining date to end of month)
     * 2. Full calendar cycle (Day 1 to Last Day) for subsequent months
     * 3. All Sundays automatically treated as Paid Holidays (0 LOP, count as Present)
     * 4. Automated Loss of Pay (LOP) calculated from Monday-Saturday attendance punches
     * 5. Custom Admin salary split applied (Basic, HRA, Allowances) or statutory auto-split
     */
    public static function getPayslipForMonth(?Employee $employee, string $salaryMonth): ?Payslip
    {
        if (! $employee) {
            return null;
        }

        try {
            $date = Carbon::createFromFormat('Y-m', $salaryMonth, 'Asia/Kolkata');
        } catch (\Throwable $e) {
            $date = now('Asia/Kolkata');
            $salaryMonth = $date->format('Y-m');
        }

        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();
        $totalDaysInMonth = $date->daysInMonth;
        $isCurrentMonth = ($salaryMonth === now('Asia/Kolkata')->format('Y-m'));

        // If payslip is already manually customized/saved by Admin, or past month, return it directly
        $existingPayslip = Payslip::where('employee_id', $employee->id)
            ->where('salary_month', $salaryMonth)
            ->first();

        if ($existingPayslip && ($existingPayslip->is_manual || ! $isCurrentMonth) && ! request()->boolean('refresh')) {
            return $existingPayslip;
        }

        // Determine Effective Start Day based on Joining Date
        // If employee joined this month (e.g. joined Nov 21): eligible days start from Nov 21 to Nov 30 (10 days)
        // If employee joined earlier: eligible days start from Day 1 to Last Day of month (full cycle)
        $joiningDate = $employee->joining_date ? Carbon::parse($employee->joining_date, 'Asia/Kolkata') : null;
        $effectiveStartDay = 1;
        $preJoiningDays = 0;

        if ($joiningDate && $joiningDate->format('Y-m') === $salaryMonth) {
            $effectiveStartDay = min($joiningDate->day, $totalDaysInMonth);
            $preJoiningDays = $effectiveStartDay - 1;
        } elseif ($joiningDate && $joiningDate->gt($endOfMonth)) {
            // Employee joins in a future month — not yet on payroll for this month
            return null;
        }

        $eligibleDays = max(0, $totalDaysInMonth - $preJoiningDays);

        // Fetch attendance logs for this employee in this month
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->keyBy(fn ($a) => Carbon::parse($a->date)->toDateString());

        // Evaluate day-by-day:
        // - All Sundays = Automatic Paid Holiday (0 LOP, treated as Present)
        // - Monday-Saturday = Evaluated from attendance punches / HR records
        $lopDays = 0.0;
        $paidSundays = 0;
        $workedDays = 0;
        $absentDays = 0.0;
        $todayDate = now('Asia/Kolkata')->startOfDay();

        for ($day = $effectiveStartDay; $day <= $totalDaysInMonth; $day++) {
            $currentDayDate = $date->copy()->day($day)->startOfDay();
            $dateKey = $currentDayDate->toDateString();
            $isSunday = $currentDayDate->isSunday();

            if ($isSunday) {
                // All Sundays are automatic paid holidays (treated as Present)!
                $paidSundays++;
                continue;
            }

            // Weekday (Monday - Saturday)
            $att = $attendances->get($dateKey);
            if ($att) {
                if ($att->status === 'Present') {
                    $workedDays += 1;
                } elseif ($att->status === 'Half-Day') {
                    $workedDays += 0.5;
                    $absentDays += 0.5;
                } elseif ($att->status === 'Leave') {
                    // Approved paid leave
                    $workedDays += 1;
                } elseif ($att->status === 'Absent') {
                    $absentDays += 1.0;
                }
            }
        }

        $monthEnded = now('Asia/Kolkata')->greaterThanOrEqualTo($endOfMonth->copy()->endOfDay());
        $paymentStatus = $monthEnded ? 'Paid' : 'In Progress';

        // Total Days Present = Actual punches (worked days + approved leave) + Sundays (paid holidays)
        $totalDaysPresent = max(0, (float) ($workedDays + $paidSundays));

        // LOP Days = Calendar Days in Month - Total Days Present
        $totalLopDays = max(0, (float) ($totalDaysInMonth - $totalDaysPresent));

        // Monthly Salary & Component Breakdown (Form XXVIII: Basic, DA, Bonus, Leave Encashment, Other Allowance)
        $monthlyGrossSalary = (float) ($employee->salary > 0 ? $employee->salary : 20000.00);

        // Read exact components set on employee:
        $basic = round(((float) ($employee->basic_salary ?: $monthlyGrossSalary)), 2);
        $da = round(((float) ($employee->da ?? 0)), 2);
        $bonus = round(((float) ($employee->bonus ?? 0)), 2);
        $leaveEncashment = round(((float) ($employee->leave_encashment ?? 0)), 2);
        $other = round(((float) ($employee->other_allowance ?? 0)), 2);
        $gross = round($basic + $da + $bonus + $leaveEncashment + $other, 2);

        // LOP Amount Calculation:
        $dailyRate = $totalDaysInMonth > 0 ? ($gross / $totalDaysInMonth) : 0;
        $lopDeduction = round($totalLopDays * $dailyRate, 2);

        // Deductions (PF, ESI, Prof. Tax, LOP)
        // 1. Employee PF: Custom override or standard 12% Basic capped at statutory 1800
        if ($employee->custom_pf_deduction !== null && $employee->custom_pf_deduction > 0) {
            $pf = round(((float) $employee->custom_pf_deduction), 2);
        } else {
            $pf = round(min($basic * 0.12, 1800.00), 2);
        }

        // 2. Employee ESI: Custom override or standard 0.75% if eligible
        if ($employee->custom_esi_deduction !== null && $employee->custom_esi_deduction > 0) {
            $esi = round(((float) $employee->custom_esi_deduction), 2);
        } else {
            $esi = ($gross <= 21000) ? round($gross * 0.0075, 2) : 0.00;
        }

        // 3. Professional Tax (PT): Custom override or standard 200.00
        if ($employee->custom_pt_deduction !== null && $employee->custom_pt_deduction > 0) {
            $pt = round(((float) $employee->custom_pt_deduction), 2);
        } else {
            $pt = 200.00;
        }

        $totalDeductions = round($pf + $esi + $pt + $lopDeduction, 2);
        $netSalary = max(0, round($gross - $totalDeductions, 2));

        $remarks = $preJoiningDays > 0
            ? 'Joining Month: Started from ' . $joiningDate->format('d M Y') . ' (' . $totalDaysPresent . ' days present in tenure)'
            : 'Full Month Statement (' . $totalDaysInMonth . ' calendar days, ' . $totalDaysPresent . ' days present)';

        if ($isCurrentMonth && ! $monthEnded) {
            $remarks .= ' • Month in Progress: Final attendance & LOP amount will be calculated on month end (' . $endOfMonth->format('d M Y') . ')';
        }

        return Payslip::updateOrCreate([
            'employee_id' => $employee->id,
            'salary_month' => $salaryMonth,
        ], [
            'month_name' => $date->format('F Y'),
            'basic_salary' => $basic,
            'da' => $da,
            'bonus' => $bonus,
            'leave_encashment' => $leaveEncashment,
            'hra' => 0.00,
            'conveyance_allowance' => 0.00,
            'special_allowance' => 0.00,
            'other_allowance' => $other,
            'gross_salary' => $gross,
            'pf_deduction' => $pf,
            'esi_deduction' => $esi,
            'professional_tax' => $pt,
            'lop_days' => $totalLopDays,
            'lop_deduction' => $lopDeduction,
            'other_deductions' => 0,
            'total_deductions' => $totalDeductions,
            'net_salary' => $netSalary,
            'total_working_days' => $totalDaysInMonth,
            'paid_days' => $totalDaysPresent,
            'payment_status' => $paymentStatus,
            'payment_date' => $endOfMonth->toDateString(),
            'remarks' => $remarks,
        ]);
    }

    /**
     * Ensure office/digital employees do not inadvertently hold CRM fieldmarketer role
     */
    private function ensureProperRole($user, $employee): void
    {
        if ($user && $employee && $user->hasRole('fieldmarketer')) {
            $desig = strtolower($employee->designation ?? '');
            if (str_contains($desig, 'digital') || str_contains($desig, 'web') || str_contains($desig, 'seo') || str_contains($desig, 'content')) {
                $employeeRole = \App\Models\Role::where('slug', 'employee')->first();
                if ($employeeRole) {
                    $user->roles()->sync([$employeeRole->id]);
                    $user->load('roles');
                }
            }
        }
    }
}
