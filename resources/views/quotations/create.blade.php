@extends('layouts.app')
@section('title','Create Quotation')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-file-plus text-success me-2"></i>Create Quotation</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-0"><li class="breadcrumb-item"><a href="{{ route('quotations.index') }}" class="text-success text-decoration-none">Quotations</a></li><li class="breadcrumb-item active">Create New</li></ol></nav>
    </div>
    <a href="{{ route('quotations.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<form method="POST" action="{{ route('quotations.store') }}" id="quotation-form">
    @csrf
    <div class="row g-4">
        {{-- Main area --}}
        <div class="col-12 col-lg-9">
            {{-- Header Info --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold"><i class="bi bi-person text-success me-2"></i>Quotation Details</h6></div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Client <span class="text-danger">*</span></label>
                            <select name="customer_id" id="customer-select" class="form-select @error('customer_id') is-invalid @enderror" required>
                                <option value="">— Select Client —</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" @selected(old('customer_id', request('customer_id'))==$customer->id)>{{ $customer->name }} ({{ $customer->customer_code }})</option>
                                @endforeach
                            </select>
                            @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Quotation Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control" value="{{ old('date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Valid Until</label>
                            <input type="date" name="valid_until" class="form-control" value="{{ old('valid_until', date('Y-m-d', strtotime('+30 days'))) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Sales Person</label>
                            <select name="sales_person_id" class="form-select">
                                <option value="">— Select —</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" @selected(old('sales_person_id', auth()->id())==$user->id)>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Status</label>
                            <select name="status" class="form-select">
                                @foreach(['Draft','Sent','Under Review','Approved','Rejected'] as $s)
                                    <option value="{{ $s }}" @selected(old('status','Draft')===$s)>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Line Items --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-list-ul text-info me-2"></i>Line Items</h6>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="addRow('Plant')"><i class="bi bi-flower1 me-1"></i>Plant</button>
                        <button type="button" class="btn btn-outline-info btn-sm" onclick="addRow('Material')"><i class="bi bi-box me-1"></i>Material</button>
                        <button type="button" class="btn btn-outline-warning btn-sm" onclick="addRow('Labour')"><i class="bi bi-person-gear me-1"></i>Labour</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addRow('Other')"><i class="bi bi-plus me-1"></i>Other</button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0" id="items-table">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:35%">Description</th>
                                    <th style="width:10%">Category</th>
                                    <th style="width:8%">Qty</th>
                                    <th style="width:8%">Unit</th>
                                    <th style="width:12%">Rate (₹)</th>
                                    <th style="width:8%">Disc %</th>
                                    <th style="width:8%">Tax %</th>
                                    <th style="width:12%" class="text-end">Amount</th>
                                    <th style="width:3%"></th>
                                </tr>
                            </thead>
                            <tbody id="items-body">
                                {{-- Rows added dynamically --}}
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3 text-center text-muted small" id="empty-hint">
                        <i class="bi bi-plus-circle me-1"></i>Click Plant / Material / Labour / Other above to add line items.
                    </div>
                </div>
            </div>

            {{-- Terms --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold"><i class="bi bi-file-earmark-text text-secondary me-2"></i>Terms & Notes</h6></div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Terms & Conditions</label>
                            <textarea name="terms" class="form-control" rows="4">{{ old('terms', "1. Prices are valid for 30 days from quotation date.\n2. Payment: 50% advance, 50% on completion.\n3. Plant mortality warranty: 15 days from delivery.\n4. GST as applicable.\n5. Work to be executed in normal working hours.") }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Any special notes for this quotation…">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Totals --}}
        <div class="col-12 col-lg-3">
            <div class="card border-0 shadow-sm sticky-top" style="top:80px">
                <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold">Quotation Summary</h6></div>
                <div class="card-body p-3">
                    <table class="table table-sm table-borderless mb-0 small">
                        <tr><td class="text-muted">Subtotal</td><td class="text-end fw-semibold" id="display-subtotal">₹0.00</td></tr>
                        <tr>
                            <td class="text-muted">Discount</td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1 align-items-center">
                                    <input type="number" name="discount_amount" id="discount-amount" class="form-control form-control-sm text-end" style="width:80px" value="{{ old('discount_amount',0) }}" min="0" step="0.01">
                                    <select name="discount_type" id="discount-type" class="form-select form-select-sm" style="width:65px">
                                        <option value="fixed" @selected(old('discount_type','fixed')==='fixed')>₹</option>
                                        <option value="percent" @selected(old('discount_type')==='percent')>%</option>
                                    </select>
                                </div>
                            </td>
                        </tr>
                        <tr><td class="text-muted">After Disc.</td><td class="text-end" id="display-after-disc">₹0.00</td></tr>
                        <tr>
                            <td class="text-muted">GST %</td>
                            <td class="text-end">
                                <input type="number" name="tax_percent" id="tax-percent" class="form-control form-control-sm text-end" style="width:80px;margin-left:auto" value="{{ old('tax_percent',18) }}" min="0" max="28" step="0.01">
                            </td>
                        </tr>
                        <tr><td class="text-muted">GST Amount</td><td class="text-end" id="display-tax">₹0.00</td></tr>
                        <tr class="border-top"><td class="fw-bold fs-6">Grand Total</td><td class="text-end fw-bold text-success fs-6" id="display-grand-total">₹0.00</td></tr>
                    </table>

                    {{-- Hidden totals for form submission --}}
                    <input type="hidden" name="subtotal" id="h-subtotal" value="0">
                    <input type="hidden" name="tax_amount" id="h-tax" value="0">
                    <input type="hidden" name="grand_total" id="h-grand-total" value="0">
                    <input type="hidden" name="items_json" id="h-items-json" value="[]">

                    <hr>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success py-2 fw-semibold" id="submit-btn"><i class="bi bi-check2-circle me-1"></i>Save Quotation</button>
                        <a href="{{ route('quotations.index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script src="{{ asset('js/quotations.js') }}"></script>
<script>
// Form submit: serialise items to hidden field
document.getElementById('quotation-form').addEventListener('submit', function() {
    serializeItems();
});
</script>
@endpush
