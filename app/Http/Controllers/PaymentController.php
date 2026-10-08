<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = Payment::with(['customer', 'invoice', 'recordedBy'])->latest('payment_date');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('method')) {
            $query->where('payment_method', $request->input('method'));
        }

        $payments = $query->paginate(15)->withQueryString();
        $totalCollected = Payment::sum('amount');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $payments]);
        }

        return view('payments.index', compact('payments', 'totalCollected'));
    }

    public function create(): View
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        $invoices = Invoice::whereIn('payment_status', ['unpaid', 'partial'])->get();
        return view('payments.create', compact('customers', 'invoices'));
    }

    public function store(Request $request): RedirectResponse
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
        $validated['recorded_by_id'] = auth()->id();

        DB::transaction(function () use ($validated) {
            $payment = Payment::create($validated);

            if (!empty($validated['invoice_id'])) {
                $invoice = Invoice::find($validated['invoice_id']);
                if ($invoice) {
                    $newPaid = $invoice->paid_amount + $validated['amount'];
                    $newBalance = max(0, $invoice->total_amount - $newPaid);
                    $status = ($newBalance <= 0) ? 'paid' : 'partial';

                    $invoice->update([
                        'paid_amount' => $newPaid,
                        'balance_amount' => $newBalance,
                        'payment_status' => $status
                    ]);
                }
            }

            AuditLogger::log('create', 'payments', $payment->id);
        });

        return redirect()->route('payments.index')->with('success', 'Payment recorded successfully!');
    }

    public function show(Payment $payment): View
    {
        $payment->load(['customer', 'invoice', 'recordedBy']);
        return view('payments.show', compact('payment'));
    }
}
