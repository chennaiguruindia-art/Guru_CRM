<?php

namespace App\Http\Controllers\Api;

use App\Models\Project;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Project::with(['customer', 'projectManager'])->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('project_code', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $paginator = $query->paginate($perPage);

        return $this->paginatedResponse($paginator, 'Projects retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'customer_id' => ['required', 'exists:customers,id'],
            'quotation_id' => ['nullable', 'exists:quotations,id'],
            'project_manager_id' => ['nullable', 'exists:users,id'],
            'start_date' => ['nullable', 'date'],
            'expected_end_date' => ['nullable', 'date'],
            'contract_value' => ['nullable', 'numeric', 'min:0'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'in:Planning,In Progress,On Hold,Completed,Cancelled'],
            'progress_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['project_code'] = Project::generateCode();

        $project = Project::create($validated);

        AuditLogger::log('create', 'projects', $project->id, null, $project->toArray());

        return $this->successResponse(
            $project->fresh(['customer', 'projectManager']),
            'Project created successfully',
            201
        );
    }

    public function show(Project $project): JsonResponse
    {
        $project->load(['customer', 'projectManager', 'tasks', 'invoices', 'workOrders']);

        return $this->successResponse($project, 'Project retrieved successfully');
    }

    public function update(Request $request, Project $project): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'customer_id' => ['sometimes', 'required', 'exists:customers,id'],
            'quotation_id' => ['nullable', 'exists:quotations,id'],
            'project_manager_id' => ['nullable', 'exists:users,id'],
            'start_date' => ['nullable', 'date'],
            'expected_end_date' => ['nullable', 'date'],
            'actual_end_date' => ['nullable', 'date'],
            'contract_value' => ['nullable', 'numeric', 'min:0'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'status' => ['sometimes', 'required', 'string', 'in:Planning,In Progress,On Hold,Completed,Cancelled'],
            'progress_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'description' => ['nullable', 'string'],
        ]);

        $old = $project->toArray();
        $project->update($validated);

        AuditLogger::log('update', 'projects', $project->id, $old, $project->fresh()->toArray());

        return $this->successResponse($project->fresh(['customer', 'projectManager']), 'Project updated');
    }

    public function destroy(Project $project): JsonResponse
    {
        $id = $project->id;
        $project->delete();

        AuditLogger::log('delete', 'projects', $id);

        return $this->successResponse(null, 'Project deleted');
    }
}
