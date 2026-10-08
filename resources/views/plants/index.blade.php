@extends('layouts.app')
@section('title', 'Plant Master')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-tree-fill text-success me-2"></i>Plant Master</h4>
        <p class="text-muted small mb-0">Catalogue of horticulture plants, saplings, trees, shrubs and lawns.</p>
    </div>
    <a href="{{ route('plants.create') }}" class="btn btn-success shadow-sm">
        <i class="bi bi-plus-lg me-1"></i> Add Plant
    </a>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('plants.index') }}">
            <div class="row g-2">
                <div class="col-12 col-md-4">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search plant name, botanical name, code…" value="{{ request('search') }}">
                </div>
                <div class="col-6 col-md-3">
                    <select name="category_id" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" @selected(request('category_id') == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="plant_type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        @foreach(['Indoor Plants', 'Outdoor Plants', 'Flowering Plants', 'Trees', 'Shrubs', 'Palms', 'Climbers', 'Lawn', 'Ground Covers'] as $t)
                            <option value="{{ $t }}" @selected(request('plant_type') === $t)>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-success btn-sm flex-fill"><i class="bi bi-search me-1"></i>Search</button>
                    <a href="{{ route('plants.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-lg"></i></a>
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
                    <th>Plant Code</th>
                    <th>Name</th>
                    <th>Botanical Name</th>
                    <th>Category / Type</th>
                    <th>Sunlight / Water</th>
                    <th>Purchase (₹)</th>
                    <th>Selling (₹)</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($plants as $plant)
                <tr>
                    <td><a href="{{ route('plants.show', $plant) }}" class="fw-semibold text-success text-decoration-none">{{ $plant->plant_code }}</a></td>
                    <td>
                        <div class="fw-semibold text-dark">{{ $plant->name }}</div>
                        @if($plant->common_name)<small class="text-muted">{{ $plant->common_name }}</small>@endif
                    </td>
                    <td class="small fst-italic">{{ $plant->botanical_name ?: '—' }}</td>
                    <td>
                        <span class="badge bg-light text-dark border">{{ $plant->plant_type }}</span>
                    </td>
                    <td class="small">
                        <div><i class="bi bi-sun text-warning me-1"></i>{{ $plant->sunlight_requirement ?: '—' }}</div>
                        <div><i class="bi bi-droplet-half text-info me-1"></i>{{ $plant->water_requirement ?: '—' }}</div>
                    </td>
                    <td class="small">₹{{ number_format($plant->purchase_price, 2) }}</td>
                    <td class="fw-semibold text-success">₹{{ number_format($plant->selling_price, 2) }}</td>
                    <td>
                        <span class="badge {{ $plant->status === 'active' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($plant->status) }}</span>
                    </td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-1">
                            <a href="{{ route('plants.show', $plant) }}" class="btn btn-sm btn-outline-success" title="View"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('plants.edit', $plant) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('plants.destroy', $plant) }}" onsubmit="return confirm('Delete this plant?');" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5">
                        <i class="bi bi-tree fs-1 text-muted d-block mb-2"></i>
                        <span class="text-muted">No plants found. <a href="{{ route('plants.create') }}">Add your first plant</a>.</span>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($plants->hasPages())
    <div class="card-footer bg-white py-3">{{ $plants->withQueryString()->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
