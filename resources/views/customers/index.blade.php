@extends('layouts.app')
@section('title','Clients')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-people-fill text-success me-2"></i>Clients</h4>
        <p class="text-muted small mb-0">Manage your complete client database.</p>
    </div>
    <a href="{{ route('customers.create') }}" class="btn btn-success shadow-sm"><i class="bi bi-plus-lg me-1"></i>Add Client</a>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('customers.index') }}">
            <div class="row g-2">
                <div class="col-12 col-md-4">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name, phone, email, company…" value="{{ request('search') }}">
                </div>
                <div class="col-6 col-md-2">
                    <select name="type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        @foreach(['Individual','Company','Apartment','Villa','School','Hospital','Hotel','Factory','Corporate','Government','Other'] as $t)
                            <option value="{{ $t }}" @selected(request('type')===$t)>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        @foreach(['Active','Inactive','Prospect','Blacklisted'] as $s)
                            <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-success btn-sm flex-fill"><i class="bi bi-search me-1"></i>Search</button>
                    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-lg"></i></a>
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
                    <th>Client ID</th>
                    <th>Name / Company</th>
                    <th>Type</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>City</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                <tr>
                    <td><a href="{{ route('customers.show',$customer) }}" class="fw-semibold text-success text-decoration-none">{{ $customer->customer_code }}</a></td>
                    <td>
                        <div class="fw-semibold">{{ $customer->name }}</div>
                        @if($customer->company_name)<div class="text-muted small">{{ $customer->company_name }}</div>@endif
                    </td>
                    <td><span class="badge bg-soft-info text-info border">{{ $customer->type }}</span></td>
                    <td class="small">{{ $customer->phone }}</td>
                    <td class="small">{{ $customer->email ?: '—' }}</td>
                    <td class="small">{{ $customer->city ?: '—' }}</td>
                    <td>
                        <span class="badge {{ $customer->status === 'Active' ? 'bg-success' : 'bg-secondary' }}">{{ $customer->status }}</span>
                    </td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-1">
                            <a href="{{ route('customers.show',$customer) }}" class="btn btn-sm btn-outline-success" title="View"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('customers.edit',$customer) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                            <button class="btn btn-sm btn-outline-danger btn-delete" data-id="{{ $customer->id }}" data-name="{{ $customer->name }}" title="Delete"><i class="bi bi-trash"></i></button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-5"><i class="bi bi-people fs-1 text-muted d-block mb-2"></i><span class="text-muted">No clients yet. <a href="{{ route('customers.create') }}">Add your first client</a>.</span></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($customers->hasPages())
    <div class="card-footer bg-white py-3">{{ $customers->withQueryString()->links('pagination::bootstrap-5') }}</div>
    @endif
</div>

{{-- Delete Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <form id="delete-form" method="POST">@csrf @method('DELETE')
                <div class="modal-header"><h5 class="modal-title text-danger"><i class="bi bi-trash me-1"></i>Delete Client</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body"><p class="text-muted small mb-0">Delete <strong id="delete-name"></strong>? This cannot be undone.</p></div>
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
        document.getElementById('delete-name').textContent = this.dataset.name;
        document.getElementById('delete-form').action = `/customers/${this.dataset.id}`;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    });
});
</script>
@endpush
