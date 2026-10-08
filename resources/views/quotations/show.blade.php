@extends('layouts.app')
@section('title','Quotation — '.$quotation->quotation_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-file-text-fill text-success me-2"></i>{{ $quotation->quotation_number }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('quotations.index') }}" class="text-success text-decoration-none">Quotations</a></li>
            <li class="breadcrumb-item active">{{ $quotation->quotation_number }}</li>
        </ol></nav>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('quotations.print',$quotation) }}" class="btn btn-outline-secondary btn-sm" target="_blank"><i class="bi bi-printer me-1"></i>Print / PDF</a>
        <form method="POST" action="{{ route('quotations.status',$quotation) }}" class="d-inline">
            @csrf @method('PATCH')
            <select name="status" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()" title="Update Status">
                @foreach(['Draft','Sent','Under Review','Approved','Rejected','Expired'] as $s)
                    <option value="{{ $s }}" @selected($quotation->status===$s)>{{ $s }}</option>
                @endforeach
            </select>
        </form>
        <a href="{{ route('quotations.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

{{-- Quotation preview --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-5">
        {{-- Header --}}
        <div class="row mb-4">
            <div class="col-7">
                <div class="text-success fw-bold fs-4">🌿 Horticulture CRM</div>
                <div class="text-muted small">Your Green Partner</div>
            </div>
            <div class="col-5 text-end">
                <h3 class="fw-bold text-dark mb-1">QUOTATION</h3>
                <div class="text-success fw-semibold">{{ $quotation->quotation_number }}</div>
                <div class="small text-muted">Date: {{ $quotation->date?->format('d M Y') }}</div>
                @if($quotation->valid_until)
                <div class="small text-muted">Valid Until: {{ $quotation->valid_until->format('d M Y') }}</div>
                @endif
                <span class="badge mt-1
                    @if($quotation->status==='Approved') bg-success
                    @elseif($quotation->status==='Rejected') bg-danger
                    @elseif($quotation->status==='Draft') bg-secondary
                    @elseif($quotation->status==='Sent') bg-info text-dark
                    @else bg-warning text-dark @endif">
                    {{ $quotation->status }}
                </span>
            </div>
        </div>

        {{-- Bill To --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6">
                <div class="bg-light rounded p-3 h-100">
                    <div class="small text-muted mb-1">BILL TO</div>
                    <div class="fw-bold">{{ $quotation->customer?->name }}</div>
                    @if($quotation->customer?->company_name)<div class="small">{{ $quotation->customer->company_name }}</div>@endif
                    @if($quotation->customer?->address)<div class="small text-muted">{{ $quotation->customer->address }}</div>@endif
                    <div class="small text-muted">{{ $quotation->customer?->phone }}</div>
                    @if($quotation->customer?->gst_number)<div class="small">GST: {{ $quotation->customer->gst_number }}</div>@endif
                </div>
            </div>
        </div>

        {{-- Items --}}
        <div class="table-responsive mb-4">
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th class="text-end">Qty</th>
                        <th>Unit</th>
                        <th class="text-end">Rate</th>
                        <th class="text-end">Disc %</th>
                        <th class="text-end">Tax %</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quotation->items as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $item->description }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $item->category }}</span></td>
                        <td class="text-end">{{ $item->quantity }}</td>
                        <td>{{ $item->unit }}</td>
                        <td class="text-end">₹{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-end">{{ $item->discount_percent ?? 0 }}%</td>
                        <td class="text-end">{{ $item->tax_percent ?? 0 }}%</td>
                        <td class="text-end fw-semibold">₹{{ number_format($item->amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center text-muted">No items</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Totals --}}
        <div class="row justify-content-end">
            <div class="col-md-5">
                <table class="table table-sm table-borderless">
                    <tr><td class="text-muted">Subtotal</td><td class="text-end fw-semibold">₹{{ number_format($quotation->subtotal, 2) }}</td></tr>
                    @if($quotation->discount_amount > 0)
                    <tr><td class="text-muted">Discount</td><td class="text-end text-danger">- ₹{{ number_format($quotation->discount_amount, 2) }}</td></tr>
                    @endif
                    @if($quotation->tax_amount > 0)
                    <tr><td class="text-muted">GST ({{ $quotation->tax_percent ?? '' }}%)</td><td class="text-end">₹{{ number_format($quotation->tax_amount, 2) }}</td></tr>
                    @endif
                    <tr class="border-top"><td class="fw-bold fs-5">Grand Total</td><td class="text-end fw-bold text-success fs-5">₹{{ number_format($quotation->grand_total, 2) }}</td></tr>
                </table>
            </div>
        </div>

        {{-- Terms --}}
        @if($quotation->terms)
        <div class="mt-4 border-top pt-4">
            <div class="fw-semibold small mb-2">Terms & Conditions</div>
            <div class="text-muted small">{!! nl2br(e($quotation->terms)) !!}</div>
        </div>
        @endif
        @if($quotation->notes)
        <div class="mt-3">
            <div class="fw-semibold small mb-1">Notes</div>
            <div class="text-muted small">{{ $quotation->notes }}</div>
        </div>
        @endif

        {{-- Footer --}}
        <div class="mt-5 pt-4 border-top d-flex justify-content-between">
            <div class="small text-muted">Prepared by: {{ $quotation->salesperson?->name ?? auth()->user()->name }}</div>
            <div class="text-end">
                <div class="border-top mt-5 pt-1 small text-muted">Authorized Signature</div>
            </div>
        </div>
    </div>
</div>
@endsection
