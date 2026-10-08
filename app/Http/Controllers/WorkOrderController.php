<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Project;
use App\Models\User;
use App\Models\WorkOrder;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkOrderController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $workOrders = WorkOrder::with(['customer', 'project', 'supervisor'])->latest()->paginate(15);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $workOrders]);
        }

        return view('work-orders.index', compact('workOrders'));
    }

    public function create(): View
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        $projects = Project::all();
        $supervisors = User::where('status', 'active')->get();
        return view('work-orders.create', compact('customers', 'projects', 'supervisors'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'supervisor_id' => ['nullable', 'exists:users,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'scope_of_work' => ['nullable', 'string'],
            'status' => ['required', 'string'],
            'remarks' => ['nullable', 'string'],
        ]);

        $last = WorkOrder::latest('id')->first();
        $next = $last ? ($last->id + 1) : 1;
        $validated['wo_number'] = 'WO-' . date('Y') . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);

        $wo = WorkOrder::create($validated);
        AuditLogger::log('create', 'work_orders', $wo->id);

        return redirect()->route('work-orders.index')->with('success', 'Work Order generated successfully!');
    }

    public function show(WorkOrder $workOrder): View
    {
        $workOrder->load(['customer', 'project', 'supervisor', 'items']);
        return view('work-orders.show', compact('workOrder'));
    }
}
