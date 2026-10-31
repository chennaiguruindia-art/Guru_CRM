<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\EstimationRequest;
use App\Models\Estimation;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstimationController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Estimation::with(['customer', 'createdBy'])->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('estimation_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
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

        return $this->paginatedResponse($paginator, 'Estimations retrieved');
    }

    public function store(EstimationRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $items = $validated['items'] ?? [];
        unset($validated['items']);

        $plants = floatval($validated['plants_total'] ?? 0);
        $materials = floatval($validated['materials_total'] ?? 0);
        $labour = floatval($validated['labour_total'] ?? 0);
        $transport = floatval($validated['transport_total'] ?? 0);
        $other = floatval($validated['other_total'] ?? 0);

        $subtotal = $plants + $materials + $labour + $transport + $other;
        $profitMarginPercent = floatval($validated['profit_margin_percent'] ?? 0);
        $profitMarginAmount = ($subtotal * $profitMarginPercent) / 100;
        $subWithProfit = $subtotal + $profitMarginAmount;

        $discount = floatval($validated['discount_amount'] ?? 0);
        $taxPercent = floatval($validated['tax_percent'] ?? 18);
        $afterDiscount = max(0, $subWithProfit - $discount);
        $taxAmount = ($afterDiscount * $taxPercent) / 100;
        $grandTotal = $afterDiscount + $taxAmount;

        $data = array_merge($validated, [
            'estimation_number' => Estimation::generateNumber(),
            'subtotal' => $subtotal,
            'profit_margin_amount' => $profitMarginAmount,
            'tax_amount' => $taxAmount,
            'grand_total' => $grandTotal,
            'status' => $validated['status'] ?? 'Draft',
            'created_by_id' => $request->user()->id,
        ]);

        $est = DB::transaction(function () use ($data, $items) {
            $estimation = Estimation::create($data);

            foreach ($items as $item) {
                $estimation->items()->create([
                    'item_type' => $item['item_type'],
                    'item_name' => $item['item_name'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'rate' => $item['rate'],
                    'amount' => round($item['quantity'] * $item['rate'], 2),
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            return $estimation;
        });

        AuditLogger::log('create', 'estimations', $est->id, null, $est->toArray());

        return $this->successResponse(
            $est->load(['customer', 'createdBy', 'items']),
            'Estimation created successfully',
            201
        );
    }

    public function show(Estimation $estimation): JsonResponse
    {
        $estimation->load(['customer', 'createdBy', 'items']);

        return $this->successResponse($estimation, 'Estimation retrieved');
    }

    public function destroy(Estimation $estimation): JsonResponse
    {
        $id = $estimation->id;
        $estimation->delete();

        AuditLogger::log('delete', 'estimations', $id);

        return $this->successResponse(null, 'Estimation deleted');
    }
}
