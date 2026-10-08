<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $date = $request->input('date', now()->toDateString());
        $employees = Employee::where('status', 'active')->orderBy('name')->get();
        $attendances = Attendance::with('employee')->where('date', $date)->get()->keyBy('employee_id');

        $presentCount = $attendances->where('status', 'Present')->count();
        $absentCount = $attendances->where('status', 'Absent')->count();
        $leaveCount = $attendances->where('status', 'Leave')->count();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $attendances]);
        }

        return view('attendance.index', compact('employees', 'attendances', 'date', 'presentCount', 'absentCount', 'leaveCount'));
    }

    public function store(Request $request): RedirectResponse
    {
        $date = $request->input('date', now()->toDateString());
        $entries = $request->input('attendance', []);

        foreach ($entries as $employeeId => $status) {
            Attendance::updateOrCreate(
                ['employee_id' => $employeeId, 'date' => $date],
                ['status' => $status]
            );
        }

        AuditLogger::log('update', 'attendances', 0);

        return redirect()->route('attendance.index', ['date' => $date])->with('success', 'Attendance marked successfully!');
    }
}
