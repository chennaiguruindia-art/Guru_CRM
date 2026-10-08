@extends('layouts.app')
@section('title', 'AMC Contracts')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-shield-check text-success me-2"></i>Annual Maintenance Contracts (AMC)</h4>
        <p class="text-muted small mb-0">Manage recurring maintenance agreements for residential, commercial and corporate garden maintenance.</p>
    </div>
    <a href="{{ route('amc.create') }}" class="btn btn-success shadow-sm">
        <i class="bi bi-plus-lg me-1"></i> New AMC Contract
    </a>
</div>

{{-- KPI cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-6">
        <div class="card border-0 shadow-sm p-3">
            <span class="text-muted small">Active AMC Contracts</span>
            <h3 class="fw-bold mb-0 text-success">{{ $activeAmcCount }}</h3>
        </div>
    </div>
    <div class="col-6 col-md-6">
        <div class="card border-0 shadow-sm p-3">
            <span class="text-muted small">Total Active Contract Value</span>
            <h3 class="fw-bold mb-0 text-dark">₹{{ number_format($totalValue, 2) }}</h3>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-crm align-middle mb-0">
            <thead>
                <tr>
                    <th>AMC Number</th>
                    <th>Client</th>
                    <th>Period</th>
                    <th>Contract Value</th>
                    <th>Service Frequency</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($contracts as $amc)
                <tr>
                    <td><a href="{{ route('amc.show', $amc) }}" class="fw-semibold text-success text-decoration-none">{{ $amc->amc_number }}</a></td>
                    <td>{{ $amc->customer?->name ?? '—' }}</td>
                    <td class="small">{{ $amc->start_date?->format('d M Y') }} - {{ $amc->end_date?->format('d M Y') }}</td>
                    <td class="fw-bold text-success">₹{{ number_format($amc->contract_value, 2) }}</td>
                    <td><span class="badge bg-light text-dark border">{{ $amc->service_frequency }}</span></td>
                    <td>
                        <span class="badge {{ $amc->status === 'Active' ? 'bg-success' : 'bg-warning text-dark' }}">{{ $amc->status }}</span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('amc.show', $amc) }}" class="btn btn-sm btn-outline-success"><i class="bi bi-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="bi bi-shield-check fs-1 text-muted d-block mb-2"></i>
                        <span class="text-muted">No AMC contracts found. <a href="{{ route('amc.create') }}">Create an AMC contract</a>.</span>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($contracts->hasPages())
    <div class="card-footer bg-white py-3">{{ $contracts->withQueryString()->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
