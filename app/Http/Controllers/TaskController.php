<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View|JsonResponse
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

        $tasks = $query->paginate(15)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $tasks]);
        }

        return view('tasks.index', compact('tasks'));
    }

    public function create(): View
    {
        $projects = Project::where('status', '!=', 'Completed')->get();
        $users = User::where('status', 'active')->get();
        return view('tasks.create', compact('projects', 'users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'assigned_to_id' => ['nullable', 'exists:users,id'],
            'priority' => ['required', 'string'],
            'status' => ['required', 'string'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
        ]);

        $task = ProjectTask::create($validated);
        AuditLogger::log('create', 'project_tasks', $task->id);

        return redirect()->route('tasks.index')->with('success', 'Task created successfully!');
    }

    public function show(ProjectTask $task): View
    {
        $task->load(['project', 'assignedTo']);
        return view('tasks.show', compact('task'));
    }

    public function destroy(ProjectTask $task): RedirectResponse
    {
        AuditLogger::log('delete', 'project_tasks', $task->id);
        $task->delete();
        return redirect()->route('tasks.index')->with('success', 'Task removed!');
    }
}
