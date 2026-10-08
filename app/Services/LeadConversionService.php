<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\MaintenanceContract;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class LeadConversionService
{
    /**
     * Convert a Lead into a Client, plus whatever Type of Service asks for:
     * an AMC Contract, a Project, or nothing at all.
     */
    public function convert(Lead $lead, array $data = []): array
    {
        return DB::transaction(function () use ($lead, $data) {
            // 1. Find existing customer or create new
            $customer = null;
            if (!empty($data['existing_customer_id'])) {
                $customer = Customer::findOrFail($data['existing_customer_id']);
            } else {
                $customer = Customer::create([
                    'customer_code' => Customer::generateCode(),
                    'customer_type' => $data['customer_type'] ?? 'Individual',
                    'name' => $lead->name,
                    'company_name' => $lead->company_name,
                    'contact_person' => $lead->contact_person,
                    'email' => $lead->email,
                    'phone' => $lead->phone,
                    'whatsapp' => $lead->whatsapp,
                    'address' => $lead->address,
                    'city' => $lead->city,
                    'state' => $lead->state,
                    'pincode' => $lead->pincode,
                    'assigned_to_id' => $lead->assigned_to_id,
                    'status' => 'active',
                    'notes' => 'Converted from Visit ' . $lead->lead_code,
                ]);

                // Create primary contact
                CustomerContact::create([
                    'customer_id' => $customer->id,
                    'name' => $lead->contact_person ?: $lead->name,
                    'phone' => $lead->phone,
                    'email' => $lead->email,
                    'is_primary' => true,
                ]);
            }

            // 2. What conversion creates.
            //
            //    Type of Service decides: AMC → an AMC Contract,
            //    Single Project → a Project. Only when the Visit has no Type
            //    of Service does the modal's tickbox get a say.
            $serviceType = $lead->service_type;
            $contract = null;
            $project = null;

            if ($serviceType === 'AMC') {
                $contract = MaintenanceContract::create([
                    'amc_number'        => MaintenanceContract::generateCode(),
                    'customer_id'       => $customer->id,
                    'start_date'        => now()->toDateString(),
                    'end_date'          => now()->addYear()->toDateString(),
                    'contract_value'    => $lead->expected_value ?? 0,
                    'billing_frequency' => 'Monthly',
                    'service_frequency' => 'Weekly',
                    'scope_of_work'     => 'AMC originated from Visit: ' . $lead->lead_code
                        . ($lead->interested_service ? ' (' . $lead->interested_service . ')' : ''),
                    'status'            => 'Active',
                    'notes'             => 'Converted from Visit ' . $lead->lead_code,
                ]);
            }

            $makeProject = $serviceType === 'Single Project'
                || ($serviceType === null && !empty($data['create_project']));

            if ($makeProject) {
                $project = Project::create([
                    'project_code' => Project::generateCode(),
                    'name' => $data['project_name'] ?? ($lead->name . ' Landscaping Project'),
                    'customer_id' => $customer->id,
                    'project_manager_id' => $lead->assigned_to_id,
                    'contract_value' => $lead->expected_value ?? 0,
                    'status' => 'Planning',
                    'progress_percent' => 0,
                    'description' => 'Project originated from Visit: ' . $lead->lead_code . ' (' . $lead->interested_service . ')',
                ]);
            }

            // 3. Update Lead status
            $lead->update([
                'status' => 'Converted',
                'converted_customer_id' => $customer->id,
            ]);

            // 4. Log Activity
            LeadActivity::create([
                'lead_id' => $lead->id,
                'user_id' => auth()->id(),
                'activity_type' => 'Converted',
                'notes' => 'Visit converted to Client [' . $customer->customer_code . ']',
            ]);

            AuditLogger::log('convert', 'leads', $lead->id, null, [
                'customer_id' => $customer->id,
                'project_id' => $project?->id,
                'maintenance_contract_id' => $contract?->id,
            ]);

            return [
                'customer' => $customer,
                'project' => $project,
                'contract' => $contract,
            ];
        });
    }
}
