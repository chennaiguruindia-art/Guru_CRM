<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = Vendor::latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('vendor_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $vendors = $query->paginate(15)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $vendors]);
        }

        return view('vendors.index', compact('vendors'));
    }

    public function create(): View
    {
        return view('vendors.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'gst_number' => ['nullable', 'string', 'max:20'],
            'pan' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'payment_terms' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['vendor_code'] = Vendor::generateCode();
        $validated['status'] = 'active';

        $vendor = Vendor::create($validated);
        AuditLogger::log('create', 'vendors', $vendor->id);

        return redirect()->route('vendors.index')->with('success', 'Vendor registered successfully!');
    }

    public function show(Vendor $vendor): View
    {
        $vendor->load('purchaseOrders');
        return view('vendors.show', compact('vendor'));
    }

    public function destroy(Vendor $vendor): RedirectResponse
    {
        AuditLogger::log('delete', 'vendors', $vendor->id);
        $vendor->delete();
        return redirect()->route('vendors.index')->with('success', 'Vendor deleted!');
    }
}
