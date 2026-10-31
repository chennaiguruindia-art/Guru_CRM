<?php

namespace App\Http\Controllers\Api;

use App\Models\Employee;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Employee::with(['branch', 'user'])->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('designation', 'like', "%{$search}%");
            });
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->input('branch_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $paginator = $query->paginate($perPage);

        return $this->paginatedResponse($paginator, 'Employees retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'designation' => ['nullable', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:100'],
            'joining_date' => ['nullable', 'date'],
            'employee_type' => ['nullable', 'string', 'max:50'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'address' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);

        $validated['employee_code'] = Employee::generateCode();

        $employee = Employee::create($validated);

        AuditLogger::log('create', 'employees', $employee->id, null, $employee->toArray());

        return $this->successResponse(
            $employee->fresh(['branch', 'user']),
            'Employee added successfully',
            201
        );
    }

    public function show(Employee $employee): JsonResponse
    {
        $employee->load(['branch', 'user', 'attendances' => fn ($q) => $q->latest('date')->take(30)]);

        return $this->successResponse($employee, 'Employee retrieved successfully');
    }

    public function update(Request $request, Employee $employee): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'phone' => ['sometimes', 'required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'designation' => ['nullable', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:100'],
            'joining_date' => ['nullable', 'date'],
            'employee_type' => ['nullable', 'string', 'max:50'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'address' => ['nullable', 'string'],
            'status' => ['sometimes', 'required', 'string', 'in:active,inactive'],
        ]);

        $old = $employee->toArray();
        $employee->update($validated);

        AuditLogger::log('update', 'employees', $employee->id, $old, $employee->fresh()->toArray());

        return $this->successResponse($employee->fresh(['branch', 'user']), 'Employee updated');
    }

    public function destroy(Employee $employee): JsonResponse
    {
        $id = $employee->id;
        $employee->delete();

        AuditLogger::log('delete', 'employees', $id);

        return $this->successResponse(null, 'Employee deleted');
    }
}
