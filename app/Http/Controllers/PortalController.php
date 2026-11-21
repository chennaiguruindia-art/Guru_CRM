<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Payslip;
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
        $recentAttendances = collect();
        $presentDays = 0;
        $halfDays = 0;
        $absentDays = 0;
        $leaveDays = 0;
        $attendancePercentage = 100;
        $currentPayslip = null;
        $profileCompletion = 0;

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
            'profileCompletion'
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

        $selectedMonth = $request->input('month', now('Asia/Kolkata')->format('Y-m'));
        $dateObj = Carbon::createFromFormat('Y-m', $selectedMonth, 'Asia/Kolkata') ?: now('Asia/Kolkata');
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
     * Helper to retrieve or automatically generate a complete payslip for a given month
     */
    private function getPayslipForMonth(?Employee $employee, string $salaryMonth): ?Payslip
    {
        if (! $employee) {
            return null;
        }

        // Try to load existing persisted payslip first
        $payslip = Payslip::where('employee_id', $employee->id)
            ->where('salary_month', $salaryMonth)
            ->first();

        if ($payslip) {
            return $payslip;
        }

        // Generate dynamically based on employee salary and attendance for this month
        $salary = (float) ($employee->salary > 0 ? $employee->salary : 20000.00);

        try {
            $date = Carbon::createFromFormat('Y-m', $salaryMonth, 'Asia/Kolkata');
        } catch (\Throwable $e) {
            $date = now('Asia/Kolkata');
            $salaryMonth = $date->format('Y-m');
        }

        $startOfMonth = $date->copy()->startOfMonth()->toDateString();
        $endOfMonth = $date->copy()->endOfMonth()->toDateString();
        $totalDays = $date->daysInMonth;

        // Query attendances for the month
        $atts = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get();

        $absentCount = $atts->where('status', 'Absent')->count();
        $halfDayCount = $atts->where('status', 'Half-Day')->count();

        $lopDays = (float) ($absentCount + ($halfDayCount * 0.5));
        $paidDays = max(0, $totalDays - $lopDays);

        // Earnings
        $perDay = $totalDays > 0 ? ($salary / $totalDays) : 0;
        $lopDeduction = round($lopDays * $perDay, 2);

        $basic = round($salary * 0.50, 2); // 50%
        $hra = round($salary * 0.30, 2);   // 30%
        $conveyance = round($salary * 0.10, 2); // 10%
        $special = round($salary - ($basic + $hra + $conveyance), 2); // balance 10%
        $gross = $salary;

        // Deductions
        $pf = round(min($basic * 0.12, 1800.00), 2); // 12% PF capped at statutory 1800
        $esi = ($salary <= 21000) ? round($salary * 0.0075, 2) : 0.00; // 0.75% if eligible
        $pt = 200.00; // standard Professional Tax

        $totalDeductions = round($pf + $esi + $pt + $lopDeduction, 2);
        $netSalary = max(0, round($gross - $totalDeductions, 2));

        return Payslip::create([
            'employee_id' => $employee->id,
            'salary_month' => $salaryMonth,
            'month_name' => $date->format('F Y'),
            'basic_salary' => $basic,
            'hra' => $hra,
            'conveyance_allowance' => $conveyance,
            'special_allowance' => $special,
            'gross_salary' => $gross,
            'pf_deduction' => $pf,
            'esi_deduction' => $esi,
            'professional_tax' => $pt,
            'lop_days' => $lopDays,
            'lop_deduction' => $lopDeduction,
            'other_deductions' => 0,
            'total_deductions' => $totalDeductions,
            'net_salary' => $netSalary,
            'total_working_days' => $totalDays,
            'paid_days' => $paidDays,
            'payment_status' => 'Paid',
            'payment_date' => $date->copy()->endOfMonth()->toDateString(),
            'remarks' => 'Monthly Salary for ' . $date->format('F Y'),
        ]);
    }
}
