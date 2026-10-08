@extends('layouts.app')
@section('title', 'Create AMC Contract')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-shield-check text-success me-2"></i>New AMC Contract</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-0"><li class="breadcrumb-item"><a href="{{ route('amc.index') }}" class="text-success text-decoration-none">AMC Contracts</a></li><li class="breadcrumb-item active">Create</li></ol></nav>
    </div>
    <a href="{{ route('amc.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<form method="POST" action="{{ route('amc.store') }}">
    @csrf
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle text-success me-2"></i>Contract Details</h6>
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
                            <label class="form-label small fw-semibold text-secondary">Contract Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control" value="{{ old('start_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Contract End Date <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" class="form-control" value="{{ old('end_date', date('Y-m-d', strtotime('+1 year'))) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Billing Frequency <span class="text-danger">*</span></label>
                            <select name="billing_frequency" class="form-select" required>
                                @foreach(['Monthly', 'Quarterly', 'Half-Yearly', 'Annually'] as $bf)
                                    <option value="{{ $bf }}">{{ $bf }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Service Visit Frequency <span class="text-danger">*</span></label>
                            <select name="service_frequency" class="form-select" required>
                                @foreach(['Daily', 'Weekly', 'Biweekly', 'Monthly'] as $sf)
                                    <option value="{{ $sf }}">{{ $sf }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Assigned Team Lead</label>
                            <select name="assigned_team_lead_id" class="form-select">
                                <option value="">— Select Team Lead —</option>
                                @foreach($teamLeads as $tl)
                                    <option value="{{ $tl->id }}">{{ $tl->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Contract Value (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="contract_value" class="form-control" placeholder="Annual total value" value="{{ old('contract_value') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Scope of Work</label>
                            <textarea name="scope_of_work" class="form-control" rows="3" placeholder="Includes lawn mowing, trimming, pest inspection, plant replacement policy…"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Notes</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success py-2 px-4 fw-semibold">
                    <i class="bi bi-check2-circle me-1"></i> Create AMC Contract
                </button>
                <a href="{{ route('amc.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
    </div>
</form>
@endsection
