<?php

namespace App\Http\Controllers\Api;

use App\Models\Invoice;
use App\Models\Payment;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Payment::with(['customer', 'invoice', 'recordedBy'])->latest('payment_date');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }

        if ($request->filled('invoice_id')) {
            $query->where('invoice_id', $request->input('invoice_id'));
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $paginator = $query->paginate($perPage);

        return $this->paginatedResponse($paginator, 'Payments retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'invoice_id' => ['nullable', 'exists:invoices,id'],
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'string'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['payment_number'] = Payment::generateNumber();
        $validated['recorded_by_id'] = $request->user()->id;

        $payment = DB::transaction(function () use ($validated) {
            $p = Payment::create($validated);

            if (! empty($validated['invoice_id'])) {
                $invoice = Invoice::find($validated['invoice_id']);
                if ($invoice) {
                    $newPaid = $invoice->paid_amount + $validated['amount'];
                    $newBalance = max(0, $invoice->total_amount - $newPaid);
                    $status = ($newBalance <= 0) ? 'paid' : 'partial';

                    $invoice->update([
                        'paid_amount' => $newPaid,
                        'balance_amount' => $newBalance,
                        'payment_status' => $status,
                    ]);
                }
            }

            return $p;
        });

        AuditLogger::log('create', 'payments', $payment->id, null, $payment->toArray());

        return $this->successResponse(
            $payment->fresh(['customer', 'invoice', 'recordedBy']),
            'Payment recorded successfully',
            201
        );
    }

    public function show(Payment $payment): JsonResponse
    {
        $payment->load(['customer', 'invoice', 'recordedBy']);

        return $this->successResponse($payment, 'Payment retrieved');
    }

    public function destroy(Payment $payment): JsonResponse
    {
        $id = $payment->id;
        $payment->delete();

        AuditLogger::log('delete', 'payments', $id);

        return $this->successResponse(null, 'Payment deleted');
    }
}
