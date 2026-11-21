@extends('layouts.app')
@section('title', 'Add New Employee')

@section('content')
<div class="container-fluid px-0" style="max-width: 1050px;">

    {{-- Top Navigation & Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center justify-content-center rounded-3 shadow-xs" 
                 style="width: 48px; height: 48px; background: #ecfdf5; color: #059669;">
                <i class="bi bi-person-plus-fill fs-3"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-0 text-dark" style="letter-spacing: -0.3px;">Add New Employee</h4>
                <p class="text-muted small mb-0">Fill in the employee details to register staff and enable punch terminal</p>
            </div>
        </div>
        <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
            <i class="bi bi-arrow-left me-1"></i>Back to Roster
        </a>
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

    <form method="POST" action="{{ route('employees.store') }}" id="employeeForm">
        @csrf
        <input type="hidden" name="create_login" value="1">

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
                        Employee ID / Code
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-upc-scan"></i></span>
                        <input type="text" 
                               name="employee_code" 
                               class="form-control border-start-0 @error('employee_code') is-invalid @enderror" 
                               placeholder="e.g. ch/m/228 (leave blank for auto EMP-xxxx)" 
                               value="{{ old('employee_code') }}">
                    </div>
                    <div class="form-text small text-muted">Custom ID like <code>ch/m/228</code>, or leave blank to auto-generate.</div>
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
                               placeholder="e.g. Suresh Kumar" 
                               value="{{ old('name') }}" 
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
                               placeholder="10-digit mobile number" 
                               value="{{ old('phone') }}" 
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
                               placeholder="employee@company.com" 
                               value="{{ old('email') }}">
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
                               value="{{ old('dob') }}">
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
                                <option value="{{ $bg }}" {{ old('blood_group') === $bg ? 'selected' : '' }}>{{ $bg }}</option>
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
                               placeholder="e.g. Sales Executive, Software Developer, Horticulturist" 
                               value="{{ old('designation') }}" 
                               required>
                    </div>
                    <div class="form-text small text-muted">Sales designations automatically get "Move to CRM" capability.</div>
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
                                <option value="{{ $et }}" {{ old('employee_type', 'Full-Time') === $et ? 'selected' : '' }}>{{ $et }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('employee_type') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                {{-- Monthly Salary --}}
                <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold text-secondary">
                        Monthly Gross Salary / CTC (₹)
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted fw-bold">₹</span>
                        <input type="number" 
                               step="0.01" 
                               name="monthly_salary" 
                               id="createGrossSalaryInput"
                               class="form-control border-start-0 @error('salary') is-invalid @enderror" 
                               placeholder="e.g. 25000" 
                               value="{{ old('monthly_salary', old('salary', '')) }}">
                    </div>
                    @error('salary') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                {{-- Custom Salary Split Section (Form XXVIII Format: Basic, DA, Bonus, Leave Encashment, Other Allowance) --}}
                <div class="col-12">
                    <div class="p-3 bg-light rounded-3 border">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark small"><i class="bi bi-pie-chart-fill text-success me-1"></i>Salary Component Split (Corporate Form XXVIII Format)</h6>
                                <div class="text-muted" style="font-size: 0.75rem;">Earnings breakdown: Basic, DA, Bonus, Leave Encashment, and Other Allowance.</div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="createSetBasicEqualToGross()">
                                    <i class="bi bi-arrow-repeat me-1"></i>Set Basic = Gross
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="createAutoSplitSalary()">
                                    <i class="bi bi-magic me-1"></i>Auto Calculate (50% Basic / 30% DA / 10% Bonus / 10% Leave)
                                </button>
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col-12 col-sm-6 col-md-3">
                                <label class="form-label text-muted fw-semibold" style="font-size: 0.75rem;">Basic (₹) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="basic_salary" id="createBasicInput" class="form-control form-control-sm" value="{{ old('basic_salary') }}" placeholder="e.g. 10000" oninput="updateCreateSalaryMatchingBadge()">
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <label class="form-label text-muted fw-semibold" style="font-size: 0.75rem;">DA - Dearness Allowance (₹)</label>
                                <input type="number" step="0.01" name="da" id="createDaInput" class="form-control form-control-sm" value="{{ old('da') }}" placeholder="e.g. 6000" oninput="updateCreateSalaryMatchingBadge()">
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <label class="form-label text-muted fw-semibold" style="font-size: 0.75rem;">Bonus (₹)</label>
                                <input type="number" step="0.01" name="bonus" id="createBonusInput" class="form-control form-control-sm" value="{{ old('bonus') }}" placeholder="e.g. 2000" oninput="updateCreateSalaryMatchingBadge()">
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <label class="form-label text-muted fw-semibold" style="font-size: 0.75rem;">Leave Encashment (₹)</label>
                                <input type="number" step="0.01" name="leave_encashment" id="createLeaveInput" class="form-control form-control-sm" value="{{ old('leave_encashment') }}" placeholder="e.g. 1000" oninput="updateCreateSalaryMatchingBadge()">
                            </div>
                            <div class="col-12 col-sm-6 col-md-3 mt-2">
                                <label class="form-label text-muted fw-semibold" style="font-size: 0.75rem;">Other Allowance (₹)</label>
                                <input type="number" step="0.01" name="other_allowance" id="createOtherInput" class="form-control form-control-sm" value="{{ old('other_allowance') }}" placeholder="e.g. 1000" oninput="updateCreateSalaryMatchingBadge()">
                            </div>
                            <div class="col-12 col-sm-6 col-md-3 mt-2">
                                <label class="form-label text-muted fw-semibold" style="font-size: 0.75rem;">Custom PF Deduction (₹)</label>
                                <input type="number" step="0.01" name="custom_pf_deduction" class="form-control form-control-sm" value="{{ old('custom_pf_deduction') }}" placeholder="Optional (default: 12% Basic)">
                            </div>
                            <div class="col-12 col-sm-6 col-md-3 mt-2">
                                <label class="form-label text-muted fw-semibold" style="font-size: 0.75rem;">Custom ESI Deduction (₹)</label>
                                <input type="number" step="0.01" name="custom_esi_deduction" class="form-control form-control-sm" value="{{ old('custom_esi_deduction') }}" placeholder="Optional (default: 0.75%)">
                            </div>
                            <div class="col-12 col-sm-6 col-md-3 mt-2">
                                <label class="form-label text-muted fw-semibold" style="font-size: 0.75rem;">Custom PT Deduction (₹)</label>
                                <input type="number" step="0.01" name="custom_pt_deduction" class="form-control form-control-sm" value="{{ old('custom_pt_deduction') }}" placeholder="Optional (default: ₹208/₹200)">
                            </div>
                        </div>

                        {{-- Live Matching Status Bar --}}
                        <div class="mt-3 p-2 px-3 rounded-2 border d-flex justify-content-between align-items-center flex-wrap gap-2 small bg-white" id="createSalaryMatchBar">
                            <div>
                                <span class="text-muted">Components Sum:</span>
                                <strong class="text-dark ms-1" id="createComponentsSumText">₹0.00</strong>
                            </div>
                            <div id="createMatchingStatusBadge">
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-check-circle-fill me-1"></i>Matches Gross Salary
                                </span>
                            </div>
                        </div>
                    </div>
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
                               value="{{ old('date_of_joining', old('joining_date', date('Y-m-d'))) }}">
                    </div>
                    @error('joining_date') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                {{-- Branch (Scoped automatically for branch admin, or selectable for super admin) --}}
                @if(auth()->check() && ! auth()->user()->canSeeAllBranches() && $branches->isNotEmpty())
                    <input type="hidden" name="branch_id" value="{{ $branches->first()->id }}">
                @else
                    <div class="col-12">
                        <label class="form-label small fw-semibold text-secondary">
                            Branch Location <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-geo-alt"></i></span>
                            <select name="branch_id" class="form-select border-start-0" required>
                                <option value="">— Select Branch —</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" {{ (int) old('branch_id') === $b->id ? 'selected' : '' }}>
                                        {{ $b->code }} · {{ $b->name }}@if($b->location) ({{ $b->location }})@endif
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
                               value="{{ old('emergency_contact') }}">
                    </div>
                    @error('emergency_contact') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                {{-- Current Branch Info Badge --}}
                <div class="col-12 col-md-6 d-flex align-items-center">
                    <div class="p-3 bg-light rounded-3 border w-100 d-flex align-items-center gap-3">
                        <i class="bi bi-info-circle-fill text-primary fs-4"></i>
                        <div class="small text-muted">
                            <span class="fw-semibold text-dark">Automated Login Setup:</span> 
                            An Employee Code (e.g. <code class="text-success fw-bold">EMP-0005</code>) will be created automatically for immediate punch-in.
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
                                  rows="3" 
                                  placeholder="Door no, Street name, Area, City, Pincode">{{ old('address') }}</textarea>
                    </div>
                    @error('address') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        {{-- Section 4: Banking, PF & Statutory Details --}}
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
            <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                <div class="rounded-circle bg-success-subtle text-success p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                    <i class="bi bi-bank small"></i>
                </div>
                <h6 class="fw-bold mb-0 text-dark">Banking, PF & Statutory Details</h6>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold text-secondary">
                        PF Number (Provident Fund Account)
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-shield-check"></i></span>
                        <input type="text" 
                               name="pf_number" 
                               class="form-control border-start-0 @error('pf_number') is-invalid @enderror" 
                               placeholder="e.g. MH/BAN/12345/678" 
                               value="{{ old('pf_number') }}">
                    </div>
                    @error('pf_number') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold text-secondary">
                        UAN Number (Universal Account Number)
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-hash"></i></span>
                        <input type="text" 
                               name="uan_number" 
                               class="form-control border-start-0 @error('uan_number') is-invalid @enderror" 
                               placeholder="e.g. 101234567890" 
                               value="{{ old('uan_number') }}">
                    </div>
                    @error('uan_number') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold text-secondary">
                        PAN Card Number
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-credit-card-2-front"></i></span>
                        <input type="text" 
                               name="pan_number" 
                               class="form-control border-start-0 text-uppercase @error('pan_number') is-invalid @enderror" 
                               placeholder="e.g. ABCDE1234F" 
                               value="{{ old('pan_number') }}">
                    </div>
                    @error('pan_number') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold text-secondary">
                        Bank Name
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-building"></i></span>
                        <input type="text" 
                               name="bank_name" 
                               class="form-control border-start-0 @error('bank_name') is-invalid @enderror" 
                               placeholder="e.g. State Bank of India, HDFC" 
                               value="{{ old('bank_name') }}">
                    </div>
                    @error('bank_name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold text-secondary">
                        Bank Account Number
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-wallet2"></i></span>
                        <input type="text" 
                               name="bank_account_number" 
                               class="form-control border-start-0 @error('bank_account_number') is-invalid @enderror" 
                               placeholder="e.g. 123456789012" 
                               value="{{ old('bank_account_number') }}">
                    </div>
                    @error('bank_account_number') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold text-secondary">
                        Bank IFSC Code
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-upc"></i></span>
                        <input type="text" 
                               name="bank_ifsc" 
                               class="form-control border-start-0 text-uppercase @error('bank_ifsc') is-invalid @enderror" 
                               placeholder="e.g. SBIN0001234" 
                               value="{{ old('bank_ifsc') }}">
                    </div>
                    @error('bank_ifsc') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        {{-- Form Action Buttons --}}
        <div class="d-flex align-items-center justify-content-between p-3 bg-white rounded-4 border shadow-sm flex-wrap gap-3">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-medium">
                    <i class="bi bi-geo-alt-fill me-1"></i>{{ auth()->user()->branchLabel() }}
                </span>
                <span class="text-muted small">Fields marked with <span class="text-danger">*</span> are required</span>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold shadow-xs">
                    <i class="bi bi-check2-circle me-1"></i> Register Employee
                </button>
            </div>
        </div>

    </form>

</div>
@endsection

@push('scripts')
<script>
    function createSetBasicEqualToGross() {
        const gross = parseFloat(document.getElementById('createGrossSalaryInput').value) || 0;
        document.getElementById('createBasicInput').value = gross > 0 ? gross.toFixed(2) : '';
        document.getElementById('createDaInput').value = '0.00';
        document.getElementById('createBonusInput').value = '0.00';
        document.getElementById('createLeaveInput').value = '0.00';
        document.getElementById('createOtherInput').value = '0.00';
        updateCreateSalaryMatchingBadge();
    }

    function createAutoSplitSalary() {
        const gross = parseFloat(document.getElementById('createGrossSalaryInput').value) || 0;
        if (gross <= 0) {
            alert('Please enter a valid Monthly Gross Salary first.');
            return;
        }

        const basic = Math.round(gross * 0.50);
        const da = Math.round(gross * 0.30);
        const bonus = Math.round(gross * 0.10);
        const leave = Math.round(gross - (basic + da + bonus));

        document.getElementById('createBasicInput').value = basic.toFixed(2);
        document.getElementById('createDaInput').value = da.toFixed(2);
        document.getElementById('createBonusInput').value = bonus.toFixed(2);
        document.getElementById('createLeaveInput').value = leave.toFixed(2);
        document.getElementById('createOtherInput').value = '0.00';
        updateCreateSalaryMatchingBadge();
    }

    function updateCreateSalaryMatchingBadge() {
        const gross = parseFloat(document.getElementById('createGrossSalaryInput').value) || 0;
        const basic = parseFloat(document.getElementById('createBasicInput').value) || 0;
        const da = parseFloat(document.getElementById('createDaInput').value) || 0;
        const bonus = parseFloat(document.getElementById('createBonusInput').value) || 0;
        const leave = parseFloat(document.getElementById('createLeaveInput').value) || 0;
        const other = parseFloat(document.getElementById('createOtherInput').value) || 0;

        const sum = basic + da + bonus + leave + other;
        document.getElementById('createComponentsSumText').textContent = '₹' + sum.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        const badge = document.getElementById('createMatchingStatusBadge');
        const diff = Math.abs(gross - sum);

        if (diff < 0.01) {
            badge.innerHTML = `<span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-check-circle-fill me-1"></i>Matches Gross Salary (₹${gross.toFixed(2)})</span>`;
        } else {
            const remaining = gross - sum;
            badge.innerHTML = `<span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="bi bi-exclamation-triangle-fill me-1"></i>Mismatch! Difference: ₹${remaining.toFixed(2)}</span>`;
        }
    }

    // Auto sync on gross change if components were previously equal or empty
    document.getElementById('createGrossSalaryInput').addEventListener('input', function() {
        const gross = parseFloat(this.value) || 0;
        const basic = parseFloat(document.getElementById('createBasicInput').value) || 0;
        const da = parseFloat(document.getElementById('createDaInput').value) || 0;
        const bonus = parseFloat(document.getElementById('createBonusInput').value) || 0;
        const leave = parseFloat(document.getElementById('createLeaveInput').value) || 0;
        const other = parseFloat(document.getElementById('createOtherInput').value) || 0;

        if (da === 0 && bonus === 0 && leave === 0 && other === 0) {
            document.getElementById('createBasicInput').value = gross > 0 ? gross.toFixed(2) : '';
        }
        updateCreateSalaryMatchingBadge();
    });

    document.addEventListener('DOMContentLoaded', updateCreateSalaryMatchingBadge);
</script>
@endpush