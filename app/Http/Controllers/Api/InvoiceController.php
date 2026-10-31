<?php

namespace App\Http\Controllers\Api;

use App\Models\Invoice;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Invoice::with(['customer', 'project'])->latest('invoice_date');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $paginator = $query->paginate($perPage);

        return $this->paginatedResponse($paginator, 'Invoices retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'quotation_id' => ['nullable', 'exists:quotations,id'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'terms' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['invoice_number'] = Invoice::generateNumber();
        $validated['paid_amount'] = 0;
        $validated['balance_amount'] = $validated['total_amount'];
        $validated['payment_status'] = 'unpaid';

        $invoice = Invoice::create($validated);

        AuditLogger::log('create', 'invoices', $invoice->id, null, $invoice->toArray());

        return $this->successResponse(
            $invoice->fresh(['customer', 'project']),
            'Invoice created successfully',
            201
        );
    }

    public function show(Invoice $invoice): JsonResponse
    {
        $invoice->load(['customer', 'project', 'items', 'payments']);

        return $this->successResponse($invoice, 'Invoice retrieved successfully');
    }

    public function update(Request $request, Invoice $invoice): JsonResponse
    {
        $validated = $request->validate([
            'invoice_date' => ['sometimes', 'required', 'date'],
            'due_date' => ['nullable', 'date'],
            'payment_status' => ['sometimes', 'required', 'string', 'in:unpaid,partial,paid,overdue'],
            'terms' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $old = $invoice->toArray();
        $invoice->update($validated);

        AuditLogger::log('update', 'invoices', $invoice->id, $old, $invoice->fresh()->toArray());

        return $this->successResponse($invoice->fresh(['customer', 'project']), 'Invoice updated');
    }

    public function destroy(Invoice $invoice): JsonResponse
    {
        $id = $invoice->id;
        $invoice->delete();

        AuditLogger::log('delete', 'invoices', $id);

        return $this->successResponse(null, 'Invoice deleted');
    }
}
