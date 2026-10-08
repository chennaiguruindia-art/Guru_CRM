<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\InventoryItem;
use App\Models\Lead;
use App\Models\MaintenanceContract;
use App\Models\Plant;
use App\Models\Project;
use App\Models\Quotation;
use App\Models\Vendor;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Global search across every module the current user is allowed to view.
     * Each source is only queried when the user holds "<module>.view".
     */
    public function __invoke(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json(['success' => true, 'query' => $q, 'results' => [], 'total' => 0]);
        }

        $like = '%' . $q . '%';
        $user = $request->user();

        $results = [];

        foreach ($this->sources() as $source) {
            if (!$user->hasPermission($source['permission'])) {
                continue;
            }

            $rows = $source['query']($like)->limit(4)->get();

            foreach ($rows as $row) {
                $results[] = [
                    'id' => $row->id,
                    'type' => $source['label'],
                    'icon' => $source['icon'],
                    'title' => $source['title']($row),
                    'subtitle' => $source['subtitle']($row),
                    'url' => route($source['route'], $row),
                ];
            }
        }

        usort($results, fn ($a, $b) => strcmp($a['title'], $b['title']));

        return response()->json([
            'success' => true,
            'query' => $q,
            'results' => $results,
            'total' => count($results),
        ]);
    }

    /**
     * @return array<int, array{label:string,permission:string,icon:string,route:string,
     *     query:callable, title:callable, subtitle:callable}>
     */
    private function sources(): array
    {
        return [
            [
                'label' => 'Visits', 'permission' => 'leads.view', 'icon' => 'bi-funnel', 'route' => 'leads.show',
                'query' => fn (string $like) => $this->search(Lead::class, $like, ['name', 'lead_code', 'company_name', 'phone', 'email']),
                'title' => fn ($r) => $r->name,
                'subtitle' => fn ($r) => $this->join([$r->lead_code, $r->phone, $r->status]),
            ],
            [
                'label' => 'Clients', 'permission' => 'customers.view', 'icon' => 'bi-people', 'route' => 'customers.show',
                'query' => fn (string $like) => $this->search(Customer::class, $like, ['name', 'customer_code', 'company_name', 'phone', 'email']),
                'title' => fn ($r) => $r->name,
                'subtitle' => fn ($r) => $this->join([$r->customer_code, $r->phone, $r->status]),
            ],
            [
                'label' => 'Projects', 'permission' => 'projects.view', 'icon' => 'bi-kanban', 'route' => 'projects.show',
                'query' => fn (string $like) => $this->search(Project::class, $like, ['name', 'project_code'])
                    ->orWhereHas('customer', fn (Builder $q) => $q->where('name', 'like', $like)),
                'title' => fn ($r) => $r->name,
                'subtitle' => fn ($r) => $this->join([$r->project_code, $r->status]),
            ],
            [
                'label' => 'Quotations', 'permission' => 'quotations.view', 'icon' => 'bi-file-earmark-text', 'route' => 'quotations.show',
                'query' => fn (string $like) => $this->search(Quotation::class, $like, ['quotation_number', 'status'])
                    ->orWhereHas('customer', fn (Builder $q) => $q->where('name', 'like', $like)),
                'title' => fn ($r) => $r->quotation_number,
                'subtitle' => fn ($r) => $this->join([$r->customer?->name, $r->status, '₹' . number_format((float) $r->grand_total, 2)]),
            ],
            [
                'label' => 'Invoices', 'permission' => 'invoices.view', 'icon' => 'bi-receipt', 'route' => 'invoices.show',
                'query' => fn (string $like) => $this->search(Invoice::class, $like, ['invoice_number', 'payment_status'])
                    ->orWhereHas('customer', fn (Builder $q) => $q->where('name', 'like', $like)),
                'title' => fn ($r) => $r->invoice_number,
                'subtitle' => fn ($r) => $this->join([$r->customer?->name, $r->payment_status, '₹' . number_format((float) $r->balance_amount, 2) . ' due']),
            ],
            [
                'label' => 'Work Orders', 'permission' => 'work_orders.view', 'icon' => 'bi-card-checklist', 'route' => 'work-orders.show',
                'query' => fn (string $like) => $this->search(WorkOrder::class, $like, ['wo_number', 'status', 'scope_of_work'])
                    ->orWhereHas('customer', fn (Builder $q) => $q->where('name', 'like', $like)),
                'title' => fn ($r) => $r->wo_number,
                'subtitle' => fn ($r) => $this->join([$r->customer?->name, $r->status]),
            ],
            [
                'label' => 'AMC Contracts', 'permission' => 'amc.view', 'icon' => 'bi-shield-check', 'route' => 'amc.show',
                'query' => fn (string $like) => $this->search(MaintenanceContract::class, $like, ['amc_number', 'status'])
                    ->orWhereHas('customer', fn (Builder $q) => $q->where('name', 'like', $like)),
                'title' => fn ($r) => $r->amc_number,
                'subtitle' => fn ($r) => $this->join([$r->customer?->name, $r->status]),
            ],
            [
                'label' => 'Plants', 'permission' => 'plants.view', 'icon' => 'bi-flower2', 'route' => 'plants.show',
                'query' => fn (string $like) => $this->search(Plant::class, $like, ['name', 'plant_code', 'botanical_name', 'common_name']),
                'title' => fn ($r) => $r->name,
                'subtitle' => fn ($r) => $this->join([$r->plant_code, $r->botanical_name, $r->plant_type]),
            ],
            [
                'label' => 'Inventory', 'permission' => 'inventory.view', 'icon' => 'bi-boxes', 'route' => 'inventory.show',
                'query' => fn (string $like) => $this->search(InventoryItem::class, $like, ['name', 'sku', 'unit'])
                    ->orWhereHas('category', fn (Builder $q) => $q->where('name', 'like', $like)),
                'title' => fn ($r) => $r->name,
                'subtitle' => fn ($r) => $this->join([$r->sku, $r->current_stock . ' in stock', $r->warehouse?->location]),
            ],
            [
                'label' => 'Vendors', 'permission' => 'vendors.view', 'icon' => 'bi-shop', 'route' => 'vendors.show',
                'query' => fn (string $like) => $this->search(Vendor::class, $like, ['name', 'vendor_code', 'phone', 'city']),
                'title' => fn ($r) => $r->name,
                'subtitle' => fn ($r) => $this->join([$r->vendor_code, $r->phone]),
            ],
            [
                'label' => 'Employees', 'permission' => 'employees.view', 'icon' => 'bi-person-badge', 'route' => 'employees.show',
                'query' => fn (string $like) => $this->search(Employee::class, $like, ['name', 'employee_code', 'phone', 'designation']),
                'title' => fn ($r) => $r->name,
                'subtitle' => fn ($r) => $this->join([$r->employee_code, $r->designation]),
            ],
            [
                'label' => 'Complaints', 'permission' => 'complaints.view', 'icon' => 'bi-chat-square-dots', 'route' => 'complaints.show',
                'query' => fn (string $like) => $this->search(Complaint::class, $like, ['ticket_number', 'subject', 'status'])
                    ->orWhereHas('customer', fn (Builder $q) => $q->where('name', 'like', $like)),
                'title' => fn ($r) => $r->subject,
                'subtitle' => fn ($r) => $this->join([$r->ticket_number, $r->status]),
            ],
        ];
    }

    private function search(string $model, string $like, array $columns): Builder
    {
        return $model::query()->where(function (Builder $q) use ($columns, $like) {
            foreach ($columns as $column) {
                $q->orWhere($column, 'like', $like);
            }
        });
    }

    private function join(array $parts): string
    {
        return implode('  ·  ', array_filter(array_map(fn ($p) => trim((string) $p), $parts), fn ($p) => $p !== ''));
    }
}
