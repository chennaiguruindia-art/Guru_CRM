<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\EmployeeMonthlyAttendance;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeMonthlyAttendanceController extends Controller
{
    /**
     * Display the monthly attendance summary table.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $selectedMonth = $request->input('month', now('Asia/Kolkata')->format('Y-m'));

        try {
            $monthCarbon = Carbon::createFromFormat('Y-m', $selectedMonth, 'Asia/Kolkata');
        } catch (\Throwable $e) {
            $monthCarbon = now('Asia/Kolkata');
            $selectedMonth = $monthCarbon->format('Y-m');
        }

        // Branch scoping
        $canSeeAll = $user && $user->canSeeAllBranches();
        $branches = $canSeeAll
            ? Branch::where('status', 'active')->orderBy('name')->get()
            : collect();

        if (! $canSeeAll) {
            $selectedBranchId = $user ? $user->branchId() : null;
        } else {
            $selectedBranchId = $request->filled('branch_id')
                ? (int) $request->input('branch_id')
                : null;
        }

        // Auto-sync monthly attendance data for selected month & branch
        EmployeeMonthlyAttendance::syncForMonth($selectedMonth, $selectedBranchId);

        // Fetch records from employee_monthly_attendances table
        $query = EmployeeMonthlyAttendance::with(['employee', 'user', 'branchModel'])
            ->where('month', $selectedMonth);

        if ($selectedBranchId) {
            $query->where('branch_id', $selectedBranchId);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%")
                  ->orWhere('branch', 'like', "%{$search}%");
            });
        }

        $records = $query->orderBy('user_name')->get();

        return view('hrm.monthly-attendance', compact(
            'records',
            'selectedMonth',
            'selectedBranchId',
            'branches',
            'canSeeAll',
            'monthCarbon'
        ));
    }

    /**
     * Manually trigger re-sync from raw attendance records.
     */
    public function sync(Request $request): RedirectResponse
    {
        $selectedMonth = $request->input('month', now('Asia/Kolkata')->format('Y-m'));
        $branchId = $request->input('branch_id') ? (int) $request->input('branch_id') : null;

        EmployeeMonthlyAttendance::syncForMonth($selectedMonth, $branchId);

        return back()->with('success', "Attendance data for {$selectedMonth} successfully synchronized into the monthly table!");
    }
}
