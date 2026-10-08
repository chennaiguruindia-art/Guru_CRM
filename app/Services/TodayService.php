<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\MaintenanceContract;
use App\Models\ProjectTask;

/**
 * Builds the "needs your attention today" list.
 *
 * Used by:
 *   - DashboardController (full strip on the dashboard)
 *   - NotificationController (top few items in the navbar bell)
 *
 * Every query is wrapped so a missing/empty table can never break a page.
 */
class TodayService
{
    public const SEVERITY_ORDER = ['danger' => 0, 'warning' => 1, 'info' => 2];

    /**
     * Every entry carries the keys below.
     *
     * @return array<int, array{type:string,icon:string,severity:string,label:string,title:string,meta:string,url:string}>
     */
    public function items(int $limit = 15): array
    {
        $items = [];

        $items = array_merge($items, $this->leadFollowUps());
        $items = array_merge($items, $this->tasks());
        $items = array_merge($items, $this->overdueInvoices());
        $items = array_merge($items, $this->expiringAmc());
        $items = array_merge($items, $this->openComplaints());
        $items = array_merge($items, $this->lowStock());

        usort($items, function ($a, $b) {
            $bySeverity = self::SEVERITY_ORDER[$a['severity']] <=> self::SEVERITY_ORDER[$b['severity']];
            return $bySeverity ?: strcmp($a['label'], $b['label']);
        });

        return array_slice($items, 0, $limit);
    }

    public function count(): int
    {
        return count($this->items(100));
    }

    /** @return array<int, array> */
    private function leadFollowUps(): array
    {
        return $this->safe(function () {
            $today = now()->toDateString();

            return Lead::with('assignedTo')
                ->whereNotNull('follow_up_date')
                ->where('follow_up_date', '<=', $today)
                ->where('status', '!=', 'Converted')
                ->orderBy('follow_up_date')
                ->limit(5)
                ->get()
                ->map(fn (Lead $l) => $this->make(
                    'lead', 'bi-funnel',
                    $l->follow_up_date < $today ? 'danger' : 'warning',
                    $l->follow_up_date < $today ? 'Visit follow-up overdue' : 'Visit follow-up today',
                    $l->name,
                    $this->join([$l->lead_code, $l->status, $l->assignedTo?->name]),
                    route('leads.show', $l),
                ))->all();
        });
    }

    /** @return array<int, array> */
    private function tasks(): array
    {
        return $this->safe(function () {
            $today = now()->toDateString();

            return ProjectTask::query()
                ->whereIn('status', ['Pending', 'In Progress'])
                ->whereNotNull('due_date')
                ->where('due_date', '<=', $today)
                ->orderBy('due_date')
                ->limit(5)
                ->get()
                ->map(fn (ProjectTask $t) => $this->make(
                    'task', 'bi-check2-square',
                    $t->due_date < $today ? 'danger' : 'warning',
                    $t->due_date < $today ? 'Task overdue' : 'Task due today',
                    $t->title,
                    $this->join([$t->due_date->format('d M Y'), $t->priority, $t->status]),
                    route('tasks.show', $t),
                ))->all();
        });
    }

    /** @return array<int, array> */
    private function overdueInvoices(): array
    {
        return $this->safe(function () {
            return Invoice::with('customer')
                ->where('payment_status', 'overdue')
                ->orWhere(function ($q) {
                    $q->whereIn('payment_status', ['unpaid', 'partial'])
                      ->whereNotNull('due_date')
                      ->where('due_date', '<', now()->toDateString());
                })
                ->orderBy('due_date')
                ->limit(5)
                ->get()
                ->map(fn (Invoice $i) => $this->make(
                    'invoice', 'bi-receipt',
                    'danger',
                    'Invoice overdue',
                    $i->customer?->name ?? $i->invoice_number,
                    $this->join([$i->invoice_number, '₹' . number_format((float) $i->balance_amount, 2) . ' due', $i->due_date?->format('d M Y')]),
                    route('invoices.show', $i),
                ))->all();
        });
    }

    /** @return array<int, array> */
    private function expiringAmc(): array
    {
        return $this->safe(function () {
            return MaintenanceContract::with(['customer'])
                ->whereIn('status', ['Active', 'Expiring Soon'])
                ->whereNotNull('end_date')
                ->whereBetween('end_date', [now()->toDateString(), now()->addDays(30)->toDateString()])
                ->orderBy('end_date')
                ->limit(4)
                ->get()
                ->map(fn ($c) => $this->make(
                    'amc', 'bi-shield-check',
                    $c->end_date->diffInDays(now()) <= 7 ? 'danger' : 'warning',
                    'AMC expiring soon',
                    $c->customer?->name ?? $c->amc_number,
                    $this->join([$c->amc_number, 'ends ' . $c->end_date->format('d M Y')]),
                    route('amc.show', $c),
                ))->all();
        });
    }

    /** @return array<int, array> */
    private function openComplaints(): array
    {
        return $this->safe(function () {
            $rank = ['Urgent' => 0, 'High' => 1, 'Medium' => 2, 'Low' => 3];

            return Complaint::with('customer')
                ->whereIn('status', ['Open', 'Assigned', 'In Progress'])
                ->limit(20)
                ->get()
                ->sortBy(fn (Complaint $c) => $rank[$c->priority] ?? 4)
                ->take(4)
                ->map(fn (Complaint $c) => $this->make(
                    'complaint', 'bi-chat-square-dots',
                    in_array($c->priority, ['Urgent', 'High']) ? 'warning' : 'info',
                    'Open complaint',
                    $c->subject,
                    $this->join([$c->ticket_number, $c->priority, $c->status]),
                    route('complaints.show', $c),
                ))->all();
        });
    }

    /** @return array<int, array> */
    private function lowStock(): array
    {
        return $this->safe(function () {
            return InventoryItem::query()
                ->where('status', 'active')
                ->whereColumn('current_stock', '<=', 'minimum_stock')
                ->orderBy('current_stock')
                ->limit(4)
                ->get()
                ->map(fn (InventoryItem $i) => $this->make(
                    'stock', 'bi-boxes',
                    $i->current_stock <= 0 ? 'danger' : 'warning',
                    $i->current_stock <= 0 ? 'Item out of stock' : 'Low stock alert',
                    $i->name,
                    $this->join([$i->sku, $i->current_stock . ' left (min ' . $i->minimum_stock . ')']),
                    route('inventory.show', $i),
                ))->all();
        });
    }

    private function make(string $type, string $icon, string $severity, string $label, string $title, string $meta, string $url): array
    {
        return compact('type', 'icon', 'severity', 'label', 'title', 'meta', 'url');
    }

    private function join(array $parts): string
    {
        return implode('  ·  ', array_filter(array_map(fn ($p) => trim((string) $p), $parts), fn ($p) => $p !== ''));
    }

    private function safe(callable $fn): array
    {
        try {
            return $fn() ?: [];
        } catch (\Throwable $e) {
            report($e);
            return [];
        }
    }
}
