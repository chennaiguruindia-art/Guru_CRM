<?php

namespace App\Http\Controllers\Api;

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

class DashboardController extends BaseApiController
{
    public function __construct(private readonly TodayService $today)
    {
    }

    /**
     * Mobile dashboard summary metrics & today's to-do items.
     */
    public function index(Request $request): JsonResponse
    {
        return $this->successResponse([
            'metrics' => $this->buildMetrics(),
            'today_items' => $this->today->items(15),
            'today_count' => $this->today->count(),
        ], 'Dashboard loaded successfully');
    }

    /**
     * Dashboard stats including analytics and charts for mobile app.
     */
    public function stats(Request $request): JsonResponse
    {
        return $this->successResponse([
            'metrics' => $this->buildMetrics(),
            'charts' => $this->buildCharts(),
            'today_items' => $this->today->items(15),
            'today_count' => $this->today->count(),
        ], 'Dashboard statistics loaded successfully');
    }

    private function buildMetrics(): array
    {
        return [
            'total_leads'         => $this->safe(fn() => Lead::count()),
            'new_leads'           => $this->safe(fn() => Lead::where('status', 'New')->count()),
            'converted_leads'     => $this->safe(fn() => Lead::where('status', 'Converted')->count()),
            'total_customers'     => $this->safe(fn() => Customer::count()),
            'active_projects'     => $this->safe(fn() => Project::where('status', 'In Progress')->count()),
            'completed_projects'  => $this->safe(fn() => Project::where('status', 'Completed')->count()),
            'pending_quotations'  => $this->safe(fn() => Quotation::whereIn('status', ['Sent', 'Under Review'])->count()),
            'approved_quotations' => $this->safe(fn() => Quotation::where('status', 'Approved')->count()),
            'active_amc'          => $this->safe(fn() => MaintenanceContract::where('status', 'Active')->count()),
            'pending_invoices'    => $this->safe(fn() => Invoice::where('payment_status', 'unpaid')->count()),
            'paid_amount'         => (float) $this->safe(fn() => Payment::sum('amount'), 0.0),
            'pending_amount'      => (float) $this->safe(fn() => Invoice::whereIn('payment_status', ['unpaid', 'partial'])->sum('balance_amount'), 0.0),
            'overdue_amount'      => (float) $this->safe(fn() => Invoice::where('payment_status', 'overdue')->sum('balance_amount'), 0.0),
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
        $leadsBySource = $this->safe(fn() => Lead::select('source', DB::raw('count(*) as total'))
            ->groupBy('source')
            ->pluck('total', 'source')->all(), []);

        $leadsByStatus = $this->safe(fn() => Lead::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')->all(), []);

        $projectStatus = $this->safe(fn() => Project::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')->all(), []);

        return [
            'leads_by_source' => $leadsBySource,
            'leads_by_status' => $leadsByStatus,
            'project_status' => $projectStatus,
        ];
    }
}
