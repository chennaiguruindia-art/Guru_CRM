<?php

namespace App\Http\Controllers;

use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Plant;
use App\Models\Warehouse;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View|JsonResponse
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

        $items = $query->paginate(15)->withQueryString();
        $categories = InventoryCategory::orderBy('name')->get();
        $warehouses = Warehouse::where('status', 'active')->orderBy('name')->get();

        $lowStockCount = InventoryItem::whereColumn('current_stock', '<=', 'minimum_stock')->count();
        $totalItems = InventoryItem::count();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $items]);
        }

        return view('inventory.index', compact('items', 'categories', 'warehouses', 'lowStockCount', 'totalItems'));
    }

    public function create(): View
    {
        $categories = InventoryCategory::orderBy('name')->get();
        $warehouses = Warehouse::where('status', 'active')->orderBy('name')->get();
        $plants = Plant::where('status', 'active')->orderBy('name')->get();
        return view('inventory.create', compact('categories', 'warehouses', 'plants'));
    }

    public function store(Request $request): RedirectResponse
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
        AuditLogger::log('create', 'inventory_items', $item->id);

        return redirect()->route('inventory.index')->with('success', 'Inventory item added successfully!');
    }

    public function show(InventoryItem $inventory): View
    {
        $inventory->load(['category', 'plant', 'warehouse', 'transactions.createdBy']);
        return view('inventory.show', compact('inventory'));
    }

    public function destroy(InventoryItem $inventory): RedirectResponse
    {
        AuditLogger::log('delete', 'inventory_items', $inventory->id);
        $inventory->delete();
        return redirect()->route('inventory.index')->with('success', 'Inventory item deleted!');
    }
}
