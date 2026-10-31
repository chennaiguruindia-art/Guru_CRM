<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\LeadRequest;
use App\Models\Branch;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Services\AuditLogger;
use App\Services\LeadConversionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LeadController extends BaseApiController
{
    /**
     * Display a paginated listing of leads/visits.
     */
    public function index(Request $request): JsonResponse
    {
        return $this->getListing($request, null);
    }

    /**
     * Cold Calls channel.
     */
    public function coldCalls(Request $request): JsonResponse
    {
        return $this->getListing($request, null);
    }

    /**
     * Promotion Emails channel.
     */
    public function promotionEmails(Request $request): JsonResponse
    {
        return $this->getListing($request, Lead::CHANNELS['promotion_email']);
    }

    /**
     * Existing Client Visits channel.
     */
    public function clientVisits(Request $request): JsonResponse
    {
        return $this->getListing($request, Lead::CHANNELS['existing_client']);
    }

    /**
     * Store a newly created lead/visit.
     */
    public function store(LeadRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $data['lead_code'] = Lead::generateCode();
        $data['created_by_id'] = $user->id;
        $data['assigned_to_id'] = $user->id;
        $data['branch_id'] = $user->branchId();
        $data['source'] ??= Lead::DEFAULT_SOURCE;

        if ($request->hasFile('visiting_card_photo')) {
            $data['visiting_card_photo'] = $request->file('visiting_card_photo')
                ->store('visiting-cards', 'public');
        }

        $lead = Lead::create($data);

        // Record initial activity
        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'activity_type' => 'Created',
            'notes' => 'Visit created from source: ' . $lead->source,
            'follow_up_date' => $lead->follow_up_date,
        ]);

        AuditLogger::log('create', 'leads', $lead->id, null, $lead->toArray());

        return $this->successResponse(
            $this->formatLead($lead->fresh(['assignedTo', 'createdBy', 'branch'])),
            'Visit registered successfully',
            201
        );
    }

    /**
     * Display the specified lead.
     */
    public function show(Lead $lead): JsonResponse
    {
        $lead->load([
            'assignedTo',
            'createdBy',
            'convertedCustomer',
            'activities.user',
            'branch',
        ]);

        return $this->successResponse($this->formatLead($lead), 'Lead retrieved successfully');
    }

    /**
     * Update the specified lead.
     */
    public function update(LeadRequest $request, Lead $lead): JsonResponse
    {
        $user = $request->user();
        $oldValues = $lead->toArray();
        $data = $request->validated();

        if ($request->hasFile('visiting_card_photo')) {
            $previous = $lead->visiting_card_photo;
            $data['visiting_card_photo'] = $request->file('visiting_card_photo')
                ->store('visiting-cards', 'public');

            if ($previous) {
                Storage::disk('public')->delete($previous);
            }
        } else {
            unset($data['visiting_card_photo']);
        }

        $lead->update($data);

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'activity_type' => 'Updated',
            'notes' => 'Visit details updated',
            'follow_up_date' => $lead->follow_up_date,
        ]);

        AuditLogger::log('update', 'leads', $lead->id, $oldValues, $lead->fresh()->toArray());

        return $this->successResponse(
            $this->formatLead($lead->fresh(['assignedTo', 'createdBy', 'branch'])),
            'Visit updated successfully'
        );
    }

    /**
     * Remove the specified lead.
     */
    public function destroy(Lead $lead): JsonResponse
    {
        $id = $lead->id;
        $lead->delete();

        AuditLogger::log('delete', 'leads', $id);

        return $this->successResponse(null, 'Visit deleted successfully');
    }

    /**
     * Convert lead into customer/project/AMC.
     */
    public function convert(Request $request, Lead $lead, LeadConversionService $service): JsonResponse
    {
        if ($lead->status === 'Converted') {
            return $this->errorResponse('This visit has already been converted to a client.', 422);
        }

        $result = $service->convert($lead, $request->all());

        return $this->successResponse($result, 'Visit successfully converted to client');
    }

    /**
     * Add activity / follow-up record to lead.
     */
    public function addActivity(Request $request, Lead $lead): JsonResponse
    {
        $validated = $request->validate([
            'activity_type' => ['required', 'string', 'max:100'],
            'notes' => ['required', 'string'],
            'follow_up_date' => ['nullable', 'date'],
        ]);

        $activity = LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => $request->user()->id,
            'activity_type' => $validated['activity_type'],
            'notes' => $validated['notes'],
            'follow_up_date' => $validated['follow_up_date'] ?? null,
        ]);

        if (! empty($validated['follow_up_date'])) {
            $lead->update(['follow_up_date' => $validated['follow_up_date']]);
        }

        AuditLogger::log('activity', 'leads', $lead->id, null, $activity->toArray());

        return $this->successResponse($activity->load('user'), 'Activity recorded successfully', 201);
    }

    /**
     * Common query listing logic.
     */
    private function getListing(Request $request, ?string $sourceFilter): JsonResponse
    {
        $query = Lead::with(['assignedTo', 'createdBy', 'branch'])->latest();

        if ($sourceFilter !== null) {
            $query->where('source', $sourceFilter);
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('lead_code', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('source')) {
            $query->where('source', $request->input('source'));
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }
        if ($request->filled('assigned_to_id')) {
            $query->where('assigned_to_id', $request->input('assigned_to_id'));
        }
        if ($request->filled('branch_id') && $request->user()?->canSeeAllBranches()) {
            $query->where('branch_id', $request->input('branch_id'));
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $paginator = $query->paginate($perPage);

        // Format each lead with photo URL
        $paginator->getCollection()->transform(fn ($item) => $this->formatLead($item));

        return $this->paginatedResponse($paginator, 'Visits retrieved successfully');
    }

    /**
     * Format a Lead model for API response.
     */
    private function formatLead(Lead $lead): array
    {
        $array = $lead->toArray();
        $array['visiting_card_photo_url'] = $lead->visiting_card_photo
            ? asset('storage/' . $lead->visiting_card_photo)
            : null;

        return $array;
    }
}
