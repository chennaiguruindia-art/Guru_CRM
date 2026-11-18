<?php

namespace App\Http\Controllers;

use App\Http\Requests\LeadRequest;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Document;
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
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadController extends Controller
{
    /**
     * /leads is the resource base behind Cold Calls: same list, no sidebar
     * entry of its own now that Visits has been dropped from the menu.
     */
    public function index(Request $request): View|JsonResponse
    {
        return $this->listing($request, 'cold_call', Lead::CHANNELS['cold_call']);
    }

    /**
     * Cold Calls / Direct Call: only shows visits with source = Cold Call.
     * Existing Client and Promotion Email visits are excluded.
     */
    public function coldCalls(Request $request): View|JsonResponse
    {
        return $this->listing($request, 'cold_call', Lead::CHANNELS['cold_call']);
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

        // Narrowing by branch is an Administration control. Everybody else is
        // already held to their own branch by the scope on Lead, so honouring
        // the parameter for them could only ever empty their list.
        $branches = collect();
        if ($request->user()?->canSeeAllBranches()) {
            $branches = Branch::where('status', 'active')->orderBy('name')->get();

            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->input('branch_id'));
            }
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

        return view('leads.index', compact('leads', 'users', 'channel', 'sources', 'branches'));
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

        $users = User::where('status', 'active')
            ->when($request->user() && ! $request->user()->hasRole('super-admin'), function ($q) use ($request) {
                $q->where('branch_id', $request->user()->branch_id);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('leads.create', compact('source', 'channel', 'users'));
    }

    public function store(LeadRequest $request): RedirectResponse|JsonResponse
    {
        if ($request->hasFile('import_file')) {
            return $this->import($request);
        }

        $data = $request->validated();
        $data['lead_code'] = Lead::generateCode();
        $data['created_by_id'] = auth()->id();

        // Ensure name is present (falls back to company_name or contact_person if site name empty)
        if (empty($data['name'])) {
            $data['name'] = $data['company_name'] ?: ($data['contact_person'] ?: 'Promotion Lead');
        }

        // Employee who spoke with or handled this lead
        $empName = trim($request->input('employee_name') ?? '');
        $data['employee_name'] = $empName ?: null;

        if (! empty($empName)) {
            $matchedUser = User::where('name', $empName)
                ->orWhere('username', $empName)
                ->orWhere('email', $empName)
                ->first();
            $data['assigned_to_id'] = $matchedUser?->id ?: ($request->input('assigned_to_id') ?: auth()->id());
        } else {
            $data['assigned_to_id'] = $request->input('assigned_to_id') ?: auth()->id();
        }

        // …and it belongs to the branch they work out of. Stamped rather than
        // chosen, so a later reassignment cannot move a visit into somebody
        // else's patch, and a mis-pick cannot leak one across branches.
        $data['branch_id'] = auth()->user()->branchId();

        // The form has no Source dropdown any more — fall back to the plain
        // Visits channel when nothing specified one.
        $data['source'] ??= Lead::DEFAULT_SOURCE;
        $data['status'] = $request->input('status') ?: ($data['source'] === Lead::CHANNELS['promotion_email'] ? 'Contacted' : 'New');
        $data['client_type'] ??= 'New Client';
        $data['priority'] ??= 'Medium';
        $data['expected_value'] ??= 0;

        if ($request->hasFile('visiting_card_photo')) {
            $data['visiting_card_photo'] = $request->file('visiting_card_photo')
                ->store('visiting-cards', 'public');
        }

        $lead = Lead::create($data);

        // Upload any attached existing client records / documents
        if ($request->hasFile('client_documents')) {
            $category = $request->input('document_category', 'Existing Client Record');
            foreach ($request->file('client_documents') as $file) {
                if ($file && $file->isValid()) {
                    $originalName = $file->getClientOriginalName();
                    $extension = strtolower($file->getClientOriginalExtension());
                    $size = $file->getSize();
                    $path = $file->store('lead-documents', 'public');

                    Document::create([
                        'documentable_type' => Lead::class,
                        'documentable_id' => $lead->id,
                        'title' => pathinfo($originalName, PATHINFO_FILENAME),
                        'category' => $category,
                        'file_path' => $path,
                        'file_type' => $extension,
                        'file_size' => $size,
                        'uploaded_by_id' => auth()->id(),
                    ]);
                }
            }
        }

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

    /**
     * Download sample CSV data template for existing clients/leads.
     */
    public function downloadSampleTemplate(Request $request): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sample_existing_clients_template.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM so Excel opens with correct encoding on Windows
            fputs($handle, "\xEF\xBB\xBF");

            // CSV Columns Header
            fputcsv($handle, [
                'Site Name',
                'Company Name',
                'Contact Person',
                'Phone',
                'Email',
                'WhatsApp Number',
                'Address',
                'City',
                'State',
                'Pincode',
                'Service Type (AMC or Single Project)',
                'Purpose of Visit',
                'Follow-up Date (YYYY-MM-DD)',
                'Remarks'
            ]);

            // Sample Existing Client Row 1
            fputcsv($handle, [
                'Taj Grand Garden - Phase 1',
                'Taj Hotels & Resorts Ltd',
                'Suresh Verma',
                '9840123456',
                'suresh@tajgardens.com',
                '9840123456',
                'No 10 GST Road, Guindy',
                'Chennai',
                'Tamil Nadu',
                '600032',
                'AMC',
                'Contract Renewal',
                now()->addDays(7)->format('Y-m-d'),
                'Existing AMC client renewal for year 2026'
            ]);

            // Sample Existing Client Row 2
            fputcsv($handle, [
                'Adyar Villa Green',
                'Adyar Living Spaces Pvt Ltd',
                'Kavitha Ramesh',
                '9884567890',
                'kavitha@adyarvilla.com',
                '9884567890',
                '24 Beach Road, Adyar',
                'Chennai',
                'Tamil Nadu',
                '600020',
                'Single Project',
                'Site Measurement',
                '',
                'Previous customer requesting terrace garden redesign'
            ]);

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Import existing client CSV data into the database.
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'import_file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
            'source' => ['nullable', 'string'],
        ]);

        $file = $request->file('import_file');
        $filePath = $file->getRealPath();

        $handle = fopen($filePath, 'r');
        if (! $handle) {
            return back()->with('error', 'Unable to open uploaded CSV file.');
        }

        // Read and strip BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $rawHeader = fgetcsv($handle);
        if (! $rawHeader) {
            fclose($handle);
            return back()->with('error', 'Uploaded CSV file is empty.');
        }

        // Normalize header names to lowercase alphanumeric for flexible matching
        $headerMap = [];
        foreach ($rawHeader as $idx => $colName) {
            $normalized = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', (string)$colName)));
            $headerMap[$normalized] = $idx;
        }

        // Helper to get column value by probable header names or fallback column index
        $getVal = function(array $row, array $keys, int $fallbackIdx) use ($headerMap): string {
            foreach ($keys as $k) {
                if (isset($headerMap[$k]) && isset($row[$headerMap[$k]])) {
                    return trim((string)$row[$headerMap[$k]]);
                }
            }
            return isset($row[$fallbackIdx]) ? trim((string)$row[$fallbackIdx]) : '';
        };

        $source = $request->input('source');
        if (! $source || ! in_array($source, Lead::CHANNELS, true)) {
            $source = Lead::CHANNELS['existing_client'];
        }

        $branchId = auth()->user()?->branchId();
        $userId = auth()->id();
        $importedCount = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (empty(array_filter($row, fn($v) => trim((string)$v) !== ''))) {
                continue;
            }

            $siteName = $getVal($row, ['sitename', 'clientname', 'name', 'site'], 0);
            $companyName = $getVal($row, ['companyname', 'company', 'organization', 'organisation'], 1);
            $contactPerson = $getVal($row, ['contactperson', 'person', 'contact'], 2);
            $phone = $getVal($row, ['phone', 'mobile', 'phonenumber', 'contactnumber'], 3);
            $email = $getVal($row, ['email', 'emailaddress'], 4);
            $whatsapp = $getVal($row, ['whatsappnumber', 'whatsapp', 'whatsappno'], 5);
            $address = $getVal($row, ['address', 'street'], 6);
            $city = $getVal($row, ['city'], 7);
            $state = $getVal($row, ['state'], 8);
            $pincode = $getVal($row, ['pincode', 'postalcode', 'zip'], 9);
            $serviceType = $getVal($row, ['servicetype', 'service'], 10);
            $purpose = $getVal($row, ['purposeofvisit', 'purpose'], 11);
            $rawDate = $getVal($row, ['followupdate', 'nextfollowup', 'followup'], 12);
            $remarks = $getVal($row, ['remarks', 'notes', 'comments'], 13);

            // If Site Name is empty, fallback to company name or contact person
            if (empty($siteName)) {
                $siteName = $companyName ?: $contactPerson;
            }

            if (empty($siteName) || empty($phone)) {
                continue;
            }

            if (! in_array($serviceType, Lead::SERVICE_TYPES, true)) {
                $serviceType = 'AMC';
            }

            $followUpDate = null;
            if (! empty($rawDate) && strtotime($rawDate)) {
                $followUpDate = date('Y-m-d', strtotime($rawDate));
            }

            $lead = Lead::create([
                'lead_code' => Lead::generateCode(),
                'name' => $siteName,
                'company_name' => $companyName ?: null,
                'contact_person' => $contactPerson ?: null,
                'phone' => $phone,
                'email' => $email ?: null,
                'whatsapp' => $whatsapp ?: null,
                'address' => $address ?: null,
                'city' => $city ?: 'Chennai',
                'state' => $state ?: 'Tamil Nadu',
                'pincode' => $pincode ?: null,
                'service_type' => $serviceType,
                'purpose_of_visit' => $purpose ?: 'Contract Renewal',
                'follow_up_date' => $followUpDate,
                'remarks' => $remarks ?: 'Imported existing client record',
                'source' => $source,
                'client_type' => 'Existing Client',
                'status' => 'New',
                'priority' => 'Medium',
                'assigned_to_id' => $userId,
                'created_by_id' => $userId,
                'branch_id' => $branchId,
            ]);

            LeadActivity::create([
                'lead_id' => $lead->id,
                'user_id' => $userId,
                'activity_type' => 'Imported',
                'notes' => 'Existing client record imported from CSV.',
                'follow_up_date' => $lead->follow_up_date,
            ]);

            AuditLogger::log('import', 'leads', $lead->id, null, $lead->toArray());
            $importedCount++;
        }

        fclose($handle);

        $targetRoute = $this->listRouteFor($source);

        return redirect()->route($targetRoute)->with('success', "{$importedCount} Existing Client record(s) imported and saved to database successfully!");
    }

    public function show(Lead $lead): View
    {
        $lead->load(['assignedTo', 'createdBy', 'convertedCustomer', 'activities.user']);
        $users = User::where('status', 'active')->get();

        return view('leads.show', compact('lead', 'users'));
    }

    public function edit(Lead $lead): View
    {
        $users = User::where('status', 'active')
            ->when(! auth()->user()->hasRole('super-admin'), function ($q) {
                $q->where('branch_id', auth()->user()->branch_id);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('leads.edit', compact('lead', 'users'));
    }

    public function update(LeadRequest $request, Lead $lead): RedirectResponse|JsonResponse
    {
        $oldValues = $lead->toArray();
        $data = $request->validated();
        $data['priority'] ??= $lead->priority ?? 'Medium';

        if ($request->has('employee_name')) {
            $empName = trim($request->input('employee_name') ?? '');
            $data['employee_name'] = $empName ?: null;
            if (! empty($empName)) {
                $matchedUser = User::where('name', $empName)
                    ->orWhere('username', $empName)
                    ->orWhere('email', $empName)
                    ->first();
                if ($matchedUser) {
                    $data['assigned_to_id'] = $matchedUser->id;
                }
            }
        } elseif ($request->filled('assigned_to_id')) {
            $data['assigned_to_id'] = $request->input('assigned_to_id');
        }

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

        // Upload any attached existing client records / documents on edit
        if ($request->hasFile('client_documents')) {
            $category = $request->input('document_category', 'Existing Client Record');
            foreach ($request->file('client_documents') as $file) {
                if ($file && $file->isValid()) {
                    $originalName = $file->getClientOriginalName();
                    $extension = strtolower($file->getClientOriginalExtension());
                    $size = $file->getSize();
                    $path = $file->store('lead-documents', 'public');

                    Document::create([
                        'documentable_type' => Lead::class,
                        'documentable_id' => $lead->id,
                        'title' => pathinfo($originalName, PATHINFO_FILENAME),
                        'category' => $category,
                        'file_path' => $path,
                        'file_type' => $extension,
                        'file_size' => $size,
                        'uploaded_by_id' => auth()->id(),
                    ]);
                }
            }
        }

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
        abort_unless($request->user()?->can('leads.convert') || $request->user()?->hasRole(['super-admin', 'admin']), 403);

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

    public function reject(Request $request, Lead $lead): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $lead->update([
            'status' => 'Lost',
        ]);

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => auth()->id(),
            'activity_type' => 'Status Change',
            'notes' => 'Visit marked as Rejected / Lost.' . (!empty($validated['reason']) ? ' Reason: ' . $validated['reason'] : ''),
        ]);

        AuditLogger::log('status_change', 'leads', $lead->id, null, [
            'status' => 'Lost',
            'reason' => $validated['reason'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Visit marked as Rejected / Lost.',
            ]);
        }

        return back()->with('success', 'Visit marked as Rejected / Lost.');
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
