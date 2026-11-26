<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\MaintenanceContract;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Quotation;
use App\Services\TodayService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly TodayService $today)
    {
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $isDigitalOrOffice = $user?->employee && (
            stripos($user->employee->designation, 'digital') !== false ||
            stripos($user->employee->designation, 'web') !== false ||
            stripos($user->employee->designation, 'seo') !== false
        );

        if ($user && ($user->hasRole('employee') || $isDigitalOrOffice) && ! $user->hasAnyRole(['super-admin', 'admin'])) {
            return redirect()->route('portal.dashboard');
        }

        $branches = $user && $user->canSeeAllBranches()
            ? Branch::where('status', 'active')->orderBy('name')->get()
            : collect();

        $selectedBranchId = null;
        if ($user && $user->canSeeAllBranches()) {
            if ($request->filled('branch_id')) {
                $selectedBranchId = (int) $request->input('branch_id');
            }
        } elseif ($user) {
            $selectedBranchId = $user->branchId();
        }

        $period = $request->input('period', 'all');
        $selectedDate = $request->input('date', now()->toDateString());
        $selectedMonth = $request->input('month', now()->format('Y-m'));

        [$startDate, $endDate] = $this->resolveDateRange($period, $selectedDate, $selectedMonth);

        $metrics = $this->buildMetrics($selectedBranchId, $startDate, $endDate);
        $todo = $this->today->items(12);

        return view('dashboard.index', compact(
            'metrics',
            'todo',
            'branches',
            'selectedBranchId',
            'period',
            'selectedDate',
            'selectedMonth'
        ));
    }

    public function stats(Request $request): JsonResponse
    {
        $user = auth()->user();
        $selectedBranchId = null;
        if ($user && $user->canSeeAllBranches()) {
            if ($request->filled('branch_id')) {
                $selectedBranchId = (int) $request->input('branch_id');
            }
        } elseif ($user) {
            $selectedBranchId = $user->branchId();
        }

        $period = $request->input('period', 'all');
        $selectedDate = $request->input('date', now()->toDateString());
        $selectedMonth = $request->input('month', now()->format('Y-m'));

        [$startDate, $endDate] = $this->resolveDateRange($period, $selectedDate, $selectedMonth);

        return response()->json([
            'success' => true,
            'message' => 'Dashboard statistics loaded successfully',
            'data' => [
                'metrics' => $this->buildMetrics($selectedBranchId, $startDate, $endDate),
                'charts' => $this->buildCharts($selectedBranchId, $startDate, $endDate),
                'todo' => $this->today->items(12),
            ],
        ]);
    }

    // ─── Query Scopes & Helpers ──────────────────────────────────────────

    private function resolveDateRange(string $period, ?string $date, ?string $month): array
    {
        $startDate = null;
        $endDate = null;

        switch ($period) {
            case 'today':
                $startDate = now()->startOfDay();
                $endDate = now()->endOfDay();
                break;
            case 'date':
                if ($date) {
                    $startDate = Carbon::parse($date)->startOfDay();
                    $endDate = Carbon::parse($date)->endOfDay();
                }
                break;
            case 'week':
                $startDate = now()->startOfWeek();
                $endDate = now()->endOfWeek();
                break;
            case 'month':
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
                break;
            case 'custom_month':
                if ($month) {
                    $startDate = Carbon::parse($month . '-01')->startOfMonth();
                    $endDate = Carbon::parse($month . '-01')->endOfMonth();
                }
                break;
            case 'all':
            default:
                break;
        }

        return [$startDate, $endDate];
    }

    private function buildMetrics(?int $branchId = null, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $leadQ = Lead::query();
        $custQ = Customer::query();
        $projQ = Project::query();
        $quotQ = Quotation::query();
        $amcQ = MaintenanceContract::query();
        $invQ = Invoice::query();
        $payQ = Payment::query();

        if ($branchId) {
            $leadQ->where('branch_id', $branchId);
            $custQ->where('branch_id', $branchId);
            $projQ->whereHas('customer', fn($q) => $q->where('branch_id', $branchId));
            $quotQ->whereHas('customer', fn($q) => $q->where('branch_id', $branchId));
            $amcQ->whereHas('customer', fn($q) => $q->where('branch_id', $branchId));
            $invQ->whereHas('customer', fn($q) => $q->where('branch_id', $branchId));
            $payQ->whereHas('invoice.customer', fn($q) => $q->where('branch_id', $branchId));
        }

        if ($startDate && $endDate) {
            $leadQ->whereBetween('created_at', [$startDate, $endDate]);
            $custQ->whereBetween('created_at', [$startDate, $endDate]);
            $projQ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate])
                  ->orWhereBetween('start_date', [$startDate, $endDate]);
            });
            $quotQ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate])
                  ->orWhereBetween('date', [$startDate, $endDate]);
            });
            $amcQ->whereBetween('created_at', [$startDate, $endDate]);
            $invQ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate])
                  ->orWhereBetween('issue_date', [$startDate, $endDate]);
            });
            $payQ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate])
                  ->orWhereBetween('payment_date', [$startDate, $endDate]);
            });
        }

        return [
            'total_leads'         => $this->safe(fn() => (clone $leadQ)->count()),
            'new_leads'           => $this->safe(fn() => (clone $leadQ)->where('status', 'New')->count()),
            'converted_leads'     => $this->safe(fn() => (clone $leadQ)->where('status', 'Converted')->count()),
            'total_customers'     => $this->safe(fn() => (clone $custQ)->count()),
            'active_projects'     => $this->safe(fn() => (clone $projQ)->where('status', 'In Progress')->count()),
            'completed_projects'  => $this->safe(fn() => (clone $projQ)->where('status', 'Completed')->count()),
            'pending_quotations'  => $this->safe(fn() => (clone $quotQ)->whereIn('status', ['Sent','Under Review'])->count()),
            'approved_quotations' => $this->safe(fn() => (clone $quotQ)->where('status', 'Approved')->count()),
            'active_amc'          => $this->safe(fn() => (clone $amcQ)->where('status', 'Active')->count()),
            'pending_invoices'    => $this->safe(fn() => (clone $invQ)->where('payment_status', 'unpaid')->count()),
            'paid_amount'         => $this->safe(fn() => (clone $payQ)->sum('amount'), 0.0),
            'pending_amount'      => $this->safe(fn() => (clone $invQ)->whereIn('payment_status', ['unpaid','partial'])->sum('balance_amount'), 0.0),
            'overdue_amount'      => $this->safe(fn() => (clone $invQ)->where('payment_status', 'overdue')->sum('balance_amount'), 0.0),
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

    private function buildCharts(?int $branchId = null, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $leadQ = Lead::query();
        $quotQ = Quotation::query();
        $projQ = Project::query();
        $amcQ = MaintenanceContract::query();
        $invQ = Invoice::query();
        $payQ = Payment::query();

        if ($branchId) {
            $leadQ->where('branch_id', $branchId);
            $quotQ->whereHas('customer', fn($q) => $q->where('branch_id', $branchId));
            $projQ->whereHas('customer', fn($q) => $q->where('branch_id', $branchId));
            $amcQ->whereHas('customer', fn($q) => $q->where('branch_id', $branchId));
            $invQ->whereHas('customer', fn($q) => $q->where('branch_id', $branchId));
            $payQ->whereHas('invoice.customer', fn($q) => $q->where('branch_id', $branchId));
        }

        if ($startDate && $endDate) {
            $leadQ->whereBetween('created_at', [$startDate, $endDate]);
            $quotQ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate])
                  ->orWhereBetween('date', [$startDate, $endDate]);
            });
            $projQ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate])
                  ->orWhereBetween('start_date', [$startDate, $endDate]);
            });
            $amcQ->whereBetween('created_at', [$startDate, $endDate]);
            $invQ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate])
                  ->orWhereBetween('issue_date', [$startDate, $endDate]);
            });
            $payQ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate])
                  ->orWhereBetween('payment_date', [$startDate, $endDate]);
            });
        }

        // --- 1. Leads by source ---
        $leadsBySource = (clone $leadQ)->select('source', DB::raw('count(*) as total'))
            ->groupBy('source')
            ->pluck('total', 'source');

        // --- 2. Leads by status ---
        $leadsByStatus = (clone $leadQ)->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        // --- 3. Sales pipeline (counts per stage) ---
        $allLeads       = (clone $leadQ)->count();
        $quotationsAll  = (clone $quotQ)->count();
        $quotationsSent = (clone $quotQ)->whereIn('status', ['Sent','Under Review','Approved'])->count();
        $approved       = (clone $quotQ)->where('status', 'Approved')->count();
        $wonProjects    = (clone $projQ)->whereIn('status', ['In Progress','Completed'])->count();

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

            $qVal = Quotation::query();
            $pVal = Payment::query();
            if ($branchId) {
                $qVal->whereHas('customer', fn($q) => $q->where('branch_id', $branchId));
                $pVal->whereHas('invoice.customer', fn($q) => $q->where('branch_id', $branchId));
            }

            $monthlyQuoteValues[] = (float) $qVal->whereBetween('date', [$start, $end])->sum('grand_total');
            $monthlyRevenue[]     = (float) $pVal->whereBetween('payment_date', [$start, $end])->sum('amount');
            $monthlyExpenses[]    = 0;
        }

        // --- 6. Project status ---
        $projectStatus = (clone $projQ)->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        // --- 7. AMC status ---
        $amcStatus = (clone $amcQ)->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        // --- 8. Payments aging ---
        $now = now();
        $agingBase = clone $invQ;
        $aging = [
            '0-15 Days'          => (clone $agingBase)->whereIn('payment_status', ['unpaid','partial'])->where('due_date', '>=', $now->copy()->subDays(15))->sum('balance_amount'),
            '16-30 Days'         => (clone $agingBase)->whereIn('payment_status', ['unpaid','partial'])->whereBetween('due_date', [$now->copy()->subDays(30), $now->copy()->subDays(16)])->sum('balance_amount'),
            '31-60 Days'         => (clone $agingBase)->whereIn('payment_status', ['unpaid','partial'])->whereBetween('due_date', [$now->copy()->subDays(60), $now->copy()->subDays(31)])->sum('balance_amount'),
            '60+ Days (Overdue)' => (clone $agingBase)->where('payment_status', 'overdue')->sum('balance_amount'),
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
                'data'   => [$allLeads, $quotationsAll, $quotationsSent, $approved, $wonProjects],
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
