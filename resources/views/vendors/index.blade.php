@extends('layouts.app')
@section('title', 'Vendors')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-shop text-success me-2"></i>Vendors & Suppliers</h4>
        <p class="text-muted small mb-0">Plant nurseries, fertilizer suppliers, pot makers, and equipment vendors.</p>
    </div>
    <a href="{{ route('vendors.create') }}" class="btn btn-success shadow-sm">
        <i class="bi bi-plus-lg me-1"></i> Add Vendor
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-crm align-middle mb-0">
            <thead>
                <tr>
                    <th>Vendor Code</th>
                    <th>Vendor Name</th>
                    <th>Contact Person</th>
                    <th>Phone</th>
                    <th>City</th>
                    <th>GST</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vendors as $vendor)
                <tr>
                    <td><a href="{{ route('vendors.show', $vendor) }}" class="fw-semibold text-success text-decoration-none">{{ $vendor->vendor_code }}</a></td>
                    <td class="fw-semibold">{{ $vendor->name }}</td>
                    <td>{{ $vendor->contact_person ?: '—' }}</td>
                    <td class="small">{{ $vendor->phone }}</td>
                    <td class="small">{{ $vendor->city ?: '—' }}</td>
                    <td class="small">{{ $vendor->gst_number ?: '—' }}</td>
                    <td>
                        <span class="badge {{ $vendor->status === 'active' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($vendor->status) }}</span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('vendors.show', $vendor) }}" class="btn btn-sm btn-outline-success"><i class="bi bi-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <i class="bi bi-shop fs-1 text-muted d-block mb-2"></i>
                        <span class="text-muted">No vendors found. <a href="{{ route('vendors.create') }}">Add a vendor</a>.</span>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($vendors->hasPages())
    <div class="card-footer bg-white py-3">{{ $vendors->withQueryString()->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
