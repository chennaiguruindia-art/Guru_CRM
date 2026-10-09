<?php

namespace App\Http\Controllers;

use App\Http\Requests\LeadRequest;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\LeadConversionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class LeadController extends Controller
{
    /**
     * /leads is the resource base behind Cold Calls: same list, no sidebar
     * entry of its own now that Visits has been dropped from the menu.
     */
    public function index(Request $request): View|JsonResponse
    {
        return $this->listing($request, 'cold_call', null);
    }

    /**
     * Cold Calls / Direct Call carries every Visit whatever its channel — it
     * took over from the old Visits list.
     */
    public function coldCalls(Request $request): View|JsonResponse
    {
        return $this->listing($request, 'cold_call', null);
    }

    /**
     * Promotion Email / Call — the same Visit records, filtered to that channel.
     */
    public function promotionEmails(Request $request): View|JsonResponse
    {
        return $this->listing($request, 'promotion_email', Lead::CHANNELS['promotion_email']);
    }

    /**
     * Existing Client Visit — the same Visit records, filtered to that channel.
     */
    public function clientVisits(Request $request): View|JsonResponse
    {
        return $this->listing($request, 'existing_client', Lead::CHANNELS['existing_client']);
    }

    /**
     * @param  string       $channel       Sidebar key: drives the heading and the Add button.
     * @param  string|null  $sourceFilter  null lists every Visit regardless of channel.
     */
    private function listing(Request $request, string $channel, ?string $sourceFilter): View|JsonResponse
    {
        $query = Lead::with(['assignedTo', 'createdBy'])->latest();

        if ($sourceFilter !== null) {
            $query->where('source', $sourceFilter);
        }

        // Search
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

        // Filters
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

        $leads = $query->paginate(15)->withQueryString();
        $users = User::where('status', 'active')->get();

        // Source is stamped, not typed, so the filter offers whatever values
        // actually exist — legacy rows still carry the old channel names.
        $sources = Lead::withTrashed()->distinct()->whereNotNull('source')
            ->orderBy('source')->pluck('source');

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $leads,
            ]);
        }

        return view('leads.index', compact('leads', 'users', 'channel', 'sources'));
    }

    /**
     * Which list a Visit of this source belongs to — used to send people back
     * to the page they came from rather than to a catch-all.
     *
     * Anything unrecognised is the Cold Call catch-all, and the target is
     * downgraded to it whenever the current user could not open that list:
     * a Field Marketer holds only Cold Calls, so they are never bounced
     * into a page their own permission would 403.
     */
    private function listRouteFor(?string $source): string
    {
        $channel = array_search($source, Lead::CHANNELS, true) ?: 'cold_call';

        if (! auth()->user()?->can(Lead::CHANNEL_LISTS[$channel][2])) {
            $channel = 'cold_call';
        }

        return Lead::CHANNEL_LISTS[$channel][1];
    }

    /**
     * $source is the channel the Add button was pressed on, so a Visit filed
     * under Cold Call is stamped Cold Call without anybody picking it.
     */
    public function create(Request $request): View
    {
        // ?source=cold_call — set by the Add button on the channel's list page.
        $channel = $request->query('source');
        $channel = is_string($channel) && array_key_exists($channel, Lead::CHANNELS)
            ? $channel
            : 'cold_call';

        // …and only where they may actually file one.
        if (! $request->user()?->can(Lead::CHANNEL_LISTS[$channel][2])) {
            $channel = 'cold_call';
        }

        $source = Lead::CHANNELS[$channel];

        return view('leads.create', compact('source', 'channel'));
    }

    public function store(LeadRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $data['lead_code'] = Lead::generateCode();
        $data['created_by_id'] = auth()->id();

        // The form no longer asks who the Visit is for — the person filing it
        // owns it, which keeps the detail row and the list column meaningful.
        $data['assigned_to_id'] = auth()->id();

        // The form has no Source dropdown any more — fall back to the plain
        // Visits channel when nothing specified one.
        $data['source'] ??= Lead::DEFAULT_SOURCE;

        if ($request->hasFile('visiting_card_photo')) {
            $data['visiting_card_photo'] = $request->file('visiting_card_photo')
                ->store('visiting-cards', 'public');
        }

        $lead = Lead::create($data);

        // Record initial activity
        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => auth()->id(),
            'activity_type' => 'Created',
            'notes' => 'Visit created from source: ' . $lead->source,
            'follow_up_date' => $lead->follow_up_date,
        ]);

        AuditLogger::log('create', 'leads', $lead->id, null, $lead->toArray());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Visit created successfully',
                'data' => $lead,
            ], 201);
        }

        return redirect()->route('leads.show', $lead)->with('success', 'Visit created successfully!');
    }

    public function show(Lead $lead): View
    {
        $lead->load(['assignedTo', 'createdBy', 'convertedCustomer', 'activities.user']);
        $users = User::where('status', 'active')->get();

        return view('leads.show', compact('lead', 'users'));
    }

    public function edit(Lead $lead): View
    {
        return view('leads.edit', compact('lead'));
    }

    public function update(LeadRequest $request, Lead $lead): RedirectResponse|JsonResponse
    {
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
            // validated() drops the key when no file was sent — must not
            // accidentally null out an existing photo on a plain edit.
            unset($data['visiting_card_photo']);
        }

        $lead->update($data);

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => auth()->id(),
            'activity_type' => 'Updated',
            'notes' => 'Visit details updated. Status: ' . $lead->status,
            'follow_up_date' => $lead->follow_up_date,
        ]);

        AuditLogger::log('update', 'leads', $lead->id, $oldValues, $lead->fresh()->toArray());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Visit updated successfully',
                'data' => $lead,
            ]);
        }

        return redirect()->route('leads.show', $lead)->with('success', 'Visit updated successfully!');
    }

    public function destroy(Request $request, Lead $lead): RedirectResponse|JsonResponse
    {
        $id = $lead->id;
        $photo = $lead->visiting_card_photo;
        $backTo = $this->listRouteFor($lead->source);
        $lead->delete();

        if ($photo) {
            Storage::disk('public')->delete($photo);
        }

        AuditLogger::log('delete', 'leads', $id);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Visit deleted successfully',
            ]);
        }

        return redirect()->route($backTo)->with('success', 'Visit removed successfully.');
    }

    public function convert(Request $request, Lead $lead, LeadConversionService $service): RedirectResponse|JsonResponse
    {
        // Converting hands over the customer record, so it is reserved for the
        // Super Admin — checked here as well as by the route permission, so a
        // grant made in the role matrix cannot widen it.
        abort_unless($request->user()?->hasRole('super-admin'), 403);

        $request->validate([
            'create_project' => ['nullable', 'boolean'],
            'project_name' => ['nullable', 'string', 'max:255'],
        ]);

        $result = $service->convert($lead, $request->all());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Visit converted to Client successfully',
                'data' => $result,
            ]);
        }

        return redirect()->route('customers.show', $result['customer'])->with('success', 'Visit successfully converted to Client!');
    }

    public function addActivity(Request $request, Lead $lead): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'activity_type' => ['required', 'string'],
            'notes' => ['required', 'string'],
            'follow_up_date' => ['nullable', 'date'],
        ]);

        $activity = LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => auth()->id(),
            'activity_type' => $validated['activity_type'],
            'notes' => $validated['notes'],
            'follow_up_date' => $validated['follow_up_date'] ?? null,
        ]);

        if (!empty($validated['follow_up_date'])) {
            $lead->update(['follow_up_date' => $validated['follow_up_date']]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Activity logged successfully',
                'data' => $activity->load('user'),
            ]);
        }

        return back()->with('success', 'Activity logged successfully.');
    }
}
