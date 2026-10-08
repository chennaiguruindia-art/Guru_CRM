<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\MaintenanceContract;
use App\Models\MaintenanceRecord;
use App\Models\MaintenanceSchedule;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaintenanceController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $records = MaintenanceRecord::with(['customer', 'employee'])->latest('visit_date')->paginate(15);
        $schedules = MaintenanceSchedule::with(['contract.customer', 'assignedEmployee'])->where('scheduled_date', '>=', now()->toDateString())->orderBy('scheduled_date')->take(5)->get();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'records' => $records, 'schedules' => $schedules]);
        }

        return view('maintenance.index', compact('records', 'schedules'));
    }

    public function create(): View
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        $employees = User::where('status', 'active')->get();
        return view('maintenance.create', compact('customers', 'employees'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'visit_date' => ['required', 'date'],
            'employee_id' => ['nullable', 'exists:users,id'],
            'activity_type' => ['required', 'string'],
            'area_details' => ['nullable', 'string'],
            'work_description' => ['nullable', 'string'],
            'remarks' => ['nullable', 'string'],
        ]);

        $record = MaintenanceRecord::create($validated);
        AuditLogger::log('create', 'maintenance_records', $record->id);

        return redirect()->route('maintenance.index')->with('success', 'Maintenance record added successfully!');
    }

    public function show(MaintenanceRecord $maintenance): View
    {
        $maintenance->load(['customer', 'employee']);
        return view('maintenance.show', compact('maintenance'));
    }
}
