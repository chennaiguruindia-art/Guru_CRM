@extends('layouts.app')
@section('title','Quotations')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-file-text-fill text-success me-2"></i>Quotations</h4>
        <p class="text-muted small mb-0">Create and manage all client quotations.</p>
    </div>
    <a href="{{ route('quotations.create') }}" class="btn btn-success shadow-sm"><i class="bi bi-plus-lg me-1"></i>New Quotation</a>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('quotations.index') }}">
            <div class="row g-2">
                <div class="col-12 col-md-4">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search quotation number, client…" value="{{ request('search') }}">
                </div>
                <div class="col-6 col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        @foreach(['Draft','Sent','Under Review','Approved','Rejected','Expired'] as $s)
                            <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-success btn-sm flex-fill"><i class="bi bi-search me-1"></i>Filter</button>
                    <a href="{{ route('quotations.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-lg"></i></a>
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
                    <th>Quotation No</th>
                    <th>Client</th>
                    <th>Date</th>
                    <th>Valid Until</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Sales Person</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($quotations as $quotation)
                <tr>
                    <td><a href="{{ route('quotations.show',$quotation) }}" class="fw-semibold text-success text-decoration-none">{{ $quotation->quotation_number }}</a></td>
                    <td>
                        <div class="fw-semibold small">{{ $quotation->customer?->name ?? '—' }}</div>
                    </td>
                    <td class="small">{{ $quotation->date?->format('d M Y') }}</td>
                    <td class="small {{ $quotation->valid_until && $quotation->valid_until->isPast() && $quotation->status !== 'Approved' ? 'text-danger' : '' }}">
                        {{ $quotation->valid_until?->format('d M Y') ?? '—' }}
                    </td>
                    <td class="fw-semibold">₹{{ number_format($quotation->grand_total, 2) }}</td>
                    <td>
                        <span class="badge
                            @if($quotation->status==='Approved') bg-success
                            @elseif($quotation->status==='Rejected') bg-danger
                            @elseif($quotation->status==='Draft') bg-secondary
                            @elseif($quotation->status==='Sent') bg-info text-dark
                            @elseif($quotation->status==='Under Review') bg-warning text-dark
                            @else bg-secondary @endif">
                            {{ $quotation->status }}
                        </span>
                    </td>
                    <td class="small">{{ $quotation->salesperson?->name ?? '—' }}</td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-1">
                            <a href="{{ route('quotations.show',$quotation) }}" class="btn btn-sm btn-outline-success" title="View"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('quotations.print',$quotation) }}" class="btn btn-sm btn-outline-secondary" title="Print" target="_blank"><i class="bi bi-printer"></i></a>
                            <button class="btn btn-sm btn-outline-danger btn-delete" data-id="{{ $quotation->id }}" data-name="{{ $quotation->quotation_number }}" title="Delete"><i class="bi bi-trash"></i></button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-5"><i class="bi bi-file-text fs-1 text-muted d-block mb-2"></i><span class="text-muted">No quotations yet. <a href="{{ route('quotations.create') }}">Create your first quotation</a>.</span></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($quotations->hasPages())
    <div class="card-footer bg-white py-3">{{ $quotations->withQueryString()->links('pagination::bootstrap-5') }}</div>
    @endif
</div>

<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <form id="delete-form" method="POST">@csrf @method('DELETE')
                <div class="modal-header"><h5 class="modal-title text-danger"><i class="bi bi-trash me-1"></i>Delete Quotation</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body"><p class="small text-muted mb-0">Delete <strong id="del-name"></strong>?</p></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('del-name').textContent = this.dataset.name;
        document.getElementById('delete-form').action = `/quotations/${this.dataset.id}`;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    });
});
</script>
@endpush
