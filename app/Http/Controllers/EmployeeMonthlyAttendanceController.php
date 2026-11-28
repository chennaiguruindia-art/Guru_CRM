<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\EmployeeMonthlyAttendance;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeMonthlyAttendanceController extends Controller
{
    /**
     * Display the monthly attendance summary table or export Excel/CSV report.
     */
    public function index(Request $request): View|StreamedResponse
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
            // Super Admin views monthly attendance strictly branch-wise
            $selectedBranchId = $request->filled('branch_id')
                ? (int) $request->input('branch_id')
                : ($branches->first()?->id);
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
        $detailsByEmployee = $this->buildMonthlyDetails($records, $monthCarbon);

        if ($request->input('export') === 'excel') {
            return $this->exportExcel($records, $detailsByEmployee, $monthCarbon, $selectedMonth);
        }

        return view('hrm.monthly-attendance', compact(
            'records',
            'detailsByEmployee',
            'selectedMonth',
            'selectedBranchId',
            'branches',
            'canSeeAll',
            'monthCarbon'
        ));
    }

    /**
     * Compute per-employee daily work hours, monthly work hours, and exact Leave/Absent/Half-Day/Permission dates.
     */
    protected function buildMonthlyDetails($records, Carbon $monthCarbon): array
    {
        $employeeIds = $records->pluck('employee_id')->unique()->filter()->values();
        $startOfMonth = $monthCarbon->copy()->startOfMonth()->toDateString();
        $endOfMonth = $monthCarbon->copy()->endOfMonth()->toDateString();
        $totalDays = $monthCarbon->daysInMonth;
        $today = now('Asia/Kolkata')->startOfDay();

        $allAttendances = Attendance::whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get()
            ->groupBy('employee_id');

        $details = [];

        foreach ($records as $row) {
            $empAttendances = ($allAttendances->get($row->employee_id) ?? collect())
                ->keyBy(fn ($a) => Carbon::parse($a->date)->toDateString());

            $monthlyHours = 0.0;
            $leaveCount = 0;
            $halfDayCount = 0;
            $leaveDates = [];
            $absentDates = [];
            $halfDayDates = [];
            $permissionDates = [];
            $days = [];

            for ($day = 1; $day <= $totalDays; $day++) {
                $dayDate = $monthCarbon->copy()->day($day)->startOfDay();
                $dateKey = $dayDate->toDateString();
                $shortDate = $dayDate->format('d M');
                $dayName = $dayDate->format('D');

                if ($dayDate->isSunday()) {
                    $days[$day] = [
                        'date' => $shortDate,
                        'day_name' => $dayName,
                        'is_sunday' => true,
                        'check_in' => '—',
                        'check_out' => '—',
                        'worked_hours' => 0.0,
                        'status' => 'Sunday (Paid)',
                        'has_permission' => false,
                        'is_auto_checkout' => false,
                        'excel_cell' => 'Sunday (Paid)',
                    ];
                    continue;
                }

                $att = $empAttendances->get($dateKey);
                $dailyHours = 0.0;

                if ($att) {
                    if ($att->worked_hours !== null) {
                        $dailyHours = (float) $att->worked_hours;
                    } elseif ($att->check_in && $att->check_out) {
                        $in = Carbon::parse($dateKey . ' ' . Carbon::parse($att->check_in)->format('H:i:s'), 'Asia/Kolkata');
                        $out = Carbon::parse($dateKey . ' ' . Carbon::parse($att->check_out)->format('H:i:s'), 'Asia/Kolkata');
                        if ($out->lt($in)) {
                            $out->addDay();
                        }
                        $dailyHours = round($in->diffInMinutes($out) / 60, 2);
                    }

                    $monthlyHours += $dailyHours;
                    $hrsFormatted = number_format($dailyHours, 2);

                    if ($att->has_permission) {
                        $permissionDates[] = "{$shortDate} ({$hrsFormatted}h)";
                    }

                    if ($att->status === 'Leave') {
                        $leaveCount++;
                        $leaveDates[] = $shortDate;
                        $excelCell = 'Leave';
                    } elseif ($att->status === 'Absent') {
                        $absentDates[] = $shortDate;
                        $excelCell = $dailyHours > 0 ? "Absent ({$hrsFormatted}h)" : 'Absent';
                    } elseif ($att->status === 'Half-Day') {
                        $halfDayCount++;
                        $halfDayDates[] = "{$shortDate} ({$hrsFormatted}h)";
                        $excelCell = "Half-Day ({$hrsFormatted}h)";
                    } else {
                        $permSuffix = $att->has_permission ? ' + Perm' : '';
                        $excelCell = "Present{$permSuffix} ({$hrsFormatted}h)";
                    }

                    $days[$day] = [
                        'date' => $shortDate,
                        'day_name' => $dayName,
                        'is_sunday' => false,
                        'check_in' => $att->check_in ? Carbon::parse($att->check_in)->format('h:i A') : '—',
                        'check_out' => $att->check_out ? Carbon::parse($att->check_out)->format('h:i A') : '—',
                        'worked_hours' => $dailyHours,
                        'status' => $att->status,
                        'has_permission' => (bool) $att->has_permission,
                        'is_auto_checkout' => (bool) $att->is_auto_checkout,
                        'excel_cell' => $excelCell,
                    ];
                } else {
                    $isPastOrToday = $dayDate->lte($today);
                    if ($isPastOrToday) {
                        $absentDates[] = $shortDate;
                    }
                    $days[$day] = [
                        'date' => $shortDate,
                        'day_name' => $dayName,
                        'is_sunday' => false,
                        'check_in' => '—',
                        'check_out' => '—',
                        'worked_hours' => 0.0,
                        'status' => $isPastOrToday ? 'Absent' : 'Upcoming',
                        'has_permission' => false,
                        'is_auto_checkout' => false,
                        'excel_cell' => $isPastOrToday ? 'Absent' : '-',
                    ];
                }
            }

            $details[$row->employee_id] = [
                'monthly_work_hours' => round($monthlyHours, 2),
                'leave_count' => $leaveCount,
                'half_day_count' => $halfDayCount,
                'leave_dates' => $leaveDates,
                'absent_dates' => $absentDates,
                'half_day_dates' => $halfDayDates,
                'permission_dates' => $permissionDates,
                'days' => $days,
            ];
        }

        return $details;
    }

    /**
     * Stream an Excel-ready UTF-8 CSV report with summary, exact leave/permission dates,
     * monthly work hours, and day-by-day daily work hours.
     */
    protected function exportExcel($records, array $detailsByEmployee, Carbon $monthCarbon, string $selectedMonth): StreamedResponse
    {
        $fileName = "monthly_attendance_{$selectedMonth}.csv";
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $totalDays = $monthCarbon->daysInMonth;

        return response()->stream(function () use ($records, $detailsByEmployee, $monthCarbon, $totalDays) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM for proper Excel rendering
            fwrite($handle, "\xEF\xBB\xBF");

            $headerRow = [
                'User ID',
                'Employee Code',
                'Employee Name',
                'Designation',
                'Branch',
                'Month',
                'Total Present (incl. Paid Sundays)',
                'Total Half-Days',
                'Total Leave',
                'Total Absent',
                'No. of Permissions',
                'Monthly Work Hours (hrs)',
                'Leave Dates',
                'Absent Dates',
                'Half-Day Dates',
                'Permission Dates',
            ];

            for ($day = 1; $day <= $totalDays; $day++) {
                $d = $monthCarbon->copy()->day($day);
                $headerRow[] = $d->format('d M') . ' (' . $d->format('D') . ')';
            }

            fputcsv($handle, $headerRow);

            foreach ($records as $row) {
                $det = $detailsByEmployee[$row->employee_id] ?? null;

                $csvRow = [
                    $row->user_id ?? '-',
                    $row->employee_code,
                    $row->user_name,
                    $row->employee?->designation ?? 'Staff',
                    $row->branch ?: '-',
                    $monthCarbon->format('M Y'),
                    $row->total_present,
                    $det['half_day_count'] ?? 0,
                    $det['leave_count'] ?? 0,
                    $row->total_absent,
                    $row->no_of_permission,
                    number_format((float) ($det['monthly_work_hours'] ?? 0), 2),
                    ! empty($det['leave_dates']) ? implode(', ', $det['leave_dates']) : 'None',
                    ! empty($det['absent_dates']) ? implode(', ', $det['absent_dates']) : 'None',
                    ! empty($det['half_day_dates']) ? implode(', ', $det['half_day_dates']) : 'None',
                    ! empty($det['permission_dates']) ? implode(', ', $det['permission_dates']) : 'None',
                ];

                for ($day = 1; $day <= $totalDays; $day++) {
                    $csvRow[] = $det['days'][$day]['excel_cell'] ?? '-';
                }

                fputcsv($handle, $csvRow);
            }

            fclose($handle);
        }, 200, $headers);
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
