<?php

namespace App\Http\Controllers\Api;

use App\Models\PurchaseOrder;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = PurchaseOrder::with(['vendor', 'createdBy'])->latest('po_date');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                  ->orWhereHas('vendor', fn ($vq) => $vq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->input('vendor_id'));
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $paginator = $query->paginate($perPage);

        return $this->paginatedResponse($paginator, 'Purchase orders retrieved');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'vendor_id' => ['required', 'exists:vendors,id'],
            'po_date' => ['required', 'date'],
            'delivery_date' => ['nullable', 'date'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'in:Draft,Ordered,Received,Cancelled'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['po_number'] = PurchaseOrder::generateNumber();
        $validated['created_by_id'] = $request->user()->id;

        $po = PurchaseOrder::create($validated);

        AuditLogger::log('create', 'purchase_orders', $po->id, null, $po->toArray());

        return $this->successResponse(
            $po->fresh(['vendor', 'createdBy']),
            'Purchase order created successfully',
            201
        );
    }

    public function show(PurchaseOrder $purchase): JsonResponse
    {
        $purchase->load(['vendor', 'createdBy', 'items']);

        return $this->successResponse($purchase, 'Purchase order retrieved');
    }

    public function update(Request $request, PurchaseOrder $purchase): JsonResponse
    {
        $validated = $request->validate([
            'delivery_date' => ['nullable', 'date'],
            'status' => ['sometimes', 'required', 'string', 'in:Draft,Ordered,Received,Cancelled'],
            'notes' => ['nullable', 'string'],
        ]);

        $old = $purchase->toArray();
        $purchase->update($validated);

        AuditLogger::log('update', 'purchase_orders', $purchase->id, $old, $purchase->fresh()->toArray());

        return $this->successResponse($purchase->fresh(['vendor', 'createdBy']), 'Purchase order updated');
    }

    public function destroy(PurchaseOrder $purchase): JsonResponse
    {
        $id = $purchase->id;
        $purchase->delete();

        AuditLogger::log('delete', 'purchase_orders', $id);

        return $this->successResponse(null, 'Purchase order deleted');
    }
}
