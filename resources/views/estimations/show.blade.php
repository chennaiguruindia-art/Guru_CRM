@extends('layouts.app')
@section('title', 'Estimation — ' . $estimation->estimation_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-calculator-fill text-success me-2"></i>{{ $estimation->estimation_number }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-0"><li class="breadcrumb-item"><a href="{{ route('estimations.index') }}" class="text-success text-decoration-none">Estimations</a></li><li class="breadcrumb-item active">{{ $estimation->estimation_number }}</li></ol></nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('quotations.create') }}?customer_id={{ $estimation->customer_id }}" class="btn btn-success btn-sm">
            <i class="bi bi-file-earmark-text me-1"></i> Convert to Quotation
        </a>
        <a href="{{ route('estimations.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle text-success me-2"></i>{{ $estimation->title }}</h6>
                <span class="badge {{ $estimation->status === 'Draft' ? 'bg-secondary' : 'bg-success' }}">{{ $estimation->status }}</span>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-sm-6"><label class="text-muted small d-block">Client</label><span class="fw-semibold">{{ $estimation->customer?->name }}</span></div>
                    <div class="col-sm-6"><label class="text-muted small d-block">Date</label><span>{{ $estimation->date?->format('d M Y') }}</span></div>
                    <div class="col-sm-6"><label class="text-muted small d-block">Created By</label><span>{{ $estimation->createdBy?->name ?? 'Staff' }}</span></div>
                    @if($estimation->notes)
                    <div class="col-12"><label class="text-muted small d-block">Notes</label><p class="bg-light p-3 rounded mb-0 small">{{ $estimation->notes }}</p></div>
                    @endif
                </div>
            </div>
        </div>

        @if($estimation->items->isNotEmpty())
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-list-ul text-info me-2"></i>Components</h6>
                <span class="badge bg-info-subtle text-info">{{ $estimation->items->count() }} item{{ $estimation->items->count() === 1 ? '' : 's' }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Component</th>
                            <th>Category</th>
                            <th class="text-end">Qty</th>
                            <th>Unit</th>
                            <th class="text-end">Rate (₹)</th>
                            <th class="text-end">Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($estimation->items as $item)
                        <tr>
                            <td>
                                <span class="fw-semibold">{{ $item->item_name }}</span>
                                @if($item->notes)<div class="text-muted small">{{ $item->notes }}</div>@endif
                            </td>
                            <td><span class="badge bg-secondary-subtle text-secondary">{{ $item->item_type }}</span></td>
                            <td class="text-end">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                            <td>{{ $item->unit }}</td>
                            <td class="text-end">₹{{ number_format($item->rate, 2) }}</td>
                            <td class="text-end fw-semibold">₹{{ number_format($item->amount, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-light">
                            <td colspan="5" class="text-end fw-bold">Component Total</td>
                            <td class="text-end fw-bold">₹{{ number_format($estimation->items->sum('amount'), 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-list-check text-info me-2"></i>Cost Breakdown</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Cost Component</th>
                            <th class="text-end">Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>🌿 Plants & Saplings</td><td class="text-end fw-semibold">₹{{ number_format($estimation->plants_total, 2) }}</td></tr>
                        <tr><td>🧱 Hardscape Materials, Soil & Fertilizers</td><td class="text-end fw-semibold">₹{{ number_format($estimation->materials_total, 2) }}</td></tr>
                        <tr><td>👷 Labour & Execution</td><td class="text-end fw-semibold">₹{{ number_format($estimation->labour_total, 2) }}</td></tr>
                        <tr><td>🚚 Transportation & Freight</td><td class="text-end fw-semibold">₹{{ number_format($estimation->transport_total, 2) }}</td></tr>
                        <tr><td>⚙️ Machinery & Other Expenses</td><td class="text-end fw-semibold">₹{{ number_format($estimation->other_total, 2) }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">Financial Summary</h6>
            </div>
            <div class="card-body p-4">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted">Total Cost Subtotal</td><td class="text-end fw-semibold">₹{{ number_format($estimation->subtotal, 2) }}</td></tr>
                    <tr><td class="text-muted">Profit Margin ({{ $estimation->profit_margin_percent }}%)</td><td class="text-end text-success">+ ₹{{ number_format($estimation->profit_margin_amount, 2) }}</td></tr>
                    @if($estimation->discount_amount > 0)
                    <tr><td class="text-muted">Discount</td><td class="text-end text-danger">- ₹{{ number_format($estimation->discount_amount, 2) }}</td></tr>
                    @endif
                    <tr><td class="text-muted">GST Tax ({{ $estimation->tax_percent }}%)</td><td class="text-end">₹{{ number_format($estimation->tax_amount, 2) }}</td></tr>
                    <tr class="border-top"><td class="fw-bold fs-5">Estimated Total</td><td class="text-end fw-bold text-success fs-5">₹{{ number_format($estimation->grand_total, 2) }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
