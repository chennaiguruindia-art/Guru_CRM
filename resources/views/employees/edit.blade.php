@extends('layouts.app')
@section('title', 'Edit Employee — ' . $employee->name)

@section('content')
<div class="container-fluid px-0" style="max-width: 1050px;">

    {{-- Top Navigation & Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center justify-content-center rounded-3 shadow-xs" 
                 style="width: 48px; height: 48px; background: #ecfdf5; color: #059669;">
                <i class="bi bi-pencil-square fs-3"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-0 text-dark" style="letter-spacing: -0.3px;">Edit Employee — {{ $employee->name }}</h4>
                <p class="text-muted small mb-0">{{ $employee->employee_code }} • Update employee profile and payroll details</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('employees.show', $employee) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
                <i class="bi bi-eye me-1"></i>View Profile
            </a>
            <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
                <i class="bi bi-arrow-left me-1"></i>Back to Roster
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4 shadow-sm" role="alert">
            <div class="d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <strong class="fw-semibold">Please correct the following errors:</strong>
            </div>
            <ul class="mb-0 small ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('employees.update', $employee) }}">
        @csrf
        @method('PUT')

        {{-- Section 1: Basic & Contact Information --}}
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
            <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                    <i class="bi bi-person-lines-fill small"></i>
                </div>
                <h6 class="fw-bold mb-0 text-dark">Personal & Contact Details</h6>
            </div>

            <div class="row g-3">
                {{-- Employee ID / Code --}}
                <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold text-secondary">
                        Employee ID / Code <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-upc-scan"></i></span>
                        <input type="text" 
                               name="employee_code" 
                               class="form-control border-start-0 @error('employee_code') is-invalid @enderror" 
                               placeholder="e.g. ch/m/228" 
                               value="{{ old('employee_code', $employee->employee_code) }}" 
                               required>
                    </div>
                    <div class="form-text small text-muted">Unique Employee ID (e.g. <code>ch/m/228</code> or <code>EMP-0001</code>).</div>
                    @error('employee_code') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                {{-- Full Name --}}
                <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold text-secondary">
                        Full Name <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person"></i></span>
                        <input type="text" 
                               name="name" 
                               class="form-control border-start-0 @error('name') is-invalid @enderror" 
                               value="{{ old('name', $employee->name) }}" 
                               required>
                    </div>
                    @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                {{-- Phone Number --}}
                <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold text-secondary">
                        Phone Number <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-phone"></i></span>
                        <input type="tel" 
                               name="phone" 
                               class="form-control border-start-0 @error('phone') is-invalid @enderror" 
                               value="{{ old('phone', $employee->phone) }}" 
                               required>
                    </div>
                    @error('phone') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                {{-- Email Address --}}
                <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold text-secondary">
                        Email Address
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                        <input type="email" 
                               name="email" 
                               class="form-control border-start-0 @error('email') is-invalid @enderror" 
                               value="{{ old('email', $employee->email) }}">
                    </div>
                    @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                {{-- Date of Birth (DOB) --}}
                <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold text-secondary">
                        Date of Birth (DOB)
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-calendar-heart"></i></span>
                        <input type="date" 
                               name="dob" 
                               class="form-control border-start-0 @error('dob') is-invalid @enderror" 
                               value="{{ old('dob', $employee->dob?->toDateString()) }}">
                    </div>
                    @error('dob') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                {{-- Blood Group --}}
                <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold text-secondary">
                        Blood Group
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-droplet-fill text-danger"></i></span>
                        <select name="blood_group" class="form-select border-start-0 @error('blood_group') is-invalid @enderror">
                            <option value="">Select Blood Group</option>
                            @foreach(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'] as $bg)
                                <option value="{{ $bg }}" {{ old('blood_group', $employee->blood_group) === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('blood_group') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        {{-- Section 2: Employment & Compensation --}}
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
            <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                <div class="rounded-circle bg-success-subtle text-success p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                    <i class="bi bi-briefcase-fill small"></i>
                </div>
                <h6 class="fw-bold mb-0 text-dark">Job & Compensation</h6>
            </div>

            <div class="row g-3">
                {{-- Designation --}}
                <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold text-secondary">
                        Designation / Role <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person-badge"></i></span>
                        <input type="text" 
                               name="designation" 
                               class="form-control border-start-0 @error('designation') is-invalid @enderror" 
                               value="{{ old('designation', $employee->designation) }}" 
                               required>
                    </div>
                    @error('designation') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                {{-- Employment Type --}}
                <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold text-secondary">
                        Employment Type <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person-workspace"></i></span>
                        <select name="employee_type" class="form-select border-start-0 @error('employee_type') is-invalid @enderror" required>
                            @foreach(['Full-Time', 'Contract', 'Daily Wage', 'Part-Time', 'Intern'] as $et)
                                <option value="{{ $et }}" {{ old('employee_type', $employee->employee_type) === $et ? 'selected' : '' }}>{{ $et }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('employee_type') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                {{-- Monthly Salary --}}
                <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold text-secondary">
                        Monthly Salary (₹)
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted fw-bold">₹</span>
                        <input type="number" 
                               step="0.01" 
                               name="monthly_salary" 
                               class="form-control border-start-0 @error('salary') is-invalid @enderror" 
                               value="{{ old('monthly_salary', old('salary', $employee->salary)) }}">
                    </div>
                    @error('salary') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                {{-- Date of Joining --}}
                <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold text-secondary">
                        Date of Joining
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-calendar-check"></i></span>
                        <input type="date" 
                               name="date_of_joining" 
                               class="form-control border-start-0 @error('joining_date') is-invalid @enderror" 
                               value="{{ old('date_of_joining', old('joining_date', $employee->joining_date?->toDateString())) }}">
                    </div>
                    @error('joining_date') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                {{-- Status --}}
                <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold text-secondary">
                        Account Status <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-toggle-on"></i></span>
                        <select name="status" class="form-select border-start-0" required>
                            <option value="active" {{ old('status', $employee->status) === 'active' ? 'selected' : '' }}>Active (Allow Punching)</option>
                            <option value="inactive" {{ old('status', $employee->status) === 'inactive' ? 'selected' : '' }}>Inactive / Suspended</option>
                        </select>
                    </div>
                </div>

                {{-- Branch (Scoped automatically for branch admin, or selectable for super admin) --}}
                @if(auth()->check() && ! auth()->user()->canSeeAllBranches() && $branches->isNotEmpty())
                    <input type="hidden" name="branch_id" value="{{ $branches->first()->id }}">
                @else
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold text-secondary">
                            Branch Location <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-geo-alt"></i></span>
                            <select name="branch_id" class="form-select border-start-0" required>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" {{ (int) old('branch_id', $employee->branch_id) === $b->id ? 'selected' : '' }}>
                                        {{ $b->code }} · {{ $b->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Section 3: Address & Emergency Details --}}
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
            <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                <div class="rounded-circle bg-warning-subtle text-warning-emphasis p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                    <i class="bi bi-shield-plus small"></i>
                </div>
                <h6 class="fw-bold mb-0 text-dark">Address & Emergency Contact</h6>
            </div>

            <div class="row g-3">
                {{-- Emergency Contact --}}
                <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold text-secondary">
                        Emergency Contact (Name / Phone)
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-telephone-plus"></i></span>
                        <input type="text" 
                               name="emergency_contact" 
                               class="form-control border-start-0 @error('emergency_contact') is-invalid @enderror" 
                               placeholder="e.g. Ramesh (Brother) - 9876543210" 
                               value="{{ old('emergency_contact', $employee->emergency_contact) }}">
                    </div>
                    @error('emergency_contact') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                {{-- Employee ID Badge --}}
                <div class="col-12 col-md-6 d-flex align-items-center">
                    <div class="p-3 bg-light rounded-3 border w-100 d-flex align-items-center gap-3">
                        <i class="bi bi-person-badge text-success fs-3"></i>
                        <div>
                            <div class="small text-muted">Employee Code</div>
                            <div class="fw-bold text-success fs-6">{{ $employee->employee_code }}</div>
                        </div>
                    </div>
                </div>

                {{-- Full Address --}}
                <div class="col-12">
                    <label class="form-label small fw-semibold text-secondary">
                        Residential Address
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted align-items-start pt-2"><i class="bi bi-house"></i></span>
                        <textarea name="address" 
                                  class="form-control border-start-0 @error('address') is-invalid @enderror" 
                                  rows="3">{{ old('address', $employee->address) }}</textarea>
                    </div>
                    @error('address') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        {{-- Form Action Buttons --}}
        <div class="d-flex align-items-center justify-content-between p-3 bg-white rounded-4 border shadow-sm flex-wrap gap-3">
            <span class="text-muted small">Fields marked with <span class="text-danger">*</span> are required</span>

            <div class="d-flex gap-2">
                <a href="{{ route('employees.show', $employee) }}" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold shadow-xs">
                    <i class="bi bi-check2-circle me-1"></i> Update Employee
                </button>
            </div>
        </div>

    </form>

</div>
@endsection