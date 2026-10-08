<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\MaintenanceContract;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AmcController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = MaintenanceContract::with(['customer', 'teamLead'])->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('amc_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $contracts = $query->paginate(15)->withQueryString();

        $activeAmcCount = MaintenanceContract::where('status', 'Active')->count();
        $totalValue = MaintenanceContract::where('status', 'Active')->sum('contract_value');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $contracts]);
        }

        return view('amc.index', compact('contracts', 'activeAmcCount', 'totalValue'));
    }

    public function create(): View
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        $teamLeads = User::where('status', 'active')->get();
        return view('amc.create', compact('customers', 'teamLeads'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'contract_value' => ['required', 'numeric', 'min:0'],
            'billing_frequency' => ['required', 'string'],
            'service_frequency' => ['required', 'string'],
            'assigned_team_lead_id' => ['nullable', 'exists:users,id'],
            'scope_of_work' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['amc_number'] = MaintenanceContract::generateCode();
        $validated['status'] = 'Active';

        $amc = MaintenanceContract::create($validated);
        AuditLogger::log('create', 'maintenance_contracts', $amc->id);

        return redirect()->route('amc.index')->with('success', 'AMC Contract created successfully!');
    }

    public function show(MaintenanceContract $amc): View
    {
        $amc->load(['customer', 'teamLead']);
        return view('amc.show', compact('amc'));
    }

    public function destroy(MaintenanceContract $amc): RedirectResponse
    {
        AuditLogger::log('delete', 'maintenance_contracts', $amc->id);
        $amc->delete();
        return redirect()->route('amc.index')->with('success', 'AMC Contract deleted!');
    }
}
