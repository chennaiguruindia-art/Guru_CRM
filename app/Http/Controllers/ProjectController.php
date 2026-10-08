<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Project;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = Project::with(['customer', 'projectManager'])->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('project_code', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $projects = $query->paginate(15)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $projects,
            ]);
        }

        return view('projects.index', compact('projects'));
    }

    public function create(): View
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        $managers = User::where('status', 'active')->get();
        return view('projects.create', compact('customers', 'managers'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'customer_id' => ['required', 'exists:customers,id'],
            'project_manager_id' => ['nullable', 'exists:users,id'],
            'start_date' => ['nullable', 'date'],
            'expected_end_date' => ['nullable', 'date'],
            'contract_value' => ['nullable', 'numeric', 'min:0'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'string'],
            'progress_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['project_code'] = Project::generateCode();
        $project = Project::create($validated);

        AuditLogger::log('create', 'projects', $project->id, null, $project->toArray());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Project initiated successfully',
                'data' => $project,
            ], 201);
        }

        return redirect()->route('projects.show', $project)->with('success', 'Project created successfully!');
    }

    public function show(Project $project): View
    {
        $project->load(['customer', 'projectManager', 'tasks', 'workOrders']);
        return view('projects.show', compact('project'));
    }

    public function edit(Project $project): View
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        $managers = User::where('status', 'active')->get();
        return view('projects.edit', compact('project', 'customers', 'managers'));
    }

    public function update(Request $request, Project $project): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'customer_id' => ['required', 'exists:customers,id'],
            'project_manager_id' => ['nullable', 'exists:users,id'],
            'start_date' => ['nullable', 'date'],
            'expected_end_date' => ['nullable', 'date'],
            'actual_end_date' => ['nullable', 'date'],
            'contract_value' => ['nullable', 'numeric', 'min:0'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'actual_cost' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'string'],
            'progress_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'description' => ['nullable', 'string'],
        ]);

        $project->update($validated);
        AuditLogger::log('update', 'projects', $project->id);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Project updated successfully',
                'data' => $project,
            ]);
        }

        return redirect()->route('projects.show', $project)->with('success', 'Project updated successfully!');
    }

    public function destroy(Request $request, Project $project): RedirectResponse|JsonResponse
    {
        $id = $project->id;
        $project->delete();

        AuditLogger::log('delete', 'projects', $id);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Project deleted successfully',
            ]);
        }

        return redirect()->route('projects.index')->with('success', 'Project removed successfully.');
    }
}
