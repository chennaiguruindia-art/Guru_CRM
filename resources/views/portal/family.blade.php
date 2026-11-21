@extends('layouts.portal')
@section('title', 'Parents & Family Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-people text-info me-2"></i>Parents & Family Records</h4>
        <div class="text-muted small">Maintain your parents' information, emergency contacts, and family background.</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('portal.profile') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-person me-1"></i>My Profile
        </a>
        <a href="{{ route('portal.dashboard') }}" class="btn btn-outline-success btn-sm">
            <i class="bi bi-speedometer2 me-1"></i>Dashboard
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Current Family & Parent Details Overview (See their data) -->
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold text-dark">
                    <i class="bi bi-journal-text text-primary me-2"></i>Current Family Records
                </h6>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle small">
                    <i class="bi bi-eye me-1"></i>Live Record
                </span>
            </div>

            <div class="card-body p-4">
                <!-- Father's Record Card -->
                <div class="p-3 bg-light rounded-3 mb-3 border">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-person-standing text-primary fs-4"></i>
                        <div>
                            <div class="fw-bold text-dark">Father's Details</div>
                            <small class="text-muted">Primary Parent</small>
                        </div>
                    </div>
                    <div class="row g-2 small mt-1">
                        <div class="col-6">
                            <span class="text-muted d-block">Full Name</span>
                            <span class="fw-semibold text-dark">{{ $employee?->father_name ?: '— Not Provided' }}</span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block">Phone Number</span>
                            <span class="fw-semibold text-dark">{{ $employee?->father_phone ?: '—' }}</span>
                        </div>
                        <div class="col-12">
                            <span class="text-muted d-block">Occupation</span>
                            <span>{{ $employee?->father_occupation ?: '—' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Mother's Record Card -->
                <div class="p-3 bg-light rounded-3 mb-3 border">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-person-standing-dress text-danger fs-4"></i>
                        <div>
                            <div class="fw-bold text-dark">Mother's Details</div>
                            <small class="text-muted">Primary Parent</small>
                        </div>
                    </div>
                    <div class="row g-2 small mt-1">
                        <div class="col-6">
                            <span class="text-muted d-block">Full Name</span>
                            <span class="fw-semibold text-dark">{{ $employee?->mother_name ?: '— Not Provided' }}</span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block">Phone Number</span>
                            <span class="fw-semibold text-dark">{{ $employee?->mother_phone ?: '—' }}</span>
                        </div>
                        <div class="col-12">
                            <span class="text-muted d-block">Occupation</span>
                            <span>{{ $employee?->mother_occupation ?: '—' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Emergency & Address Info -->
                <div class="p-3 bg-light rounded-3 border">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-shield-exclamation text-warning fs-4"></i>
                        <div>
                            <div class="fw-bold text-dark">Emergency Contact</div>
                            <small class="text-muted">Designated Contact Person</small>
                        </div>
                    </div>
                    <div class="row g-2 small mt-1">
                        <div class="col-6">
                            <span class="text-muted d-block">Emergency Number</span>
                            <span class="fw-semibold text-dark">{{ $employee?->emergency_contact ?: '—' }}</span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block">Relationship</span>
                            <span class="fw-semibold text-dark">{{ $employee?->emergency_contact_relation ?: '—' }}</span>
                        </div>
                        <div class="col-12 mt-2">
                            <span class="text-muted d-block">Parents / Permanent Address</span>
                            <span class="text-dark">{{ $employee?->parent_address ?: '— Same as residential or not set' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Add / Update Parents & Family Details Form -->
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold text-dark">
                    <i class="bi bi-pencil-square text-info me-2"></i>Add or Update Parents Details
                </h6>
                <span class="badge bg-info-subtle text-info border border-info-subtle small">
                    Self-Service Form
                </span>
            </div>

            <div class="card-body p-4">
                <form method="POST" action="{{ route('portal.family.update') }}">
                    @csrf

                    <!-- Father's Info Section -->
                    <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                        <i class="bi bi-person-standing me-1"></i>Father's Information
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Father's Full Name</label>
                            <input type="text" name="father_name" class="form-control @error('father_name') is-invalid @enderror" value="{{ old('father_name', $employee?->father_name) }}" placeholder="e.g. Ramesh Kumar">
                            @error('father_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Father's Phone Number</label>
                            <input type="text" name="father_phone" class="form-control @error('father_phone') is-invalid @enderror" value="{{ old('father_phone', $employee?->father_phone) }}" placeholder="e.g. 9876543210">
                            @error('father_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Father's Occupation</label>
                            <input type="text" name="father_occupation" class="form-control @error('father_occupation') is-invalid @enderror" value="{{ old('father_occupation', $employee?->father_occupation) }}" placeholder="e.g. Business, Agriculture, Private Service, Retired">
                            @error('father_occupation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Mother's Info Section -->
                    <h6 class="fw-bold text-danger border-bottom pb-2 mb-3">
                        <i class="bi bi-person-standing-dress me-1"></i>Mother's Information
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Mother's Full Name</label>
                            <input type="text" name="mother_name" class="form-control @error('mother_name') is-invalid @enderror" value="{{ old('mother_name', $employee?->mother_name) }}" placeholder="e.g. Lakshmi">
                            @error('mother_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Mother's Phone Number</label>
                            <input type="text" name="mother_phone" class="form-control @error('mother_phone') is-invalid @enderror" value="{{ old('mother_phone', $employee?->mother_phone) }}" placeholder="e.g. 9876543210">
                            @error('mother_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Mother's Occupation</label>
                            <input type="text" name="mother_occupation" class="form-control @error('mother_occupation') is-invalid @enderror" value="{{ old('mother_occupation', $employee?->mother_occupation) }}" placeholder="e.g. Homemaker, Teacher, Healthcare, Business">
                            @error('mother_occupation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Permanent / Parents Address & Emergency Contact -->
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                        <i class="bi bi-geo-alt me-1"></i>Permanent Address & Emergency Details
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Parents / Permanent Home Address</label>
                            <textarea name="parent_address" rows="2" class="form-control @error('parent_address') is-invalid @enderror" placeholder="Complete native / permanent address with Pincode">{{ old('parent_address', $employee?->parent_address) }}</textarea>
                            @error('parent_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Emergency Contact Phone Number</label>
                            <input type="text" name="emergency_contact" class="form-control @error('emergency_contact') is-invalid @enderror" value="{{ old('emergency_contact', $employee?->emergency_contact) }}" placeholder="e.g. 9876543210">
                            @error('emergency_contact') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Relationship</label>
                            <input type="text" name="emergency_contact_relation" class="form-control @error('emergency_contact_relation') is-invalid @enderror" value="{{ old('emergency_contact_relation', $employee?->emergency_contact_relation) }}" placeholder="e.g. Father, Mother, Brother, Spouse">
                            @error('emergency_contact_relation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Marital Status</label>
                            <select name="marital_status" class="form-select @error('marital_status') is-invalid @enderror">
                                <option value="Single" {{ old('marital_status', $employee?->marital_status ?? 'Single') === 'Single' ? 'selected' : '' }}>Single</option>
                                <option value="Married" {{ old('marital_status', $employee?->marital_status) === 'Married' ? 'selected' : '' }}>Married</option>
                                <option value="Other" {{ old('marital_status', $employee?->marital_status) === 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('marital_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Spouse Name (if married)</label>
                            <input type="text" name="spouse_name" class="form-control @error('spouse_name') is-invalid @enderror" value="{{ old('spouse_name', $employee?->spouse_name) }}" placeholder="Optional">
                            @error('spouse_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                        <button type="submit" class="btn btn-info text-white px-4 py-2 fw-semibold shadow-sm">
                            <i class="bi bi-check-circle me-1"></i>Save Family Details
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
