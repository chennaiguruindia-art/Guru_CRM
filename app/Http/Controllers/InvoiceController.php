<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Quotation;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = Invoice::with(['customer', 'project'])->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }

        $invoices = $query->paginate(15)->withQueryString();

        $totalInvoiced = Invoice::sum('total_amount');
        $totalPaid = Invoice::sum('paid_amount');
        $totalPending = Invoice::sum('balance_amount');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $invoices]);
        }

        return view('invoices.index', compact('invoices', 'totalInvoiced', 'totalPaid', 'totalPending'));
    }

    public function create(): View
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        $projects = Project::all();
        $quotations = Quotation::where('status', 'Approved')->get();
        return view('invoices.create', compact('customers', 'projects', 'quotations'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'quotation_id' => ['nullable', 'exists:quotations,id'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:invoice_date'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'terms' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $subtotal = floatval($validated['subtotal']);
        $tax = floatval($validated['tax_amount'] ?? 0);
        $discount = floatval($validated['discount_amount'] ?? 0);
        $total = ($subtotal - $discount) + $tax;

        $validated['invoice_number'] = Invoice::generateNumber();
        $validated['total_amount'] = $total;
        $validated['paid_amount'] = 0;
        $validated['balance_amount'] = $total;
        $validated['payment_status'] = 'unpaid';

        $inv = Invoice::create($validated);
        AuditLogger::log('create', 'invoices', $inv->id);

        return redirect()->route('invoices.index')->with('success', 'Invoice generated successfully!');
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load(['customer', 'project', 'items', 'payments']);
        return view('invoices.show', compact('invoice'));
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        AuditLogger::log('delete', 'invoices', $invoice->id);
        $invoice->delete();
        return redirect()->route('invoices.index')->with('success', 'Invoice deleted!');
    }
}
