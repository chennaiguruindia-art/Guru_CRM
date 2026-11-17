<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Employee;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    public function index(Request $request): View|JsonResponse|StreamedResponse
    {
        $user = $request->user();
        $date = $request->input('date', now()->toDateString());
        $search = $request->input('search');
        $department = $request->input('department');
        $statusFilter = $request->input('status');

        $carbonDate = Carbon::parse($date);
        $prevDate = $carbonDate->copy()->subDay()->toDateString();
        $nextDate = $carbonDate->copy()->addDay()->toDateString();
        $formattedDate = $carbonDate->format('d M Y');

        $employeeQuery = Employee::with('branch')->where('status', 'active');

        // Scoped to Branch Admin's branch
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

        if (! empty($search)) {
            $employeeQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (! empty($department)) {
            $employeeQuery->where('department', $department);
        }

        $employees = $employeeQuery->orderBy('name')->get();
        $employeeIds = $employees->pluck('id');

        $attendances = Attendance::with('employee')
            ->whereIn('employee_id', $employeeIds)
            ->where('date', $date)
            ->get()
            ->keyBy('employee_id');

        // Filter by attendance status if requested
        if (! empty($statusFilter)) {
            $employees = $employees->filter(function ($emp) use ($attendances, $statusFilter) {
                $att = $attendances->get($emp->id);
                if ($statusFilter === 'Not Marked') {
                    return $att === null;
                }
                return $att && strcasecmp($att->status, $statusFilter) === 0;
            });
        }

        $presentCount = $attendances->where('status', 'Present')->count();
        $absentCount = $attendances->where('status', 'Absent')->count();
        $leaveCount = $attendances->where('status', 'Leave')->count();
        $halfDayCount = $attendances->where('status', 'Half-Day')->count();

        // Export to CSV if requested
        if ($request->input('export') === 'csv') {
            return $this->exportCsv($employees, $attendances, $date);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $attendances]);
        }

        $branches = $user && $user->canSeeAllBranches()
            ? Branch::where('status', 'active')->orderBy('name')->get()
            : collect();

        $departments = Employee::whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->pluck('department');

        return view('attendance.index', compact(
            'employees',
            'attendances',
            'date',
            'prevDate',
            'nextDate',
            'formattedDate',
            'presentCount',
            'absentCount',
            'leaveCount',
            'halfDayCount',
            'branches',
            'departments',
            'search',
            'department',
            'statusFilter'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $date = $request->input('date', now()->toDateString());
        $entries = $request->input('attendance', []);
        $checkIns = $request->input('check_in', []);
        $checkOuts = $request->input('check_out', []);

        // Only allow updating employees within the user's branch
        $allowedEmployeeIds = null;
        if ($user && ! $user->canSeeAllBranches()) {
            $allowedEmployeeIds = Employee::where('branch_id', $user->branchId())->pluck('id')->all();
        }

        foreach ($entries as $employeeId => $status) {
            if ($allowedEmployeeIds !== null && ! in_array((int) $employeeId, $allowedEmployeeIds, true)) {
                continue;
            }

            $updateData = ['status' => $status];
            if (isset($checkIns[$employeeId]) && $checkIns[$employeeId] !== '') {
                $updateData['check_in'] = $checkIns[$employeeId];
            }
            if (isset($checkOuts[$employeeId]) && $checkOuts[$employeeId] !== '') {
                $updateData['check_out'] = $checkOuts[$employeeId];
            }

            Attendance::updateOrCreate(
                ['employee_id' => $employeeId, 'date' => $date],
                $updateData
            );
        }

        AuditLogger::log('update', 'attendances', 0);

        return redirect()->route('attendance.index', ['date' => $date])->with('success', 'Attendance updated successfully!');
    }

    protected function exportCsv($employees, $attendances, string $date): StreamedResponse
    {
        $fileName = "attendance_{$date}.csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        return response()->stream(function () use ($employees, $attendances, $date) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Date',
                'Employee Code',
                'Employee Name',
                'Department',
                'Designation',
                'Status',
                'In Time',
                'In Address',
                'In Latitude',
                'In Longitude',
                'Out Time',
                'Out Address',
                'Out Latitude',
                'Out Longitude',
            ]);

            foreach ($employees as $emp) {
                $att = $attendances->get($emp->id);
                fputcsv($handle, [
                    $date,
                    $emp->employee_code,
                    $emp->name,
                    $emp->department ?? '-',
                    $emp->designation ?? '-',
                    $att ? $att->status : 'Not Marked',
                    $att && $att->check_in ? Carbon::parse($att->check_in)->format('H:i') : '-',
                    $att?->check_in_address ?? '-',
                    $att?->check_in_latitude ?? '-',
                    $att?->check_in_longitude ?? '-',
                    $att && $att->check_out ? Carbon::parse($att->check_out)->format('H:i') : '-',
                    $att?->check_out_address ?? '-',
                    $att?->check_out_latitude ?? '-',
                    $att?->check_out_longitude ?? '-',
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }
}
