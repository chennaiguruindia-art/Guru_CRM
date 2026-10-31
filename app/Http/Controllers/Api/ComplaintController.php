<?php

namespace App\Http\Controllers\Api;

use App\Models\Complaint;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ComplaintController extends BaseApiController
{
    /**
     * Display a listing of complaints.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Complaint::with(['customer', 'project', 'assignedTo'])->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $paginator = $query->paginate($perPage);

        return $this->paginatedResponse($paginator, 'Complaints retrieved successfully');
    }

    /**
     * Store a newly created complaint.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => ['required', 'string', 'in:Low,Medium,High,Urgent'],
            'assigned_to_id' => ['nullable', 'exists:users,id'],
        ]);

        $validated['ticket_number'] = Complaint::generateNumber();
        $validated['status'] = 'Open';
        $validated['created_by_id'] = $request->user()->id;

        $complaint = Complaint::create($validated);

        AuditLogger::log('create', 'complaints', $complaint->id, null, $complaint->toArray());

        return $this->successResponse(
            $complaint->fresh(['customer', 'project', 'assignedTo']),
            'Complaint registered successfully',
            201
        );
    }

    /**
     * Display the specified complaint.
     */
    public function show(Complaint $complaint): JsonResponse
    {
        $complaint->load(['customer', 'project', 'assignedTo', 'createdBy']);

        return $this->successResponse($complaint, 'Complaint retrieved successfully');
    }

    /**
     * Update the specified complaint.
     */
    public function update(Request $request, Complaint $complaint): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => ['sometimes', 'required', 'exists:customers,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'subject' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
            'priority' => ['sometimes', 'required', 'string', 'in:Low,Medium,High,Urgent'],
            'status' => ['sometimes', 'required', 'string', 'in:Open,Assigned,In Progress,Resolved,Closed'],
            'assigned_to_id' => ['nullable', 'exists:users,id'],
            'resolution_notes' => ['nullable', 'string'],
        ]);

        $oldValues = $complaint->toArray();
        $complaint->update($validated);

        AuditLogger::log('update', 'complaints', $complaint->id, $oldValues, $complaint->fresh()->toArray());

        return $this->successResponse($complaint->fresh(['customer', 'project', 'assignedTo']), 'Complaint updated');
    }

    /**
     * Mark complaint as resolved with notes.
     */
    public function resolve(Request $request, Complaint $complaint): JsonResponse
    {
        $validated = $request->validate([
            'resolution_notes' => ['required', 'string'],
        ]);

        $complaint->update([
            'status' => 'Resolved',
            'resolution_notes' => $validated['resolution_notes'],
            'resolved_at' => now(),
        ]);

        AuditLogger::log('resolve', 'complaints', $complaint->id, null, $validated);

        return $this->successResponse($complaint->fresh(['customer', 'project', 'assignedTo']), 'Complaint marked as resolved');
    }

    /**
     * Remove the specified complaint.
     */
    public function destroy(Complaint $complaint): JsonResponse
    {
        $id = $complaint->id;
        $complaint->delete();

        AuditLogger::log('delete', 'complaints', $id);

        return $this->successResponse(null, 'Complaint deleted successfully');
    }
}
