<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation {{ $quotation->quotation_number }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', 'Segoe UI', sans-serif; font-size: 13px; color: #1f2937; }
        .header-bar { background: #166534; color: #fff; padding: 18px 24px; }
        .company-name { font-size: 1.5rem; font-weight: 800; letter-spacing: -0.3px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #166534; color: #fff; padding: 9px 10px; text-align: left; font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.06em; }
        td { padding: 8px 10px; border-bottom: 1px solid #eceef1; vertical-align: top; }
        tr:nth-child(even) td { background: #f9fafb; }
        .totals-table td { border: none !important; padding: 4px 10px; }
        .grand-total { font-size: 1.15rem; font-weight: 800; color: #166534; }
        .badge-status { padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; }
        .bill-box { background: #f5f7f9; border: 1px solid #eceef1; border-radius: 8px; padding: 12px 16px; }
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
        }
    </style>
</head>
<body>
    <div class="no-print mb-3 p-3 bg-light border-bottom d-flex gap-2">
        <button onclick="window.print()" class="btn btn-success btn-sm"><i class="bi bi-printer me-1"></i>Print</button>
        <a href="{{ route('quotations.show',$quotation) }}" class="btn btn-outline-secondary btn-sm">← Back to CRM</a>
    </div>

    <div style="max-width:900px;margin:0 auto;padding:24px">
        {{-- Header --}}
        <div class="header-bar mb-0" style="border-radius:6px 6px 0 0;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="company-name">🌿 Horticulture CRM</div>
                    <div style="font-size:12px;opacity:0.8">Your Green Partner | horticulture@example.com | +91 98765 43210</div>
                </div>
                <div class="text-end text-white">
                    <div style="font-size:1.4rem;font-weight:700">QUOTATION</div>
                    <div style="font-size:1.1rem;opacity:0.9">{{ $quotation->quotation_number }}</div>
                </div>
            </div>
        </div>

        {{-- Meta --}}
        <div class="d-flex justify-content-between border px-4 py-3 mb-4" style="background:#f9faf9;border-radius:0 0 6px 6px">
            <div><strong>Date:</strong> {{ $quotation->date?->format('d M Y') }}</div>
            <div><strong>Valid Until:</strong> {{ $quotation->valid_until?->format('d M Y') ?? 'N/A' }}</div>
            <div><strong>Status:</strong>
                <span class="badge-status
                    @if($quotation->status==='Approved') bg-success text-white
                    @elseif($quotation->status==='Rejected') bg-danger text-white
                    @else bg-warning text-dark @endif">
                    {{ $quotation->status }}
                </span>
            </div>
            <div><strong>Sales Person:</strong> {{ $quotation->salesperson?->name ?? '—' }}</div>
        </div>

        {{-- Bill To --}}
        <div class="row mb-4 g-3">
            <div class="col-12">
                <div class="bill-box">
                    <div style="font-size:10px;text-transform:uppercase;color:#888;margin-bottom:6px;font-weight:600">Bill To</div>
                    <div style="font-size:1rem;font-weight:700">{{ $quotation->customer?->name }}</div>
                    @if($quotation->customer?->company_name)<div>{{ $quotation->customer->company_name }}</div>@endif
                    @if($quotation->customer?->address)<div style="color:#666">{{ $quotation->customer->address }}, {{ $quotation->customer?->city }}</div>@endif
                    <div style="color:#555">📞 {{ $quotation->customer?->phone }}</div>
                    @if($quotation->customer?->gst_number)<div style="color:#555">GST: {{ $quotation->customer->gst_number }}</div>@endif
                </div>
            </div>
        </div>

        {{-- Items Table --}}
        <table class="mb-4" style="border-radius:6px;overflow:hidden">
            <thead>
                <tr>
                    <th style="width:4%">#</th>
                    <th style="width:35%">Description</th>
                    <th style="width:10%">Category</th>
                    <th style="width:7%;text-align:right">Qty</th>
                    <th style="width:6%">Unit</th>
                    <th style="width:12%;text-align:right">Rate (₹)</th>
                    <th style="width:8%;text-align:right">Disc %</th>
                    <th style="width:8%;text-align:right">Tax %</th>
                    <th style="width:12%;text-align:right">Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($quotation->items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->description }}</td>
                    <td>{{ $item->category }}</td>
                    <td style="text-align:right">{{ $item->quantity }}</td>
                    <td>{{ $item->unit }}</td>
                    <td style="text-align:right">{{ number_format($item->unit_price, 2) }}</td>
                    <td style="text-align:right">{{ $item->discount_percent ?? 0 }}%</td>
                    <td style="text-align:right">{{ $item->tax_percent ?? 0 }}%</td>
                    <td style="text-align:right;font-weight:600">{{ number_format($item->amount, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="9" style="text-align:center;color:#aaa">No items listed</td></tr>
                @endforelse
            </tbody>
        </table>

        {{-- Totals --}}
        <div class="d-flex justify-content-end mb-4">
            <div style="min-width:280px;border:1px solid #eee;border-radius:6px;overflow:hidden">
                <table class="totals-table w-100">
                    <tr><td>Subtotal</td><td style="text-align:right">₹{{ number_format($quotation->subtotal, 2) }}</td></tr>
                    @if($quotation->discount_amount > 0)
                    <tr><td>Discount</td><td style="text-align:right;color:#e53935">- ₹{{ number_format($quotation->discount_amount, 2) }}</td></tr>
                    @endif
                    @if($quotation->tax_amount > 0)
                    <tr><td>GST ({{ $quotation->tax_percent }}%)</td><td style="text-align:right">₹{{ number_format($quotation->tax_amount, 2) }}</td></tr>
                    @endif
                    <tr style="border-top:2px solid #166534;background:#f0fdf4">
                        <td class="grand-total">Grand Total</td>
                        <td class="grand-total" style="text-align:right">₹{{ number_format($quotation->grand_total, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Terms --}}
        @if($quotation->terms)
        <div style="border-top:1px solid #eee;padding-top:16px;margin-bottom:16px">
            <div style="font-weight:600;font-size:13px;margin-bottom:6px">Terms & Conditions</div>
            <div style="color:#666;font-size:12px;white-space:pre-line">{{ $quotation->terms }}</div>
        </div>
        @endif
        @if($quotation->notes)
        <div style="margin-bottom:16px">
            <div style="font-weight:600;font-size:13px;margin-bottom:4px">Notes</div>
            <div style="color:#666;font-size:12px">{{ $quotation->notes }}</div>
        </div>
        @endif

        {{-- Signature --}}
        <div class="d-flex justify-content-between mt-5" style="border-top:1px solid #eee;padding-top:20px">
            <div>
                <div style="border-top:1px solid #333;padding-top:6px;margin-top:40px;font-size:12px;color:#666;width:180px">Client Signature</div>
            </div>
            <div class="text-end">
                <div style="border-top:1px solid #333;padding-top:6px;margin-top:40px;font-size:12px;color:#666;width:180px">Authorized Signatory</div>
            </div>
        </div>

        <div style="text-align:center;color:#aaa;font-size:11px;margin-top:24px">
            This is a computer-generated quotation. Generated on {{ now()->format('d M Y, h:i A') }}
        </div>
    </div>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</body>
</html>
