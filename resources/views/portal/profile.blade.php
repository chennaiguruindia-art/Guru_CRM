@extends('layouts.portal')
@section('title', 'My Profile & Employment Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-person-vcard text-success me-2"></i>My Profile</h4>
        <div class="text-muted small">View your employment records and manage your editable personal & bank details.</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('portal.family') }}" class="btn btn-outline-info btn-sm">
            <i class="bi bi-people me-1"></i>Parents & Family Details
        </a>
        <a href="{{ route('portal.payslips') }}" class="btn btn-outline-success btn-sm">
            <i class="bi bi-receipt me-1"></i>My Payslips
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Company Employment Profile (Locked / Read-Only) -->
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold text-dark">
                    <i class="bi bi-building-lock text-secondary me-2"></i>Official Employment Records
                </h6>
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle small">
                    <i class="bi bi-lock-fill me-1"></i>Company Controlled
                </span>
            </div>

            <div class="card-body p-4">
                <div class="text-center mb-4 pb-3 border-bottom">
                    <div class="portal-avatar mx-auto mb-2" style="width: 64px; height: 64px; font-size: 1.6rem;">
                        {{ strtoupper(substr($employee ? $employee->name : $user->name, 0, 1)) }}
                    </div>
                    <h5 class="fw-bold text-dark mb-1">{{ $employee ? $employee->name : $user->name }}</h5>
                    <div class="text-success font-monospace fw-semibold fs-6">
                        {{ $employee ? $employee->employee_code : ($user->username ?? '—') }}
                    </div>
                    <div class="text-muted small mt-1">
                        {{ $employee?->designation ?? 'Employee' }} &middot; {{ $employee?->department ?? 'General' }}
                    </div>
                    <span class="badge {{ ($employee?->status ?? 'active') === 'active' ? 'bg-success' : 'bg-secondary' }} mt-2 px-3 py-1">
                        {{ ucfirst($employee?->status ?? 'Active') }}
                    </span>
                </div>

                <div class="row g-3 small">
                    <div class="col-sm-6">
                        <label class="text-muted d-block">Branch Office</label>
                        <span class="fw-semibold text-dark">{{ $employee?->branch?->name ?? 'Headquarters' }}</span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted d-block">Employment Type</label>
                        <span class="fw-semibold text-dark">{{ $employee?->employee_type ?? 'Full-Time' }}</span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted d-block">Joining Date</label>
                        <span class="fw-semibold text-dark">{{ $employee?->joining_date?->format('d M Y') ?? '—' }}</span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted d-block">Monthly CTC Salary</label>
                        <span class="fw-semibold text-success fs-6">₹{{ number_format($employee?->salary ?? 0, 2) }}</span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted d-block">Portal Login ID</label>
                        <span class="fw-semibold font-monospace text-dark">{{ $user->username ?? $user->phone }}</span>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted d-block">System User ID</label>
                        <span class="fw-semibold font-monospace text-muted">USR-{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</span>
                    </div>
                </div>

                <div class="alert alert-light border mt-4 mb-0 small text-muted">
                    <i class="bi bi-info-circle text-primary me-1"></i>
                    Designation, branch, salary, and employee code are locked. For changes to official records, please contact your HR or Branch Administrator.
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Editable Personal & Banking Details Form -->
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold text-dark">
                    <i class="bi bi-pencil-square text-success me-2"></i>Edit My Personal & Banking Details
                </h6>
                <span class="badge bg-success-subtle text-success border border-success-subtle small">
                    <i class="bi bi-check2-circle me-1"></i>Self-Editable
                </span>
            </div>

            <div class="card-body p-4">
                <form method="POST" action="{{ route('portal.profile.update') }}">
                    @csrf

                    <h6 class="fw-bold text-success border-bottom pb-2 mb-3">
                        <i class="bi bi-person me-1"></i>Contact & Personal Info
                    </h6>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Phone / Mobile Number <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $employee?->phone ?? $user->phone) }}" required placeholder="e.g. 9876543210">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email Address</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $employee?->email ?? $user->email) }}" placeholder="e.g. name@example.com">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Date of Birth (DOB)</label>
                            <input type="date" name="dob" class="form-control @error('dob') is-invalid @enderror" value="{{ old('dob', $employee?->dob?->format('Y-m-d')) }}">
                            @error('dob') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Blood Group</label>
                            <select name="blood_group" class="form-select @error('blood_group') is-invalid @enderror">
                                <option value="">Select Blood Group</option>
                                @foreach(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'] as $bg)
                                    <option value="{{ $bg }}" {{ old('blood_group', $employee?->blood_group) === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                                @endforeach
                            </select>
                            @error('blood_group') <div class="invalid-feedback">{{ $message }}</div> @enderror
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

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Current Residential Address</label>
                            <textarea name="address" rows="2" class="form-control @error('address') is-invalid @enderror" placeholder="Door No, Street, Area, City, Pincode">{{ old('address', $employee?->address) }}</textarea>
                            @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <h6 class="fw-bold text-success border-bottom pb-2 mb-3">
                        <i class="bi bi-telephone-plus me-1"></i>Emergency Contact
                    </h6>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Emergency Contact Phone Number</label>
                            <input type="text" name="emergency_contact" class="form-control @error('emergency_contact') is-invalid @enderror" value="{{ old('emergency_contact', $employee?->emergency_contact) }}" placeholder="e.g. 9876543210">
                            @error('emergency_contact') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Relationship</label>
                            <input type="text" name="emergency_contact_relation" class="form-control @error('emergency_contact_relation') is-invalid @enderror" value="{{ old('emergency_contact_relation', $employee?->emergency_contact_relation) }}" placeholder="e.g. Father, Mother, Spouse, Brother">
                            @error('emergency_contact_relation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <h6 class="fw-bold text-success border-bottom pb-2 mb-3">
                        <i class="bi bi-bank me-1"></i>Banking & Salary Credit Details
                    </h6>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Bank Name</label>
                            <input type="text" name="bank_name" class="form-control @error('bank_name') is-invalid @enderror" value="{{ old('bank_name', $employee?->bank_name) }}" placeholder="e.g. State Bank of India, HDFC">
                            @error('bank_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Account Number</label>
                            <input type="text" name="bank_account_number" class="form-control @error('bank_account_number') is-invalid @enderror" value="{{ old('bank_account_number', $employee?->bank_account_number) }}" placeholder="e.g. 123456789012">
                            @error('bank_account_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">IFSC Code</label>
                            <input type="text" name="bank_ifsc" class="form-control text-uppercase @error('bank_ifsc') is-invalid @enderror" value="{{ old('bank_ifsc', $employee?->bank_ifsc) }}" placeholder="e.g. SBIN0001234">
                            @error('bank_ifsc') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">PAN Number</label>
                            <input type="text" name="pan_number" class="form-control text-uppercase @error('pan_number') is-invalid @enderror" value="{{ old('pan_number', $employee?->pan_number) }}" placeholder="e.g. ABCDE1234F">
                            @error('pan_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">PF / UAN Number</label>
                            <input type="text" name="uan_number" class="form-control @error('uan_number') is-invalid @enderror" value="{{ old('uan_number', $employee?->uan_number) }}" placeholder="Optional">
                            @error('uan_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                        <button type="submit" class="btn btn-success px-4 py-2 fw-semibold shadow-sm">
                            <i class="bi bi-save me-1"></i>Save Personal Details
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
