@extends('layouts.app')
@section('title', 'Record Payment')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-cash-stack text-success me-2"></i>Record Payment</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-0"><li class="breadcrumb-item"><a href="{{ route('payments.index') }}" class="text-success text-decoration-none">Payments</a></li><li class="breadcrumb-item active">Record</li></ol></nav>
    </div>
    <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<form method="POST" action="{{ route('payments.store') }}">
    @csrf
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle text-success me-2"></i>Payment Details</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Client <span class="text-danger">*</span></label>
                            <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                                <option value="">— Select Client —</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}" @selected(old('customer_id', request('customer_id')) == $c->id)>{{ $c->name }} ({{ $c->customer_code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Linked Invoice</label>
                            <select name="invoice_id" class="form-select">
                                <option value="">— Advance / General Payment —</option>
                                @foreach($invoices as $inv)
                                    <option value="{{ $inv->id }}" @selected(old('invoice_id', request('invoice_id')) == $inv->id)>{{ $inv->invoice_number }} (Due: ₹{{ number_format($inv->balance_amount) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control" value="{{ old('payment_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Amount Paid (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select" required>
                                @foreach(['Bank Transfer', 'UPI', 'Cheque', 'Cash', 'Credit Card', 'Debit Card', 'Other'] as $m)
                                    <option value="{{ $m }}">{{ $m }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Reference / Transaction / UTR No</label>
                            <input type="text" name="reference_number" class="form-control" placeholder="e.g. UTR12345678, CHQ#001234" value="{{ old('reference_number') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Notes</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success py-2 px-4 fw-semibold">
                    <i class="bi bi-check2-circle me-1"></i> Record Payment
                </button>
                <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
    </div>
</form>
@endsection
