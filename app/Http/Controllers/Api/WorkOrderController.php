<?php

namespace App\Http\Controllers\Api;

use App\Models\WorkOrder;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkOrderController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = WorkOrder::with(['customer', 'project', 'supervisor'])->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('wo_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $paginator = $query->paginate($perPage);

        return $this->paginatedResponse($paginator, 'Work orders retrieved');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'quotation_id' => ['nullable', 'exists:quotations,id'],
            'supervisor_id' => ['nullable', 'exists:users,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date'],
            'scope_of_work' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:Draft,Issued,In Progress,Completed,Cancelled'],
            'remarks' => ['nullable', 'string'],
        ]);

        $last = WorkOrder::latest('id')->first();
        $next = $last ? ($last->id + 1) : 1;
        $validated['wo_number'] = 'WO-' . date('Y') . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);

        $workOrder = WorkOrder::create($validated);

        AuditLogger::log('create', 'work_orders', $workOrder->id, null, $workOrder->toArray());

        return $this->successResponse(
            $workOrder->fresh(['customer', 'project', 'supervisor']),
            'Work order created successfully',
            201
        );
    }

    public function show(WorkOrder $workOrder): JsonResponse
    {
        $workOrder->load(['customer', 'project', 'supervisor', 'items']);

        return $this->successResponse($workOrder, 'Work order retrieved');
    }

    public function update(Request $request, WorkOrder $workOrder): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => ['sometimes', 'required', 'exists:customers,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'supervisor_id' => ['nullable', 'exists:users,id'],
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['nullable', 'date'],
            'scope_of_work' => ['nullable', 'string'],
            'status' => ['sometimes', 'required', 'string', 'in:Draft,Issued,In Progress,Completed,Cancelled'],
            'remarks' => ['nullable', 'string'],
        ]);

        $old = $workOrder->toArray();
        $workOrder->update($validated);

        AuditLogger::log('update', 'work_orders', $workOrder->id, $old, $workOrder->fresh()->toArray());

        return $this->successResponse($workOrder->fresh(['customer', 'project', 'supervisor']), 'Work order updated');
    }

    public function destroy(WorkOrder $workOrder): JsonResponse
    {
        $id = $workOrder->id;
        $workOrder->delete();

        AuditLogger::log('delete', 'work_orders', $id);

        return $this->successResponse(null, 'Work order deleted');
    }
}
