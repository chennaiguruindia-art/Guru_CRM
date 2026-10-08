<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Customer;
use App\Models\Project;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComplaintController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = Complaint::with(['customer', 'assignedTo'])->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $complaints = $query->paginate(15)->withQueryString();

        $openCount = Complaint::whereIn('status', ['Open', 'Assigned', 'In Progress'])->count();
        $resolvedCount = Complaint::where('status', 'Resolved')->count();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $complaints]);
        }

        return view('complaints.index', compact('complaints', 'openCount', 'resolvedCount'));
    }

    public function create(): View
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        $projects = Project::all();
        $users = User::where('status', 'active')->get();
        return view('complaints.create', compact('customers', 'projects', 'users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => ['required', 'string'],
            'assigned_to_id' => ['nullable', 'exists:users,id'],
        ]);

        $validated['ticket_number'] = Complaint::generateNumber();
        $validated['status'] = 'Open';
        $validated['created_by_id'] = auth()->id();

        $complaint = Complaint::create($validated);
        AuditLogger::log('create', 'complaints', $complaint->id);

        return redirect()->route('complaints.index')->with('success', 'Complaint ticket registered!');
    }

    public function show(Complaint $complaint): View
    {
        $complaint->load(['customer', 'project', 'assignedTo', 'createdBy']);
        return view('complaints.show', compact('complaint'));
    }

    public function update(Request $request, Complaint $complaint): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string'],
            'resolution_notes' => ['nullable', 'string'],
        ]);

        if ($validated['status'] === 'Resolved' && empty($complaint->resolved_at)) {
            $validated['resolved_at'] = now();
        }

        $complaint->update($validated);
        AuditLogger::log('update', 'complaints', $complaint->id);

        return back()->with('success', 'Ticket status updated!');
    }

    public function destroy(Complaint $complaint): RedirectResponse
    {
        AuditLogger::log('delete', 'complaints', $complaint->id);
        $complaint->delete();
        return redirect()->route('complaints.index')->with('success', 'Ticket removed!');
    }
}
