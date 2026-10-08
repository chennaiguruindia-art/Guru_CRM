<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\MaintenanceContract;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Quotation;
use App\Services\TodayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly TodayService $today)
    {
    }

    public function index(): View
    {
        $metrics = $this->buildMetrics();

        $recentActivities = AuditLog::with('user')
            ->latest()
            ->take(8)
            ->get();

        $todo = $this->today->items(12);

        return view('dashboard.index', compact('metrics', 'recentActivities', 'todo'));
    }

    public function stats(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Dashboard statistics loaded successfully',
            'data' => [
                'metrics' => $this->buildMetrics(),
                'charts' => $this->buildCharts(),
                'todo' => $this->today->items(12),
            ],
        ]);
    }

    // ─── Real data helpers ────────────────────────────────────────────

    private function buildMetrics(): array
    {
        return [
            'total_leads'         => $this->safe(fn() => Lead::count()),
            'new_leads'           => $this->safe(fn() => Lead::where('status', 'New')->count()),
            'converted_leads'     => $this->safe(fn() => Lead::where('status', 'Converted')->count()),
            'total_customers'     => $this->safe(fn() => Customer::count()),
            'active_projects'     => $this->safe(fn() => Project::where('status', 'In Progress')->count()),
            'completed_projects'  => $this->safe(fn() => Project::where('status', 'Completed')->count()),
            'pending_quotations'  => $this->safe(fn() => Quotation::whereIn('status', ['Sent','Under Review'])->count()),
            'approved_quotations' => $this->safe(fn() => Quotation::where('status', 'Approved')->count()),
            'active_amc'          => $this->safe(fn() => MaintenanceContract::where('status', 'Active')->count()),
            'pending_invoices'    => $this->safe(fn() => Invoice::where('payment_status', 'unpaid')->count()),
            'paid_amount'         => $this->safe(fn() => Payment::sum('amount'), 0.0),
            'pending_amount'      => $this->safe(fn() => Invoice::whereIn('payment_status', ['unpaid','partial'])->sum('balance_amount'), 0.0),
            'overdue_amount'      => $this->safe(fn() => Invoice::where('payment_status', 'overdue')->sum('balance_amount'), 0.0),
        ];
    }

    private function safe(callable $fn, mixed $fallback = 0): mixed
    {
        try {
            return $fn();
        } catch (\Throwable) {
            return $fallback;
        }
    }

    private function buildCharts(): array
    {
        // --- 1. Leads by source (real) ---
        $leadsBySource = Lead::select('source', DB::raw('count(*) as total'))
            ->groupBy('source')
            ->pluck('total', 'source');

        // --- 2. Leads by status (real) ---
        $leadsByStatus = Lead::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        // --- 3. Sales pipeline (real counts per stage) ---
        $allLeads      = Lead::count();
        $quotationsAll = Quotation::count();
        $quotationsSent= Quotation::whereIn('status',['Sent','Under Review','Approved'])->count();
        $approved      = Quotation::where('status','Approved')->count();
        $wonProjects   = Project::whereIn('status',['In Progress','Completed'])->count();

        // --- 4 & 5. Monthly quotation / revenue (last 6 months) ---
        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $months->push(now()->subMonths($i)->format('M Y'));
        }

        $monthlyQuoteValues = [];
        $monthlyRevenue     = [];
        $monthlyExpenses    = [];
        for ($i = 5; $i >= 0; $i--) {
            $start = now()->subMonths($i)->startOfMonth();
            $end   = now()->subMonths($i)->endOfMonth();

            $monthlyQuoteValues[] = (float) Quotation::whereBetween('date', [$start,$end])
                ->sum('grand_total');

            $monthlyRevenue[]  = (float) Payment::whereBetween('payment_date', [$start,$end])->sum('amount');
            $monthlyExpenses[] = 0; // Expenses table - will populate as data comes in
        }

        // --- 6. Project status (real) ---
        $projectStatus = Project::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total','status');

        // --- 7. AMC status (real) ---
        $amcStatus = MaintenanceContract::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total','status');

        // --- 8. Payments aging (real) ---
        $now = now();
        $aging = [
            '0-15 Days'       => Invoice::whereIn('payment_status',['unpaid','partial'])->where('due_date','>=', $now->copy()->subDays(15))->sum('balance_amount'),
            '16-30 Days'      => Invoice::whereIn('payment_status',['unpaid','partial'])->whereBetween('due_date', [$now->copy()->subDays(30), $now->copy()->subDays(16)])->sum('balance_amount'),
            '31-60 Days'      => Invoice::whereIn('payment_status',['unpaid','partial'])->whereBetween('due_date', [$now->copy()->subDays(60), $now->copy()->subDays(31)])->sum('balance_amount'),
            '60+ Days (Overdue)' => Invoice::where('payment_status','overdue')->sum('balance_amount'),
        ];

        return [
            'leads_by_source' => [
                'labels' => $leadsBySource->keys()->values()->toArray() ?: ['No Data'],
                'data'   => $leadsBySource->values()->toArray() ?: [0],
            ],
            'leads_by_status' => [
                'labels' => $leadsByStatus->keys()->values()->toArray() ?: ['No Data'],
                'data'   => $leadsByStatus->values()->toArray() ?: [0],
            ],
            'sales_pipeline' => [
                'labels' => ['Visits','Quotations','Sent/Review','Approved','Won Projects'],
                'data'   => [$allLeads,$quotationsAll,$quotationsSent,$approved,$wonProjects],
            ],
            'monthly_quotations' => [
                'labels' => $months->toArray(),
                'data'   => $monthlyQuoteValues,
            ],
            'monthly_revenue' => [
                'labels'   => $months->toArray(),
                'revenue'  => $monthlyRevenue,
                'expenses' => $monthlyExpenses,
            ],
            'project_status' => [
                'labels' => $projectStatus->keys()->values()->toArray() ?: ['No Projects'],
                'data'   => $projectStatus->values()->toArray() ?: [0],
            ],
            'amc_status' => [
                'labels' => $amcStatus->keys()->values()->toArray() ?: ['No Contracts'],
                'data'   => $amcStatus->values()->toArray() ?: [0],
            ],
            'pending_payments' => [
                'labels' => array_keys($aging),
                'data'   => array_values($aging),
            ],
        ];
    }
}
