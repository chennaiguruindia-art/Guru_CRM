<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = Customer::with(['assignedTo'])->latest();

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

        if ($request->filled('customer_type')) {
            $query->where('customer_type', $request->input('customer_type'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $customers = $query->paginate(15)->withQueryString();
        $users = User::where('status', 'active')->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $customers,
            ]);
        }

        return view('customers.index', compact('customers', 'users'));
    }

    public function create(): View
    {
        $users = User::where('status', 'active')->get();
        return view('customers.create', compact('users'));
    }

    public function store(CustomerRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $data['customer_code'] = Customer::generateCode();

        $customer = Customer::create($data);

        AuditLogger::log('create', 'customers', $customer->id, null, $customer->toArray());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Client created successfully',
                'data' => $customer,
            ], 201);
        }

        return redirect()->route('customers.show', $customer)->with('success', 'Client profile created successfully!');
    }

    public function show(Customer $customer): View
    {
        $customer->load([
            'assignedTo',
            'contacts',
            'quotations.items',
            'projects',
            'invoices',
            'payments',
            'maintenanceContracts',
            'complaints',
        ]);

        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer): View
    {
        $users = User::where('status', 'active')->get();
        return view('customers.edit', compact('customer', 'users'));
    }

    public function update(CustomerRequest $request, Customer $customer): RedirectResponse|JsonResponse
    {
        $old = $customer->toArray();
        $customer->update($request->validated());

        AuditLogger::log('update', 'customers', $customer->id, $old, $customer->fresh()->toArray());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Client updated successfully',
                'data' => $customer,
            ]);
        }

        return redirect()->route('customers.show', $customer)->with('success', 'Client details updated successfully!');
    }

    public function destroy(Request $request, Customer $customer): RedirectResponse|JsonResponse
    {
        $id = $customer->id;
        $customer->delete();

        AuditLogger::log('delete', 'customers', $id);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Client archived successfully',
            ]);
        }

        return redirect()->route('customers.index')->with('success', 'Client archived successfully.');
    }
}
