<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\QuotationRequest;
use App\Models\Quotation;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuotationController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Quotation::with(['customer', 'salesPerson'])->latest('date');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('quotation_number', 'like', "%{$search}%")
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

        return $this->paginatedResponse($paginator, 'Quotations retrieved successfully');
    }

    public function store(QuotationRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $quotation = DB::transaction(function () use ($validated, $request) {
            $subtotal = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $qty = (float) $item['quantity'];
                $price = (float) $item['unit_price'];
                $discount = (float) ($item['discount'] ?? 0);
                $taxPercent = (float) ($item['tax_percent'] ?? 18);

                $lineTotal = ($qty * $price) - $discount;
                $lineTax = ($lineTotal * $taxPercent) / 100;
                $lineGrand = $lineTotal + $lineTax;

                $subtotal += $lineTotal;

                $itemsData[] = [
                    'item_type' => $item['item_type'],
                    'item_name' => $item['item_name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $qty,
                    'unit' => $item['unit'],
                    'unit_price' => $price,
                    'discount' => $discount,
                    'tax_percent' => $taxPercent,
                    'total_amount' => $lineGrand,
                ];
            }

            $discountType = $validated['discount_type'] ?? 'fixed';
            $discountValue = (float) ($validated['discount_value'] ?? 0);
            $discountAmount = $discountType === 'percentage'
                ? ($subtotal * $discountValue) / 100
                : $discountValue;

            $taxPercent = (float) ($validated['tax_percent'] ?? 18);
            $taxableAmount = max(0, $subtotal - $discountAmount);
            $taxAmount = ($taxableAmount * $taxPercent) / 100;
            $grandTotal = $taxableAmount + $taxAmount;

            $quotation = Quotation::create([
                'quotation_number' => Quotation::generateNumber(),
                'customer_id' => $validated['customer_id'],
                'date' => $validated['date'],
                'valid_until' => $validated['valid_until'] ?? null,
                'sales_person_id' => $validated['sales_person_id'] ?? $request->user()->id,
                'subtotal' => $subtotal,
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'discount_amount' => $discountAmount,
                'tax_percent' => $taxPercent,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
                'terms_conditions' => $validated['terms_conditions'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => $validated['status'] ?? 'Draft',
            ]);

            foreach ($itemsData as $item) {
                $quotation->items()->create($item);
            }

            return $quotation;
        });

        AuditLogger::log('create', 'quotations', $quotation->id, null, $quotation->toArray());

        return $this->successResponse(
            $quotation->load(['customer', 'salesPerson', 'items']),
            'Quotation created successfully',
            201
        );
    }

    public function show(Quotation $quotation): JsonResponse
    {
        $quotation->load(['customer', 'salesPerson', 'items']);

        return $this->successResponse($quotation, 'Quotation retrieved successfully');
    }

    public function updateStatus(Request $request, Quotation $quotation): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:Draft,Sent,Under Review,Approved,Rejected'],
        ]);

        $quotation->update(['status' => $validated['status']]);

        AuditLogger::log('status_change', 'quotations', $quotation->id, null, $validated);

        return $this->successResponse($quotation->fresh(['customer', 'salesPerson', 'items']), 'Quotation status updated');
    }

    public function destroy(Quotation $quotation): JsonResponse
    {
        $id = $quotation->id;
        $quotation->delete();

        AuditLogger::log('delete', 'quotations', $id);

        return $this->successResponse(null, 'Quotation deleted successfully');
    }
}
