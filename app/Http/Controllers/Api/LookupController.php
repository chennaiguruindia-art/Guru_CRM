<?php

namespace App\Http\Controllers\Api;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\InventoryCategory;
use App\Models\Lead;
use App\Models\PlantCategory;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LookupController extends BaseApiController
{
    /**
     * Provide key metadata and dropdown choices for mobile app forms.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Branches
        $branches = [];
        if ($user && $user->canSeeAllBranches()) {
            $branches = Branch::where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'location']);
        } elseif ($user && $user->employee?->branch) {
            $b = $user->employee->branch;
            $branches = [
                ['id' => $b->id, 'name' => $b->name, 'code' => $b->code, 'location' => $b->location],
            ];
        }

        // Active Users (for assigning tasks/leads)
        $users = User::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone']);

        // Customers list (scoped)
        $customers = Customer::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'customer_code', 'name', 'company_name', 'phone']);

        // Active Projects
        $projects = Project::where('status', '!=', 'Completed')
            ->orderBy('name')
            ->get(['id', 'project_code', 'name', 'customer_id', 'status']);

        // Plant Categories
        $plantCategories = PlantCategory::orderBy('name')->get(['id', 'name']);

        // Inventory Categories
        $inventoryCategories = InventoryCategory::orderBy('name')->get(['id', 'name']);

        return $this->successResponse([
            'branches' => $branches,
            'users' => $users,
            'customers' => $customers,
            'projects' => $projects,
            'plant_categories' => $plantCategories,
            'inventory_categories' => $inventoryCategories,
            'leads' => [
                'channels' => Lead::CHANNELS,
                'purposes' => Lead::PURPOSES,
                'service_types' => Lead::SERVICE_TYPES,
                'client_types' => Lead::CLIENT_TYPES,
                'statuses' => ['New', 'Contacted', 'In Progress', 'Qualified', 'Converted', 'Lost'],
                'priorities' => ['Low', 'Medium', 'High', 'Urgent'],
            ],
            'customers_meta' => [
                'types' => ['Corporate', 'Individual', 'Government', 'Retail', 'Other'],
                'statuses' => ['active', 'inactive'],
            ],
            'quotations' => [
                'statuses' => ['Draft', 'Sent', 'Under Review', 'Approved', 'Rejected'],
            ],
            'projects_meta' => [
                'statuses' => ['Planning', 'In Progress', 'On Hold', 'Completed', 'Cancelled'],
            ],
            'tasks' => [
                'statuses' => ['Pending', 'In Progress', 'Completed', 'Cancelled'],
                'priorities' => ['Low', 'Medium', 'High', 'Urgent'],
            ],
            'attendance' => [
                'statuses' => ['Present', 'Absent', 'Half Day', 'Leave'],
            ],
            'complaints' => [
                'statuses' => ['Open', 'Assigned', 'In Progress', 'Resolved', 'Closed'],
                'priorities' => ['Low', 'Medium', 'High', 'Urgent'],
            ],
            'expenses' => [
                'categories' => ['Travel', 'Materials', 'Fuel', 'Food', 'Maintenance', 'Office', 'Other'],
                'payment_modes' => ['Cash', 'UPI', 'Bank Transfer', 'Credit Card', 'Cheque'],
            ],
            'payments' => [
                'modes' => ['Cash', 'UPI', 'NEFT/RTGS', 'Cheque', 'Credit Card'],
                'statuses' => ['Completed', 'Pending', 'Failed'],
            ],
        ], 'Lookup options retrieved');
    }
}
