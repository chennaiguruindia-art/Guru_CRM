<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HrmDashboardController extends Controller
{
    /**
     * Dedicated Admin HRM Dashboard
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $today = now()->toDateString();

        $branches = $user && $user->canSeeAllBranches()
            ? Branch::where('status', 'active')->orderBy('name')->get()
            : collect();

        $selectedBranchId = null;
        if ($user && $user->canSeeAllBranches()) {
            if ($request->filled('branch_id')) {
                $selectedBranchId = (int) $request->input('branch_id');
            }
        } elseif ($user) {
            $selectedBranchId = $user->branchId();
        }

        // 1. Employee query with Branch Scoping
        $employeeQuery = Employee::with('branch')->where('status', 'active');

        if ($user && ! $user->canSeeAllBranches()) {
            if ($selectedBranchId === null) {
                $employeeQuery->whereRaw('1 = 0');
            } else {
                $employeeQuery->where('branch_id', $selectedBranchId);
            }
        } elseif ($selectedBranchId) {
            $employeeQuery->where('branch_id', $selectedBranchId);
        }

        $employees = $employeeQuery->orderBy('name')->get();
        $employeeIds = $employees->pluck('id');
        $totalStaff = $employees->count();
        $totalPayrollEstimate = $employees->sum('salary');
        $deptCounts = $employees->groupBy('department')->map->count();

        // 2. Period and Date Resolution
        $period = $request->input('period', 'today');
        $selectedDate = $request->input('date', now()->toDateString());
        $selectedMonth = $request->input('month', now()->format('Y-m'));

        $isRangePeriod = in_array($period, ['week', 'month']);
        $targetDate = $today;
        $startDate = null;
        $endDate = null;

        if ($period === 'date') {
            $targetDate = $selectedDate ?: $today;
        } elseif ($period === 'week') {
            $startDate = now()->startOfWeek()->toDateString();
            $endDate = now()->endOfWeek()->toDateString();
        } elseif ($period === 'month') {
            $monthObj = Carbon::createFromFormat('Y-m', $selectedMonth ?: now()->format('Y-m')) ?: now();
            $startDate = $monthObj->copy()->startOfMonth()->toDateString();
            $endDate = $monthObj->copy()->endOfMonth()->toDateString();
        }

        // 3. Attendance Query
        if ($isRangePeriod) {
            $rangeAttendances = Attendance::with('employee')
                ->whereIn('employee_id', $employeeIds)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();

            $presentToday = $rangeAttendances->where('status', 'Present')->count();
            $halfDayToday = $rangeAttendances->where('status', 'Half-Day')->count();
            $absentToday  = $rangeAttendances->where('status', 'Absent')->count();
            $leaveToday   = $rangeAttendances->where('status', 'Leave')->count();
            $pendingPunch = $halfDayToday; // in range view, pending punch is not a single day snapshot
            $totalShifts  = $presentToday + $halfDayToday + $absentToday + $leaveToday;
            $attendanceRate = $totalShifts > 0 ? round((($presentToday + ($halfDayToday * 0.5)) / $totalShifts) * 100) : 0;
            $todayAttendances = $rangeAttendances->keyBy('employee_id');

            $livePunches = Attendance::with(['employee.branch'])
                ->whereIn('employee_id', $employeeIds)
                ->whereBetween('date', [$startDate, $endDate])
                ->whereNotNull('check_in')
                ->latest('date')
                ->latest('updated_at')
                ->take(15)
                ->get();
        } else {
            $todayAttendances = Attendance::with('employee')
                ->whereIn('employee_id', $employeeIds)
                ->where('date', $targetDate)
                ->get()
                ->keyBy('employee_id');

            $presentToday = $todayAttendances->where('status', 'Present')->count();
            $halfDayToday = $todayAttendances->where('status', 'Half-Day')->count();
            $absentToday  = $todayAttendances->where('status', 'Absent')->count();
            $leaveToday   = $todayAttendances->where('status', 'Leave')->count();
            $pendingPunch = max(0, $totalStaff - ($presentToday + $halfDayToday + $absentToday + $leaveToday));
            $attendanceRate = $totalStaff > 0 ? round((($presentToday + ($halfDayToday * 0.5)) / $totalStaff) * 100) : 0;

            $livePunches = Attendance::with(['employee.branch'])
                ->whereIn('employee_id', $employeeIds)
                ->where('date', $targetDate)
                ->whereNotNull('check_in')
                ->latest('updated_at')
                ->take(15)
                ->get();
        }

        return view('hrm.dashboard', compact(
            'employees',
            'todayAttendances',
            'totalStaff',
            'presentToday',
            'halfDayToday',
            'absentToday',
            'leaveToday',
            'pendingPunch',
            'attendanceRate',
            'totalPayrollEstimate',
            'deptCounts',
            'livePunches',
            'branches',
            'today',
            'period',
            'selectedDate',
            'selectedMonth',
            'selectedBranchId',
            'isRangePeriod',
            'targetDate',
            'startDate',
            'endDate'
        ));
    }

    /**
     * HRM Monthly Attendance Report
     */
    public function report(Request $request): View
    {
        $user = $request->user();
        $selectedMonth = $request->input('month', now()->format('Y-m'));
        $dateObj = Carbon::createFromFormat('Y-m', $selectedMonth) ?: now();
        $startOfMonth = $dateObj->copy()->startOfMonth()->toDateString();
        $endOfMonth = $dateObj->copy()->endOfMonth()->toDateString();

        $employeeQuery = Employee::with('branch')->where('status', 'active');

        if ($user && ! $user->canSeeAllBranches()) {
            $branchId = $user->branchId();
            if ($branchId === null) {
                $employeeQuery->whereRaw('1 = 0');
            } else {
                $employeeQuery->where('branch_id', $branchId);
            }
        } elseif ($request->filled('branch_id')) {
            $employeeQuery->where('branch_id', $request->input('branch_id'));
        }

        $employees = $employeeQuery->orderBy('name')->get();
        $employeeIds = $employees->pluck('id');

        $attendances = Attendance::whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get()
            ->groupBy('employee_id');

        $branches = $user && $user->canSeeAllBranches()
            ? Branch::where('status', 'active')->orderBy('name')->get()
            : collect();

        return view('hrm.report', compact('employees', 'attendances', 'selectedMonth', 'branches'));
    }
}
