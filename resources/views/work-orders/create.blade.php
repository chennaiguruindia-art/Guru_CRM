@extends('layouts.app')
@section('title', 'Create Work Order')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-briefcase-fill text-success me-2"></i>New Work Order</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-0"><li class="breadcrumb-item"><a href="{{ route('work-orders.index') }}" class="text-success text-decoration-none">Work Orders</a></li><li class="breadcrumb-item active">Create</li></ol></nav>
    </div>
    <a href="{{ route('work-orders.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<form method="POST" action="{{ route('work-orders.store') }}">
    @csrf
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle text-success me-2"></i>Work Order Details</h6>
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
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Related Project</label>
                            <select name="project_id" class="form-select">
                                <option value="">— None / Standalone —</option>
                                @foreach($projects as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Site Supervisor</label>
                            <select name="supervisor_id" class="form-select">
                                <option value="">— Select Supervisor —</option>
                                @foreach($supervisors as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ old('start_date', date('Y-m-d')) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Target End Date</label>
                            <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Status</label>
                            <select name="status" class="form-select">
                                @foreach(['Issued', 'In Progress', 'Completed', 'On Hold', 'Cancelled'] as $st)
                                    <option value="{{ $st }}">{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Scope of Work</label>
                            <textarea name="scope_of_work" class="form-control" rows="4" placeholder="Detail tasks, instructions, plant installation details, safety notes…"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success py-2 px-4 fw-semibold">
                    <i class="bi bi-check2-circle me-1"></i> Issue Work Order
                </button>
                <a href="{{ route('work-orders.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
    </div>
</form>
@endsection
