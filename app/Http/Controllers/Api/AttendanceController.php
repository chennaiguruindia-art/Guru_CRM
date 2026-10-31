<?php

namespace App\Http\Controllers\Api;

use App\Models\Attendance;
use App\Models\Employee;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends BaseApiController
{
    /**
     * Display attendance records for a date or month.
     */
    public function index(Request $request): JsonResponse
    {
        $date = $request->input('date', now()->toDateString());
        $month = $request->input('month');

        $query = Attendance::with('employee');

        if ($month) {
            $query->where('date', 'like', "{$month}%");
        } else {
            $query->where('date', $date);
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->input('employee_id'));
        }

        $attendances = $query->get();

        return $this->successResponse([
            'date' => $date,
            'summary' => [
                'present' => $attendances->where('status', 'Present')->count(),
                'absent' => $attendances->where('status', 'Absent')->count(),
                'leave' => $attendances->where('status', 'Leave')->count(),
                'half_day' => $attendances->where('status', 'Half Day')->count(),
            ],
            'records' => $attendances,
        ], 'Attendance records retrieved successfully');
    }

    /**
     * Mobile self-punch attendance for the logged-in user.
     */
    public function punch(Request $request): JsonResponse
    {
        $user = $request->user();
        $employee = $user->employee;

        if (! $employee) {
            return $this->errorResponse('No employee profile associated with this account. Cannot mark attendance.', 422);
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:Present,Absent,Half Day,Leave'],
            'notes' => ['nullable', 'string', 'max:500'],
            'date' => ['nullable', 'date'],
        ]);

        $date = $validated['date'] ?? now()->toDateString();

        $attendance = Attendance::updateOrCreate(
            ['employee_id' => $employee->id, 'date' => $date],
            [
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
            ]
        );

        AuditLogger::log('punch', 'attendances', $attendance->id, null, [
            'employee' => $employee->name,
            'status' => $attendance->status,
            'date' => $date,
        ]);

        return $this->successResponse($attendance->load('employee'), 'Attendance marked successfully');
    }

    /**
     * Get logged-in user's attendance history.
     */
    public function myAttendance(Request $request): JsonResponse
    {
        $user = $request->user();
        $employee = $user->employee;

        if (! $employee) {
            return $this->errorResponse('No employee profile associated with this account.', 422);
        }

        $query = Attendance::where('employee_id', $employee->id)->latest('date');

        if ($request->filled('month')) {
            $query->where('date', 'like', $request->input('month') . '%');
        }

        $perPage = min((int) $request->input('per_page', 31), 100);
        $paginator = $query->paginate($perPage);

        return $this->paginatedResponse($paginator, 'Personal attendance history retrieved');
    }

    /**
     * Bulk store attendance records (Manager / Admin).
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'date' => ['required', 'date'],
            'attendance' => ['required', 'array'],
            'attendance.*.employee_id' => ['required', 'exists:employees,id'],
            'attendance.*.status' => ['required', 'string', 'in:Present,Absent,Half Day,Leave'],
        ]);

        $date = $request->input('date');
        $entries = $request->input('attendance');

        $saved = [];
        foreach ($entries as $entry) {
            $saved[] = Attendance::updateOrCreate(
                ['employee_id' => $entry['employee_id'], 'date' => $date],
                ['status' => $entry['status']]
            );
        }

        AuditLogger::log('bulk_update', 'attendances', 0, null, ['date' => $date, 'count' => count($saved)]);

        return $this->successResponse($saved, 'Bulk attendance saved successfully');
    }
}
