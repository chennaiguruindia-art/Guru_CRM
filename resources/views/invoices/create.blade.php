@extends('layouts.app')
@section('title', 'Create Invoice')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-receipt text-success me-2"></i>Create Invoice</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-0"><li class="breadcrumb-item"><a href="{{ route('invoices.index') }}" class="text-success text-decoration-none">Invoices</a></li><li class="breadcrumb-item active">Create</li></ol></nav>
    </div>
    <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<form method="POST" action="{{ route('invoices.store') }}">
    @csrf
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle text-success me-2"></i>Invoice Details</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Client <span class="text-danger">*</span></label>
                            <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                                <option value="">— Select Client —</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}" @selected(old('customer_id') == $c->id)>{{ $c->name }} ({{ $c->customer_code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Related Project</label>
                            <select name="project_id" class="form-select">
                                <option value="">— Standalone / Maintenance —</option>
                                @foreach($projects as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Approved Quotation (Optional)</label>
                            <select name="quotation_id" class="form-select">
                                <option value="">— None —</option>
                                @foreach($quotations as $q)
                                    <option value="{{ $q->id }}">{{ $q->quotation_number }} (₹{{ number_format($q->grand_total) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Invoice Date <span class="text-danger">*</span></label>
                            <input type="date" name="invoice_date" class="form-control" value="{{ old('invoice_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Payment Due Date <span class="text-danger">*</span></label>
                            <input type="date" name="due_date" class="form-control" value="{{ old('due_date', date('Y-m-d', strtotime('+15 days'))) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Subtotal (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="subtotal" class="form-control" value="{{ old('subtotal', 0) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">GST Tax (₹)</label>
                            <input type="number" step="0.01" name="tax_amount" class="form-control" value="{{ old('tax_amount', 0) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Discount (₹)</label>
                            <input type="number" step="0.01" name="discount_amount" class="form-control" value="{{ old('discount_amount', 0) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Payment Terms</label>
                            <textarea name="terms" class="form-control" rows="2">{{ old('terms', 'Payment due within 15 days. Bank Transfer or Cheque payable to Horticulture CRM.') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success py-2 px-4 fw-semibold">
                    <i class="bi bi-check2-circle me-1"></i> Generate Invoice
                </button>
                <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
    </div>
</form>
@endsection
