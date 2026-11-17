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

        // 1. Employee query with Branch Scoping
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

        // 2. Attendance query for Today
        $todayAttendances = Attendance::with('employee')
            ->whereIn('employee_id', $employeeIds)
            ->where('date', $today)
            ->get()
            ->keyBy('employee_id');

        $totalStaff = $employees->count();
        $presentToday = $todayAttendances->where('status', 'Present')->count();
        $halfDayToday = $todayAttendances->where('status', 'Half-Day')->count();
        $absentToday = $todayAttendances->where('status', 'Absent')->count();
        $leaveToday = $todayAttendances->where('status', 'Leave')->count();
        $pendingPunch = max(0, $totalStaff - ($presentToday + $halfDayToday + $absentToday + $leaveToday));
        $attendanceRate = $totalStaff > 0 ? round((($presentToday + ($halfDayToday * 0.5)) / $totalStaff) * 100) : 0;
        $totalPayrollEstimate = $employees->sum('salary');

        // 3. Department breakdown
        $deptCounts = $employees->groupBy('department')->map->count();

        // 4. Latest punch logs today with GPS
        $livePunches = Attendance::with(['employee.branch'])
            ->whereIn('employee_id', $employeeIds)
            ->where('date', $today)
            ->whereNotNull('check_in')
            ->latest('updated_at')
            ->take(15)
            ->get();

        $branches = $user && $user->canSeeAllBranches()
            ? Branch::where('status', 'active')->orderBy('name')->get()
            : collect();

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
            'today'
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
