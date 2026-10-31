<?php

namespace App\Http\Controllers\Api;

use App\Models\ProjectTask;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends BaseApiController
{
    /**
     * Display listing of tasks.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ProjectTask::with(['project', 'assignedTo'])->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->input('project_id'));
        }

        // Filter tasks assigned to current user
        if ($request->boolean('my_tasks')) {
            $query->where('assigned_to_id', $request->user()->id);
        } elseif ($request->filled('assigned_to_id')) {
            $query->where('assigned_to_id', $request->input('assigned_to_id'));
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $paginator = $query->paginate($perPage);

        return $this->paginatedResponse($paginator, 'Tasks retrieved successfully');
    }

    /**
     * Store a newly created task.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'assigned_to_id' => ['nullable', 'exists:users,id'],
            'priority' => ['required', 'string', 'in:Low,Medium,High,Urgent'],
            'status' => ['required', 'string', 'in:Pending,In Progress,Completed,Cancelled'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
        ]);

        $task = ProjectTask::create($validated);

        AuditLogger::log('create', 'project_tasks', $task->id, null, $task->toArray());

        return $this->successResponse(
            $task->fresh(['project', 'assignedTo']),
            'Task created successfully',
            201
        );
    }

    /**
     * Display the specified task.
     */
    public function show(ProjectTask $task): JsonResponse
    {
        $task->load(['project', 'assignedTo']);

        return $this->successResponse($task, 'Task retrieved successfully');
    }

    /**
     * Update the specified task.
     */
    public function update(Request $request, ProjectTask $task): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'assigned_to_id' => ['nullable', 'exists:users,id'],
            'priority' => ['sometimes', 'required', 'string', 'in:Low,Medium,High,Urgent'],
            'status' => ['sometimes', 'required', 'string', 'in:Pending,In Progress,Completed,Cancelled'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
        ]);

        $oldValues = $task->toArray();
        $task->update($validated);

        AuditLogger::log('update', 'project_tasks', $task->id, $oldValues, $task->fresh()->toArray());

        return $this->successResponse($task->fresh(['project', 'assignedTo']), 'Task updated successfully');
    }

    /**
     * Quick status update for mobile task toggling.
     */
    public function updateStatus(Request $request, ProjectTask $task): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:Pending,In Progress,Completed,Cancelled'],
        ]);

        $task->update(['status' => $validated['status']]);

        AuditLogger::log('status_change', 'project_tasks', $task->id, null, ['status' => $validated['status']]);

        return $this->successResponse($task->fresh(['project', 'assignedTo']), 'Task status updated');
    }

    /**
     * Remove the specified task.
     */
    public function destroy(ProjectTask $task): JsonResponse
    {
        $id = $task->id;
        $task->delete();

        AuditLogger::log('delete', 'project_tasks', $id);

        return $this->successResponse(null, 'Task deleted successfully');
    }
}
