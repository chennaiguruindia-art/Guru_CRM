<?php

namespace App\Http\Controllers;

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
        $leadStats = [
            'total' => Lead::count(),
            'converted' => Lead::where('status', 'Converted')->count(),
            'lost' => Lead::where('status', 'Lost')->count(),
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

        return view('reports.index', compact('leadStats', 'salesStats', 'projectStats', 'financeStats'));
    }
}
