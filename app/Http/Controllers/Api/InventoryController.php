<?php

namespace App\Http\Controllers\Api;

use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = InventoryItem::with(['category', 'plant', 'warehouse'])->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->boolean('low_stock')) {
            $query->whereColumn('current_stock', '<=', 'minimum_stock');
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $paginator = $query->paginate($perPage);

        return $this->paginatedResponse($paginator, 'Inventory items retrieved');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'unique:inventory_items,sku'],
            'category_id' => ['nullable', 'exists:inventory_categories,id'],
            'plant_id' => ['nullable', 'exists:plants,id'],
            'unit' => ['required', 'string'],
            'opening_stock' => ['required', 'numeric', 'min:0'],
            'minimum_stock' => ['required', 'numeric', 'min:0'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'selling_price' => ['nullable', 'numeric', 'min:0'],
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'rack' => ['nullable', 'string'],
        ]);

        if (empty($validated['sku'])) {
            $validated['sku'] = 'SKU-' . strtoupper(uniqid());
        }
        $validated['current_stock'] = $validated['opening_stock'];
        $validated['status'] = 'active';

        $item = InventoryItem::create($validated);

        AuditLogger::log('create', 'inventory_items', $item->id, null, $item->toArray());

        return $this->successResponse(
            $item->fresh(['category', 'plant', 'warehouse']),
            'Inventory item created successfully',
            201
        );
    }

    public function show(InventoryItem $inventory): JsonResponse
    {
        $inventory->load(['category', 'plant', 'warehouse', 'transactions.createdBy']);

        return $this->successResponse($inventory, 'Inventory item retrieved');
    }

    public function update(Request $request, InventoryItem $inventory): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'sku' => ['sometimes', 'required', 'string', 'unique:inventory_items,sku,' . $inventory->id],
            'category_id' => ['nullable', 'exists:inventory_categories,id'],
            'plant_id' => ['nullable', 'exists:plants,id'],
            'unit' => ['sometimes', 'required', 'string'],
            'minimum_stock' => ['sometimes', 'required', 'numeric', 'min:0'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'selling_price' => ['nullable', 'numeric', 'min:0'],
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'rack' => ['nullable', 'string'],
            'status' => ['sometimes', 'required', 'string'],
        ]);

        $old = $inventory->toArray();
        $inventory->update($validated);

        AuditLogger::log('update', 'inventory_items', $inventory->id, $old, $inventory->fresh()->toArray());

        return $this->successResponse($inventory->fresh(['category', 'plant', 'warehouse']), 'Inventory item updated');
    }

    public function destroy(InventoryItem $inventory): JsonResponse
    {
        $id = $inventory->id;
        $inventory->delete();

        AuditLogger::log('delete', 'inventory_items', $id);

        return $this->successResponse(null, 'Inventory item deleted');
    }

    /**
     * Adjust stock / record transaction.
     */
    public function adjustStock(Request $request, InventoryItem $inventory): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:in,out,adjustment'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $item = DB::transaction(function () use ($inventory, $validated, $request) {
            $qty = (float) $validated['quantity'];
            $newStock = (float) $inventory->current_stock;

            if ($validated['type'] === 'in') {
                $newStock += $qty;
            } elseif ($validated['type'] === 'out') {
                $newStock -= $qty;
            } else { // adjustment
                $newStock = $qty;
            }

            $inventory->update(['current_stock' => $newStock]);

            InventoryTransaction::create([
                'inventory_item_id' => $inventory->id,
                'transaction_type' => $validated['type'],
                'quantity' => $qty,
                'balance_after' => $newStock,
                'notes' => $validated['notes'] ?? null,
                'created_by_id' => $request->user()->id,
            ]);

            return $inventory->fresh(['category', 'warehouse', 'transactions']);
        });

        AuditLogger::log('stock_adjust', 'inventory_items', $inventory->id, null, $validated);

        return $this->successResponse($item, 'Stock adjusted successfully');
    }
}
