<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends BaseApiController
{
    /**
     * Display a listing of clients.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Customer::with(['assignedTo', 'branch'])->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('customer_code', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('customer_type')) {
            $query->where('customer_type', $request->input('customer_type'));
        }
        if ($request->filled('branch_id') && $request->user()?->canSeeAllBranches()) {
            $query->where('branch_id', $request->input('branch_id'));
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $paginator = $query->paginate($perPage);

        return $this->paginatedResponse($paginator, 'Clients retrieved successfully');
    }

    /**
     * Store a newly created client.
     */
    public function store(CustomerRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $data['customer_code'] = Customer::generateCode();
        $data['created_by_id'] = $user->id;
        $data['branch_id'] = $user->branchId();

        $customer = Customer::create($data);

        AuditLogger::log('create', 'customers', $customer->id, null, $customer->toArray());

        return $this->successResponse(
            $customer->fresh(['assignedTo', 'branch']),
            'Client profile created successfully',
            201
        );
    }

    /**
     * Display the specified client with full history.
     */
    public function show(Customer $customer): JsonResponse
    {
        $customer->load([
            'assignedTo',
            'branch',
            'contacts',
            'quotations.items',
            'projects',
            'invoices',
            'payments',
            'maintenanceContracts',
            'complaints',
        ]);

        return $this->successResponse($customer, 'Client retrieved successfully');
    }

    /**
     * Update the specified client.
     */
    public function update(CustomerRequest $request, Customer $customer): JsonResponse
    {
        $old = $customer->toArray();
        $customer->update($request->validated());

        AuditLogger::log('update', 'customers', $customer->id, $old, $customer->fresh()->toArray());

        return $this->successResponse($customer->fresh(['assignedTo', 'branch']), 'Client details updated successfully');
    }

    /**
     * Remove the specified client.
     */
    public function destroy(Customer $customer): JsonResponse
    {
        $id = $customer->id;
        $customer->delete();

        AuditLogger::log('delete', 'customers', $id);

        return $this->successResponse(null, 'Client archived successfully');
    }
}
