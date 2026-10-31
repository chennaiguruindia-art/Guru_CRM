<?php

namespace App\Http\Controllers\Api;

use App\Models\Vendor;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Vendor::latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('vendor_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $paginator = $query->paginate($perPage);

        return $this->paginatedResponse($paginator, 'Vendors retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'gst_number' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);

        $last = Vendor::latest('id')->first();
        $next = $last ? ($last->id + 1) : 1;
        $validated['vendor_code'] = 'VEN-' . str_pad($next, 4, '0', STR_PAD_LEFT);

        $vendor = Vendor::create($validated);

        AuditLogger::log('create', 'vendors', $vendor->id, null, $vendor->toArray());

        return $this->successResponse($vendor, 'Vendor created successfully', 201);
    }

    public function show(Vendor $vendor): JsonResponse
    {
        $vendor->load(['purchaseOrders']);

        return $this->successResponse($vendor, 'Vendor retrieved');
    }

    public function update(Request $request, Vendor $vendor): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['sometimes', 'required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'gst_number' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'status' => ['sometimes', 'required', 'string', 'in:active,inactive'],
        ]);

        $old = $vendor->toArray();
        $vendor->update($validated);

        AuditLogger::log('update', 'vendors', $vendor->id, $old, $vendor->fresh()->toArray());

        return $this->successResponse($vendor, 'Vendor updated');
    }

    public function destroy(Vendor $vendor): JsonResponse
    {
        $id = $vendor->id;
        $vendor->delete();

        AuditLogger::log('delete', 'vendors', $id);

        return $this->successResponse(null, 'Vendor deleted');
    }
}
