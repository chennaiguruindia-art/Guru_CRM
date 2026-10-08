@extends('layouts.app')
@section('title', 'Project Estimations')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-calculator-fill text-success me-2"></i>Estimations</h4>
        <p class="text-muted small mb-0">Prepare dynamic project estimations with multi-category costing.</p>
    </div>
    <a href="{{ route('estimations.create') }}" class="btn btn-success shadow-sm">
        <i class="bi bi-plus-lg me-1"></i> New Estimation
    </a>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('estimations.index') }}">
            <div class="row g-2">
                <div class="col-12 col-md-5">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search number, title, client…" value="{{ request('search') }}">
                </div>
                <div class="col-6 col-md-3">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        @foreach(['Draft', 'Converted to Quotation', 'Archived'] as $s)
                            <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-success btn-sm flex-fill"><i class="bi bi-search me-1"></i>Filter</button>
                    <a href="{{ route('estimations.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-lg"></i></a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-crm align-middle mb-0">
            <thead>
                <tr>
                    <th>Estimation No</th>
                    <th>Client</th>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Cost Breakdown (Subtotal)</th>
                    <th>Grand Total</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($estimations as $est)
                <tr>
                    <td><a href="{{ route('estimations.show', $est) }}" class="fw-semibold text-success text-decoration-none">{{ $est->estimation_number }}</a></td>
                    <td>{{ $est->customer?->name ?? '—' }}</td>
                    <td class="fw-semibold">{{ $est->title }}</td>
                    <td class="small">{{ $est->date?->format('d M Y') }}</td>
                    <td class="small">
                        <span class="badge bg-light text-dark border">P: ₹{{ number_format($est->plants_total) }}</span>
                        <span class="badge bg-light text-dark border">M: ₹{{ number_format($est->materials_total) }}</span>
                        <span class="badge bg-light text-dark border">L: ₹{{ number_format($est->labour_total) }}</span>
                    </td>
                    <td class="fw-bold text-success">₹{{ number_format($est->grand_total, 2) }}</td>
                    <td>
                        <span class="badge {{ $est->status === 'Draft' ? 'bg-secondary' : 'bg-success' }}">{{ $est->status }}</span>
                    </td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-1">
                            <a href="{{ route('estimations.show', $est) }}" class="btn btn-sm btn-outline-success" title="View"><i class="bi bi-eye"></i></a>
                            <form method="POST" action="{{ route('estimations.destroy', $est) }}" onsubmit="return confirm('Delete this estimation?');" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <i class="bi bi-calculator fs-1 text-muted d-block mb-2"></i>
                        <span class="text-muted">No estimations found. <a href="{{ route('estimations.create') }}">Create your first estimation</a>.</span>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($estimations->hasPages())
    <div class="card-footer bg-white py-3">{{ $estimations->withQueryString()->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
