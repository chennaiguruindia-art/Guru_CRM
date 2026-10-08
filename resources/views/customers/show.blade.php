@extends('layouts.app')
@section('title','Client — '.$customer->customer_code)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-people-fill text-success me-2"></i>{{ $customer->name }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('customers.index') }}" class="text-success text-decoration-none">Clients</a></li>
            <li class="breadcrumb-item active">{{ $customer->customer_code }}</li>
        </ol></nav>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('customers.edit',$customer) }}" class="btn btn-primary btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

{{-- 360° tabs --}}
<ul class="nav nav-tabs mb-4" id="customerTabs" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-overview">Overview</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-quotations">Quotations <span class="badge bg-secondary ms-1">{{ $customer->quotations->count() }}</span></button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-projects">Projects <span class="badge bg-secondary ms-1">{{ $customer->projects->count() }}</span></button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-invoices">Invoices <span class="badge bg-secondary ms-1">{{ $customer->invoices->count() }}</span></button></li>
</ul>

<div class="tab-content" id="customerTabsContent">
    {{-- Overview --}}
    <div class="tab-pane fade show active" id="tab-overview">
        <div class="row g-4">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between">
                        <h6 class="mb-0 fw-semibold"><i class="bi bi-person text-success me-2"></i>Client Details</h6>
                        <span class="badge {{ $customer->status === 'Active' ? 'bg-success' : 'bg-secondary' }}">{{ $customer->status }}</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-sm-6"><label class="text-muted small d-block">Client ID</label><span class="fw-semibold text-success">{{ $customer->customer_code }}</span></div>
                            <div class="col-sm-6"><label class="text-muted small d-block">Type</label><span class="badge bg-soft-info text-info border">{{ $customer->type }}</span></div>
                            <div class="col-sm-6"><label class="text-muted small d-block">Phone</label><a href="tel:{{ $customer->phone }}" class="fw-semibold text-decoration-none">{{ $customer->phone }}</a></div>
                            <div class="col-sm-6"><label class="text-muted small d-block">Email</label>{{ $customer->email ?: '—' }}</div>
                            <div class="col-sm-6"><label class="text-muted small d-block">WhatsApp</label>
                                @if($customer->whatsapp)<a href="https://wa.me/{{ preg_replace('/\D/','',$customer->whatsapp) }}" target="_blank" class="text-success text-decoration-none"><i class="bi bi-whatsapp me-1"></i>{{ $customer->whatsapp }}</a>@else<span class="text-muted">—</span>@endif
                            </div>
                            <div class="col-sm-6"><label class="text-muted small d-block">GST Number</label>{{ $customer->gst_number ?: '—' }}</div>
                            @if($customer->address)
                            <div class="col-12"><label class="text-muted small d-block">Address</label>{{ $customer->address }}, {{ $customer->city }}, {{ $customer->state }} {{ $customer->pincode }}</div>
                            @endif
                            @if($customer->notes)
                            <div class="col-12"><label class="text-muted small d-block">Notes</label><p class="mb-0 small bg-light p-3 rounded">{{ $customer->notes }}</p></div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                {{-- KPI summary cards --}}
                <div class="row g-3">
                    <div class="col-4">
                        <div class="card border-0 shadow-sm text-center p-3 card-kpi">
                            <div class="fw-bold fs-3 text-primary">{{ $customer->projects->count() }}</div>
                            <div class="text-muted small">Projects</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="card border-0 shadow-sm text-center p-3 card-kpi">
                            <div class="fw-bold fs-3 text-warning">{{ $customer->quotations->count() }}</div>
                            <div class="text-muted small">Quotations</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="card border-0 shadow-sm text-center p-3 card-kpi">
                            <div class="fw-bold fs-3 text-danger">{{ $customer->invoices->count() }}</div>
                            <div class="text-muted small">Invoices</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quotations Tab --}}
    <div class="tab-pane fade" id="tab-quotations">
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('quotations.create') }}?customer_id={{ $customer->id }}" class="btn btn-success btn-sm"><i class="bi bi-plus me-1"></i>Create Quotation</a>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-crm align-middle mb-0">
                    <thead><tr><th>Quot. No</th><th>Date</th><th>Amount</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                        @forelse($customer->quotations as $q)
                        <tr>
                            <td class="text-success fw-semibold">{{ $q->quotation_number }}</td>
                            <td class="small">{{ $q->date?->format('d M Y') }}</td>
                            <td class="fw-semibold">₹{{ number_format($q->grand_total,2) }}</td>
                            <td><span class="badge bg-info text-dark">{{ $q->status }}</span></td>
                            <td><a href="{{ route('quotations.show',$q) }}" class="btn btn-sm btn-outline-success"><i class="bi bi-eye"></i></a></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-4 text-muted">No quotations yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Projects Tab --}}
    <div class="tab-pane fade" id="tab-projects">
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-crm align-middle mb-0">
                    <thead><tr><th>Project ID</th><th>Name</th><th>Status</th><th>Start Date</th><th>Actions</th></tr></thead>
                    <tbody>
                        @forelse($customer->projects as $project)
                        <tr>
                            <td class="text-success fw-semibold">{{ $project->project_code }}</td>
                            <td>{{ $project->name }}</td>
                            <td><span class="badge bg-warning text-dark">{{ $project->status }}</span></td>
                            <td class="small">{{ $project->start_date?->format('d M Y') }}</td>
                            <td><a href="{{ route('projects.show',$project) }}" class="btn btn-sm btn-outline-success"><i class="bi bi-eye"></i></a></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-4 text-muted">No projects yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Invoices Tab --}}
    <div class="tab-pane fade" id="tab-invoices">
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-crm align-middle mb-0">
                    <thead><tr><th>Invoice No</th><th>Date</th><th>Amount</th><th>Balance</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($customer->invoices as $inv)
                        <tr>
                            <td class="text-success fw-semibold">{{ $inv->invoice_number }}</td>
                            <td class="small">{{ $inv->invoice_date?->format('d M Y') }}</td>
                            <td>₹{{ number_format($inv->total_amount,2) }}</td>
                            <td class="text-danger">₹{{ number_format($inv->balance_amount,2) }}</td>
                            <td><span class="badge {{ $inv->payment_status === 'paid' ? 'bg-success' : 'bg-danger' }}">{{ ucfirst($inv->payment_status) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-4 text-muted">No invoices yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
