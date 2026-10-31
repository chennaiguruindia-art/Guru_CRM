<?php

namespace App\Http\Controllers\Api;

use App\Models\MaintenanceContract;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AmcController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = MaintenanceContract::with(['customer', 'teamLead'])->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('amc_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $paginator = $query->paginate($perPage);

        return $this->paginatedResponse($paginator, 'AMC contracts retrieved');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'contract_value' => ['required', 'numeric', 'min:0'],
            'billing_frequency' => ['required', 'string'],
            'service_frequency' => ['required', 'string'],
            'assigned_team_lead_id' => ['nullable', 'exists:users,id'],
            'scope_of_work' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['amc_number'] = MaintenanceContract::generateCode();
        $validated['status'] = 'Active';

        $amc = MaintenanceContract::create($validated);

        AuditLogger::log('create', 'maintenance_contracts', $amc->id, null, $amc->toArray());

        return $this->successResponse(
            $amc->fresh(['customer', 'teamLead']),
            'AMC contract created successfully',
            201
        );
    }

    public function show(MaintenanceContract $amc): JsonResponse
    {
        $amc->load(['customer', 'teamLead', 'schedules.assignedEmployee']);

        return $this->successResponse($amc, 'AMC contract retrieved');
    }

    public function update(Request $request, MaintenanceContract $amc): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => ['sometimes', 'required', 'exists:customers,id'],
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['sometimes', 'required', 'date'],
            'contract_value' => ['sometimes', 'required', 'numeric', 'min:0'],
            'billing_frequency' => ['sometimes', 'required', 'string'],
            'service_frequency' => ['sometimes', 'required', 'string'],
            'assigned_team_lead_id' => ['nullable', 'exists:users,id'],
            'scope_of_work' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'status' => ['sometimes', 'required', 'string', 'in:Active,Expired,Terminated,Pending'],
        ]);

        $old = $amc->toArray();
        $amc->update($validated);

        AuditLogger::log('update', 'maintenance_contracts', $amc->id, $old, $amc->fresh()->toArray());

        return $this->successResponse($amc->fresh(['customer', 'teamLead']), 'AMC contract updated');
    }

    public function destroy(MaintenanceContract $amc): JsonResponse
    {
        $id = $amc->id;
        $amc->delete();

        AuditLogger::log('delete', 'maintenance_contracts', $id);

        return $this->successResponse(null, 'AMC contract deleted');
    }
}
