<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Vendor;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = PurchaseOrder::with(['vendor', 'createdBy'])->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                  ->orWhereHas('vendor', fn($vq) => $vq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $purchases = $query->paginate(15)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $purchases]);
        }

        return view('purchases.index', compact('purchases'));
    }

    public function create(): View
    {
        $vendors = Vendor::where('status', 'active')->orderBy('name')->get();
        return view('purchases.create', compact('vendors'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'vendor_id' => ['required', 'exists:vendors,id'],
            'po_date' => ['required', 'date'],
            'delivery_date' => ['nullable', 'date'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $subtotal = floatval($validated['subtotal']);
        $tax = floatval($validated['tax_amount'] ?? 0);
        $disc = floatval($validated['discount_amount'] ?? 0);
        $total = ($subtotal - $disc) + $tax;

        $validated['po_number'] = PurchaseOrder::generateNumber();
        $validated['total_amount'] = $total;
        $validated['status'] = 'Ordered';
        $validated['created_by_id'] = auth()->id();

        $po = PurchaseOrder::create($validated);
        AuditLogger::log('create', 'purchase_orders', $po->id);

        return redirect()->route('purchases.index')->with('success', 'Purchase order created!');
    }

    public function show(PurchaseOrder $purchase): View
    {
        $purchase->load(['vendor', 'items', 'createdBy']);
        return view('purchases.show', compact('purchase'));
    }

    public function destroy(PurchaseOrder $purchase): RedirectResponse
    {
        AuditLogger::log('delete', 'purchase_orders', $purchase->id);
        $purchase->delete();
        return redirect()->route('purchases.index')->with('success', 'Purchase order deleted!');
    }
}
