<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Payslip;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HrmPayslipController extends Controller
{
    /**
     * Display the Admin Monthly Payslips management screen.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // 1. Branch Scoping
        $canSeeAll = $user && $user->canSeeAllBranches();
        $branches = $canSeeAll
            ? Branch::where('status', 'active')->orderBy('name')->get()
            : collect();

        if (! $canSeeAll) {
            $selectedBranchId = $user ? $user->branchId() : null;
        } else {
            $selectedBranchId = $request->filled('branch_id')
                ? (int) $request->input('branch_id')
                : ($branches->first()->id ?? null);
        }

        // 2. Fetch Employees for selected branch
        $employeeQuery = Employee::with('branch')->where('status', 'active');
        if ($selectedBranchId) {
            $employeeQuery->where('branch_id', $selectedBranchId);
        }
        $employees = $employeeQuery->orderBy('name')->get();

        // 3. Selected Employee & Month
        $selectedEmployeeId = $request->filled('employee_id')
            ? (int) $request->input('employee_id')
            : ($employees->first()->id ?? null);

        $selectedEmployee = $employees->firstWhere('id', $selectedEmployeeId);

        $selectedMonth = $request->input('month', now('Asia/Kolkata')->format('Y-m'));
        try {
            $monthCarbon = Carbon::createFromFormat('Y-m', $selectedMonth, 'Asia/Kolkata');
        } catch (\Throwable $e) {
            $monthCarbon = now('Asia/Kolkata');
            $selectedMonth = $monthCarbon->format('Y-m');
        }

        // 4. Retrieve or calculate payslip for the selected employee & month
        $payslip = null;
        $isExisting = false;

        if ($selectedEmployee) {
            $payslip = Payslip::where('employee_id', $selectedEmployee->id)
                ->where('salary_month', $selectedMonth)
                ->first();

            if ($payslip) {
                $isExisting = true;
            } else {
                // Generate auto-calculated proposal based on employee's base salary & attendance
                $payslip = $this->calculateProposedPayslip($selectedEmployee, $selectedMonth);
            }
        }

        // 5. Recent / Generated payslips for this month (scoped to selected branch if applicable)
        $monthPayslipsQuery = Payslip::with(['employee.branch'])
            ->where('salary_month', $selectedMonth);

        if ($selectedBranchId) {
            $monthPayslipsQuery->whereHas('employee', function ($q) use ($selectedBranchId) {
                $q->where('branch_id', $selectedBranchId);
            });
        }

        $recentPayslips = $monthPayslipsQuery->latest('updated_at')->get();

        return view('hrm.payslips', compact(
            'branches',
            'canSeeAll',
            'selectedBranchId',
            'employees',
            'selectedEmployeeId',
            'selectedEmployee',
            'selectedMonth',
            'payslip',
            'isExisting',
            'recentPayslips'
        ));
    }

    /**
     * Store or update a specific month's payslip for an employee.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'salary_month' => 'required|regex:/^\d{4}-\d{2}$/',
            'basic_salary' => 'required|numeric|min:0',
            'da' => 'nullable|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'leave_encashment' => 'nullable|numeric|min:0',
            'other_allowance' => 'nullable|numeric|min:0',
            'gross_salary' => 'required|numeric|min:0',
            'pf_deduction' => 'nullable|numeric|min:0',
            'esi_deduction' => 'nullable|numeric|min:0',
            'professional_tax' => 'nullable|numeric|min:0',
            'lop_days' => 'nullable|numeric|min:0',
            'lop_deduction' => 'nullable|numeric|min:0',
            'other_deductions' => 'nullable|numeric|min:0',
            'total_deductions' => 'required|numeric|min:0',
            'net_salary' => 'required|numeric|min:0',
            'total_working_days' => 'required|integer|min:1|max:31',
            'paid_days' => 'required|numeric|min:0|max:31',
            'payment_status' => 'required|string|in:Paid,In Progress,Draft,Processing,Pending',
            'payment_date' => 'nullable|date',
            'remarks' => 'nullable|string|max:500',
        ]);

        try {
            $monthDate = Carbon::createFromFormat('Y-m', $validated['salary_month'], 'Asia/Kolkata');
            $monthName = $monthDate->format('F Y');
        } catch (\Throwable $e) {
            $monthName = $validated['salary_month'];
        }

        $employee = Employee::findOrFail($validated['employee_id']);

        $payslip = Payslip::updateOrCreate([
            'employee_id' => $validated['employee_id'],
            'salary_month' => $validated['salary_month'],
        ], [
            'month_name' => $monthName,
            'basic_salary' => (float) $validated['basic_salary'],
            'da' => (float) ($validated['da'] ?? 0),
            'bonus' => (float) ($validated['bonus'] ?? 0),
            'leave_encashment' => (float) ($validated['leave_encashment'] ?? 0),
            'other_allowance' => (float) ($validated['other_allowance'] ?? 0),
            'gross_salary' => (float) $validated['gross_salary'],
            'pf_deduction' => (float) ($validated['pf_deduction'] ?? 0),
            'esi_deduction' => (float) ($validated['esi_deduction'] ?? 0),
            'professional_tax' => (float) ($validated['professional_tax'] ?? 0),
            'lop_days' => (float) ($validated['lop_days'] ?? 0),
            'lop_deduction' => (float) ($validated['lop_deduction'] ?? 0),
            'other_deductions' => (float) ($validated['other_deductions'] ?? 0),
            'total_deductions' => (float) $validated['total_deductions'],
            'net_salary' => (float) $validated['net_salary'],
            'total_working_days' => (int) $validated['total_working_days'],
            'paid_days' => (float) $validated['paid_days'],
            'payment_status' => $validated['payment_status'],
            'payment_date' => $validated['payment_date'] ?: ($validated['payment_status'] === 'Paid' ? now()->toDateString() : null),
            'remarks' => $validated['remarks'],
            'is_manual' => true,
        ]);

        return redirect()->route('hrm.payslips', [
            'branch_id' => $employee->branch_id,
            'employee_id' => $employee->id,
            'month' => $validated['salary_month'],
        ])->with('success', "Payslip for {$employee->name} ({$monthName}) saved successfully! Employee portal will now show these exact figures.");
    }

    /**
     * Print / View statutory Form XXVIII payslip as Admin.
     */
    public function print(Employee $employee, string $month): View
    {
        $payslip = Payslip::where('employee_id', $employee->id)
            ->where('salary_month', $month)
            ->first();

        if (! $payslip) {
            $payslip = $this->calculateProposedPayslip($employee, $month);
        }

        return view('portal.payslip-print', compact('employee', 'payslip'));
    }

    /**
     * Delete a saved payslip (reverting back to live calculation).
     */
    public function destroy(Payslip $payslip): RedirectResponse
    {
        $employee = $payslip->employee;
        $month = $payslip->salary_month;
        $payslip->delete();

        return redirect()->route('hrm.payslips', [
            'branch_id' => $employee?->branch_id,
            'employee_id' => $employee?->id,
            'month' => $month,
        ])->with('success', "Saved payslip for {$employee?->name} ({$month}) has been deleted. System will revert to auto-attendance calculation.");
    }

    /**
     * Helper to compute proposed payslip data from employee profile and attendance logs.
     */
    private function calculateProposedPayslip(Employee $employee, string $salaryMonth): Payslip
    {
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

        // Prorating logic for joining date
        $joiningDate = $employee->joining_date ? Carbon::parse($employee->joining_date, 'Asia/Kolkata') : null;
        $effectiveStartDay = 1;
        $preJoiningDays = 0;

        if ($joiningDate && $joiningDate->format('Y-m') === $salaryMonth) {
            $effectiveStartDay = min($joiningDate->day, $totalDaysInMonth);
            $preJoiningDays = $effectiveStartDay - 1;
        }

        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->keyBy(fn ($a) => Carbon::parse($a->date)->toDateString());

        $absentDays = 0.0;
        $paidSundays = 0;
        $workedDays = 0;

        for ($day = $effectiveStartDay; $day <= $totalDaysInMonth; $day++) {
            $currentDayDate = $date->copy()->day($day)->startOfDay();
            $dateKey = $currentDayDate->toDateString();

            if ($currentDayDate->isSunday()) {
                // All Sundays are automatic paid holidays (treated as Present)!
                $paidSundays++;
                continue;
            }

            $att = $attendances->get($dateKey);
            if ($att) {
                if ($att->status === 'Present') {
                    $workedDays += 1;
                } elseif ($att->status === 'Half-Day') {
                    $workedDays += 0.5;
                    $absentDays += 0.5;
                } elseif ($att->status === 'Leave') {
                    $workedDays += 1;
                } elseif ($att->status === 'Absent') {
                    $absentDays += 1.0;
                }
            }
        }

        $monthEnded = now('Asia/Kolkata')->greaterThanOrEqualTo($endOfMonth->copy()->endOfDay());
        $eligibleDays = max(0, $totalDaysInMonth - $preJoiningDays);

        // Total Days Present = Actual punches (worked days + approved leave) + Sundays (paid holidays)
        $totalDaysPresent = max(0, (float) ($workedDays + $paidSundays));

        // LOP is strictly considered by number of absent days:
        $totalLopDays = (float) ($preJoiningDays + $absentDays);

        $monthlyGrossSalary = (float) ($employee->salary > 0 ? $employee->salary : 20000.00);
        $basic = round(((float) ($employee->basic_salary ?: $monthlyGrossSalary)), 2);
        $da = round(((float) ($employee->da ?? 0)), 2);
        $bonus = round(((float) ($employee->bonus ?? 0)), 2);
        $leaveEncashment = round(((float) ($employee->leave_encashment ?? 0)), 2);
        $other = round(((float) ($employee->other_allowance ?? 0)), 2);
        $gross = round($basic + $da + $bonus + $leaveEncashment + $other, 2);

        $dailyRate = $totalDaysInMonth > 0 ? ($gross / $totalDaysInMonth) : 0;
        $lopDeduction = round($totalLopDays * $dailyRate, 2);

        // Statutory Deductions
        if ($employee->custom_pf_deduction !== null && $employee->custom_pf_deduction > 0) {
            $pf = round(((float) $employee->custom_pf_deduction), 2);
        } else {
            $pf = round(min($basic * 0.12, 1800.00), 2);
        }

        if ($employee->custom_esi_deduction !== null && $employee->custom_esi_deduction > 0) {
            $esi = round(((float) $employee->custom_esi_deduction), 2);
        } else {
            $esi = ($gross <= 21000) ? round($gross * 0.0075, 2) : 0.00;
        }

        if ($employee->custom_pt_deduction !== null && $employee->custom_pt_deduction > 0) {
            $pt = round(((float) $employee->custom_pt_deduction), 2);
        } else {
            $pt = 200.00;
        }

        $totalDeductions = round($pf + $esi + $pt + $lopDeduction, 2);
        $netSalary = max(0, round($gross - $totalDeductions, 2));

        $remarks = $preJoiningDays > 0
            ? 'Joining Month: Started from ' . $joiningDate->format('d M Y') . " ({$totalDaysPresent} days present in tenure)"
            : "Full Month Statement ({$totalDaysInMonth} calendar days, {$totalDaysPresent} days present)";

        $p = new Payslip();
        $p->employee_id = $employee->id;
        $p->salary_month = $salaryMonth;
        $p->month_name = $date->format('F Y');
        $p->basic_salary = $basic;
        $p->da = $da;
        $p->bonus = $bonus;
        $p->leave_encashment = $leaveEncashment;
        $p->other_allowance = $other;
        $p->gross_salary = $gross;
        $p->pf_deduction = $pf;
        $p->esi_deduction = $esi;
        $p->professional_tax = $pt;
        $p->lop_days = $totalLopDays;
        $p->lop_deduction = $lopDeduction;
        $p->other_deductions = 0.00;
        $p->total_deductions = $totalDeductions;
        $p->net_salary = $netSalary;
        $p->total_working_days = $totalDaysInMonth;
        $p->paid_days = $totalDaysPresent;
        $p->payment_status = $monthEnded ? 'Paid' : 'In Progress';
        $p->payment_date = $monthEnded ? $endOfMonth->toDateString() : null;
        $p->remarks = $remarks;
        $p->is_manual = false;

        return $p;
    }
}
