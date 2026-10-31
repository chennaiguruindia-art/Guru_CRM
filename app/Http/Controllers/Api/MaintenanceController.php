<?php

namespace App\Http\Controllers\Api;

use App\Models\MaintenanceRecord;
use App\Models\MaintenanceSchedule;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaintenanceController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = MaintenanceRecord::with(['customer', 'employee'])->latest('visit_date');

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('work_description', 'like', "%{$search}%")
                  ->orWhere('activity_type', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $paginator = $query->paginate($perPage);

        return $this->paginatedResponse($paginator, 'Maintenance records retrieved');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'visit_date' => ['required', 'date'],
            'employee_id' => ['nullable', 'exists:users,id'],
            'activity_type' => ['required', 'string'],
            'area_details' => ['nullable', 'string'],
            'work_description' => ['nullable', 'string'],
            'remarks' => ['nullable', 'string'],
        ]);

        $record = MaintenanceRecord::create($validated);

        AuditLogger::log('create', 'maintenance_records', $record->id, null, $record->toArray());

        return $this->successResponse(
            $record->fresh(['customer', 'employee']),
            'Maintenance record logged successfully',
            201
        );
    }

    public function show(MaintenanceRecord $maintenance): JsonResponse
    {
        $maintenance->load(['customer', 'employee']);

        return $this->successResponse($maintenance, 'Maintenance record retrieved');
    }

    public function update(Request $request, MaintenanceRecord $maintenance): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => ['sometimes', 'required', 'exists:customers,id'],
            'visit_date' => ['sometimes', 'required', 'date'],
            'employee_id' => ['nullable', 'exists:users,id'],
            'activity_type' => ['sometimes', 'required', 'string'],
            'area_details' => ['nullable', 'string'],
            'work_description' => ['nullable', 'string'],
            'remarks' => ['nullable', 'string'],
        ]);

        $old = $maintenance->toArray();
        $maintenance->update($validated);

        AuditLogger::log('update', 'maintenance_records', $maintenance->id, $old, $maintenance->fresh()->toArray());

        return $this->successResponse($maintenance->fresh(['customer', 'employee']), 'Maintenance record updated');
    }

    public function destroy(MaintenanceRecord $maintenance): JsonResponse
    {
        $id = $maintenance->id;
        $maintenance->delete();

        AuditLogger::log('delete', 'maintenance_records', $id);

        return $this->successResponse(null, 'Maintenance record deleted');
    }
}
