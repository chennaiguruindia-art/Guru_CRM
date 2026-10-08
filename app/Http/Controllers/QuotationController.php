<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuotationRequest;
use App\Models\Customer;
use App\Models\Plant;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class QuotationController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = Quotation::with(['customer', 'salesPerson'])->latest('date');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('quotation_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $quotations = $query->paginate(15)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $quotations,
            ]);
        }

        return view('quotations.index', compact('quotations'));
    }

    public function create(Request $request): View
    {
        $customerId = $request->query('customer_id');

        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        $plants = Plant::where('status', 'active')->get();
        $users = User::where('status', 'active')->get();

        return view('quotations.create', compact('customers', 'plants', 'users', 'customerId'));
    }

    public function store(QuotationRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();

        $quotation = DB::transaction(function () use ($validated) {
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

            // Global discount calculation
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
                'sales_person_id' => $validated['sales_person_id'] ?? auth()->id(),
                'subtotal' => $subtotal,
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'discount_amount' => $discountAmount,
                'tax_percent' => $taxPercent,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
                'terms_conditions' => $validated['terms_conditions'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => $validated['status'],
            ]);

            foreach ($itemsData as $item) {
                $quotation->items()->create($item);
            }

            return $quotation;
        });

        AuditLogger::log('create', 'quotations', $quotation->id, null, $quotation->toArray());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Quotation generated successfully',
                'data' => $quotation->load('items'),
            ], 201);
        }

        return redirect()->route('quotations.show', $quotation)->with('success', 'Quotation generated successfully!');
    }

    public function show(Quotation $quotation): View
    {
        $quotation->load(['customer', 'salesPerson', 'items']);
        return view('quotations.show', compact('quotation'));
    }

    public function print(Quotation $quotation): View
    {
        $quotation->load(['customer', 'salesPerson', 'items']);
        return view('quotations.print', compact('quotation'));
    }

    public function updateStatus(Request $request, Quotation $quotation): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:Draft,Sent,Under Review,Approved,Rejected,Expired'],
        ]);

        $quotation->update(['status' => $validated['status']]);

        AuditLogger::log('status_change', 'quotations', $quotation->id, null, ['status' => $validated['status']]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Quotation status updated to ' . $validated['status'],
                'data' => $quotation,
            ]);
        }

        return back()->with('success', 'Quotation status updated successfully.');
    }

    public function destroy(Request $request, Quotation $quotation): RedirectResponse|JsonResponse
    {
        $id = $quotation->id;
        $quotation->delete();

        AuditLogger::log('delete', 'quotations', $id);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Quotation deleted successfully',
            ]);
        }

        return redirect()->route('quotations.index')->with('success', 'Quotation removed successfully.');
    }
}
