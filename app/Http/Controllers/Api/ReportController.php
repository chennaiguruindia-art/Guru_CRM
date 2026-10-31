<?php

namespace App\Http\Controllers\Api;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Quotation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $branchId = null;
        if ($request->user()?->canSeeAllBranches() && $request->filled('branch_id')) {
            $branchId = (int) $request->input('branch_id');
        }

        $byBranch = fn ($query) => $branchId ? $query->where('branch_id', $branchId) : $query;

        $leadStats = [
            'total' => $byBranch(Lead::query())->count(),
            'new' => $byBranch(Lead::query()->where('status', 'New'))->count(),
            'converted' => $byBranch(Lead::query()->where('status', 'Converted'))->count(),
            'lost' => $byBranch(Lead::query()->where('status', 'Lost'))->count(),
        ];

        $salesStats = [
            'quotations_count' => Quotation::count(),
            'quotations_total' => (float) Quotation::sum('grand_total'),
            'approved_total' => (float) Quotation::where('status', 'Approved')->sum('grand_total'),
        ];

        $projectStats = [
            'total' => Project::count(),
            'in_progress' => Project::where('status', 'In Progress')->count(),
            'completed' => Project::where('status', 'Completed')->count(),
        ];

        $financeStats = [
            'invoiced' => (float) Invoice::sum('total_amount'),
            'collected' => (float) Payment::sum('amount'),
            'pending' => (float) Invoice::sum('balance_amount'),
            'expenses' => (float) Expense::sum('amount'),
        ];

        return $this->successResponse([
            'leads' => $leadStats,
            'sales' => $salesStats,
            'projects' => $projectStats,
            'finance' => $financeStats,
            'branch_id' => $branchId,
        ], 'Report statistics retrieved');
    }
}
