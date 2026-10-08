@extends('layouts.app')
@section('title', 'Log Maintenance Visit')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-tools text-success me-2"></i>Log Maintenance Visit</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-0"><li class="breadcrumb-item"><a href="{{ route('maintenance.index') }}" class="text-success text-decoration-none">Maintenance</a></li><li class="breadcrumb-item active">Log Visit</li></ol></nav>
    </div>
    <a href="{{ route('maintenance.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<form method="POST" action="{{ route('maintenance.store') }}">
    @csrf
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-clipboard-check text-success me-2"></i>Visit Information</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Client <span class="text-danger">*</span></label>
                            <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                                <option value="">— Select Client —</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}" @selected(old('customer_id') == $c->id)>{{ $c->name }} ({{ $c->customer_code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Visit Date <span class="text-danger">*</span></label>
                            <input type="date" name="visit_date" class="form-control" value="{{ old('visit_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Staff / Supervisor</label>
                            <select name="employee_id" class="form-select">
                                <option value="">— Select Staff —</option>
                                @foreach($employees as $e)
                                    <option value="{{ $e->id }}">{{ $e->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Primary Activity <span class="text-danger">*</span></label>
                            <select name="activity_type" class="form-select" required>
                                @foreach(['Watering', 'Pruning', 'Trimming', 'Fertilization', 'Pest Control', 'Weeding', 'Lawn Cutting', 'Soil Treatment', 'Plant Replacement', 'Cleaning', 'Irrigation Maintenance'] as $act)
                                    <option value="{{ $act }}">{{ $act }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Area Details</label>
                            <input type="text" name="area_details" class="form-control" placeholder="e.g. Front lawn, terrace planters, swimming pool perimeter">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Work Description</label>
                            <textarea name="work_description" class="form-control" rows="3" placeholder="Tasks carried out today, chemical doses used, observations…"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Remarks / Client Feedback</label>
                            <textarea name="remarks" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success py-2 px-4 fw-semibold">
                    <i class="bi bi-check2-circle me-1"></i> Save Maintenance Record
                </button>
                <a href="{{ route('maintenance.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
    </div>
</form>
@endsection
