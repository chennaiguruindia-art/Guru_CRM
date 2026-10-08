<?php

namespace App\Http\Controllers;

use App\Http\Requests\EstimationRequest;
use App\Models\Customer;
use App\Models\Estimation;
use App\Models\EstimationItem;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class EstimationController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = Estimation::with(['customer', 'createdBy'])->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('estimation_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $estimations = $query->paginate(15)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $estimations]);
        }

        return view('estimations.index', compact('estimations'));
    }

    public function create(Request $request): View
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->get();

        // Previously used components — powers the typeahead on the form
        $components = $this->componentMemory();

        return view('estimations.create', compact('customers', 'components'));
    }

    public function store(EstimationRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Components are stored separately — they are detail rows, not columns
        $items = $validated['items'] ?? [];
        unset($validated['items']);

        $plants = floatval($validated['plants_total'] ?? 0);
        $materials = floatval($validated['materials_total'] ?? 0);
        $labour = floatval($validated['labour_total'] ?? 0);
        $transport = floatval($validated['transport_total'] ?? 0);
        $other = floatval($validated['other_total'] ?? 0);

        $subtotal = $plants + $materials + $labour + $transport + $other;
        $profitMarginPercent = floatval($validated['profit_margin_percent'] ?? 0);
        $profitMarginAmount = ($subtotal * $profitMarginPercent) / 100;
        $subWithProfit = $subtotal + $profitMarginAmount;

        $discount = floatval($validated['discount_amount'] ?? 0);
        $taxPercent = floatval($validated['tax_percent'] ?? 18);
        $afterDiscount = max(0, $subWithProfit - $discount);
        $taxAmount = ($afterDiscount * $taxPercent) / 100;
        $grandTotal = $afterDiscount + $taxAmount;

        $data = array_merge($validated, [
            'estimation_number' => Estimation::generateNumber(),
            'subtotal' => $subtotal,
            'profit_margin_amount' => $profitMarginAmount,
            'tax_amount' => $taxAmount,
            'grand_total' => $grandTotal,
            'status' => 'Draft',
            'created_by_id' => auth()->id(),
        ]);

        $est = DB::transaction(function () use ($data, $items) {
            $estimation = Estimation::create($data);

            foreach ($items as $item) {
                $estimation->items()->create([
                    'item_type' => $item['item_type'],
                    'item_name' => $item['item_name'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'rate' => $item['rate'],
                    'amount' => round($item['quantity'] * $item['rate'], 2),
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            return $estimation;
        });

        AuditLogger::log('create', 'estimations', $est->id);

        $message = $items
            ? 'Estimation created with ' . count($items) . ' component' . (count($items) === 1 ? '' : 's') . '!'
            : 'Estimation created successfully!';

        return redirect()->route('estimations.index')->with('success', $message);
    }

    /**
     * Distinct components used on past estimations / quotations, most recent
     * rate first. This is the typeahead "memory" on the create form — it lets
     * staff reuse a name + price without being locked into it, since names and
     * rates genuinely differ from job to job.
     *
     * @return Collection<int, array{name:string,type:string,rate:float,unit:string,last_used:?string}>
     */
    private function componentMemory(): Collection
    {
        $sources = [];

        if (Schema::hasTable('estimation_items')) {
            $sources[] = DB::table('estimation_items')
                ->select('item_name', 'item_type', 'rate', 'unit', 'created_at')
                ->orderByDesc('id')
                ->limit(500)
                ->get();
        }

        if (Schema::hasTable('quotation_items')) {
            $sources[] = DB::table('quotation_items')
                ->select('item_name', 'item_type', 'unit_price as rate', 'unit', 'created_at')
                ->orderByDesc('id')
                ->limit(500)
                ->get();
        }

        $memory = [];

        foreach ($sources as $rows) {
            foreach ($rows as $row) {
                $name = trim((string) $row->item_name);
                $key = mb_strtolower($name);

                if ($key === '' || isset($memory[$key])) {
                    continue; // first sighting wins = most recent rate
                }

                $memory[$key] = [
                    'name' => $name,
                    'type' => in_array($row->item_type, ['Plant', 'Material', 'Labour', 'Transport', 'Other'], true)
                        ? $row->item_type
                        : 'Other',
                    'rate' => (float) $row->rate,
                    'unit' => $row->unit ?: 'Nos',
                    'last_used' => $row->created_at,
                ];
            }
        }

        uasort($memory, fn (array $a, array $b) => strcasecmp($a['name'], $b['name']));

        return collect(array_values($memory));
    }

    public function show(Estimation $estimation): View
    {
        $estimation->load(['customer', 'items', 'createdBy']);
        return view('estimations.show', compact('estimation'));
    }

    public function destroy(Estimation $estimation): RedirectResponse
    {
        AuditLogger::log('delete', 'estimations', $estimation->id);
        $estimation->delete();
        return redirect()->route('estimations.index')->with('success', 'Estimation deleted successfully!');
    }
}
