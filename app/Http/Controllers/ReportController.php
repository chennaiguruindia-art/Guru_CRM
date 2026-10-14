<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        // Narrowing the report to one branch is an Administration control:
        // every other role is already held to their own branch by the scope on
        // Lead, so only the visit figures below can be filtered this way —
        // quotations, projects, invoices and expenses belong to customers, and
        // customers do not carry a branch.
        $branches = collect();
        $branchId = null;

        if ($request->user()?->canSeeAllBranches()) {
            $branches = Branch::where('status', 'active')->orderBy('name')->get();

            if ($request->filled('branch_id')) {
                $branchId = (int) $request->input('branch_id');
            }
        }

        $byBranch = fn ($query) => $branchId ? $query->where('branch_id', $branchId) : $query;

        $leadStats = [
            'total' => $byBranch(Lead::query())->count(),
            'converted' => $byBranch(Lead::query()->where('status', 'Converted'))->count(),
            'lost' => $byBranch(Lead::query()->where('status', 'Lost'))->count(),
        ];

        $salesStats = [
            'quotations_count' => Quotation::count(),
            'quotations_total' => Quotation::sum('grand_total'),
            'approved_total' => Quotation::where('status', 'Approved')->sum('grand_total'),
        ];

        $projectStats = [
            'total' => Project::count(),
            'in_progress' => Project::where('status', 'In Progress')->count(),
            'completed' => Project::where('status', 'Completed')->count(),
        ];

        $financeStats = [
            'invoiced' => Invoice::sum('total_amount'),
            'collected' => Payment::sum('amount'),
            'pending' => Invoice::sum('balance_amount'),
            'expenses' => Expense::sum('amount'),
        ];

        return view('reports.index', compact(
            'leadStats',
            'salesStats',
            'projectStats',
            'financeStats',
            'branches',
            'branchId',
        ));
    }
}
