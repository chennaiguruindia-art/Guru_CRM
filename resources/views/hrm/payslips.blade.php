@extends('layouts.app')
@section('title', 'Monthly Payslip Management - Form XXVIII')

@section('content')
<div class="container-fluid px-0">

    {{-- 1. Header Bar --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3 pb-3 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h4 class="fw-bold mb-0 text-dark" style="letter-spacing: -0.4px;">
                    <i class="bi bi-file-earmark-ruled-fill text-success me-2"></i>Monthly Payslip Management
                </h4>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 small fw-semibold">
                    Form XXVIII Statutory
                </span>
            </div>
            <p class="text-muted small mb-0 d-flex align-items-center gap-2 flex-wrap">
                <span><i class="bi bi-building me-1"></i>Guru Integrated Services India Pvt Ltd</span>
                <span>•</span>
                <span>Rule 78(1)(b) of the TamilNadu Contract Labour Rules, 1975</span>
            </p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            @if($selectedEmployee)
                <a href="{{ route('hrm.payslips.print', ['employee' => $selectedEmployee->id, 'month' => $selectedMonth]) }}" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-xs d-inline-flex align-items-center gap-1.5 fw-semibold">
                    <i class="bi bi-printer-fill"></i>
                    <span>Print Form XXVIII</span>
                </a>
                <a href="{{ route('employees.edit', $selectedEmployee->id) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs d-inline-flex align-items-center gap-1.5 fw-medium bg-white">
                    <i class="bi bi-pencil-square"></i>
                    <span>Edit Profile</span>
                </a>
            @endif
            <a href="{{ route('hrm.dashboard') }}" class="btn btn-outline-dark btn-sm rounded-pill px-3 shadow-xs d-inline-flex align-items-center gap-1.5 fw-medium bg-white">
                <i class="bi bi-speedometer2"></i>
                <span>HRM Overview</span>
            </a>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">
            <strong class="d-block mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i>Please correct the following errors:</strong>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- 2. Top Selection Card (Branch -> Employee -> Month) --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3 p-md-4">
            <form method="GET" action="{{ route('hrm.payslips') }}" id="filterForm">
                <div class="row g-3 align-items-end">
                    {{-- Branch Selector --}}
                    @if($canSeeAll && $branches->isNotEmpty())
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-bold text-uppercase mb-1">
                                <i class="bi bi-geo-alt-fill text-danger me-1"></i>Branch / Location
                            </label>
                            <select name="branch_id" class="form-select rounded-3 shadow-xs" onchange="document.getElementById('filterForm').submit()">
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" {{ $selectedBranchId == $b->id ? 'selected' : '' }}>
                                        {{ $b->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="branch_id" value="{{ $selectedBranchId }}">
                    @endif

                    {{-- Employee Selector --}}
                    <div class="{{ ($canSeeAll && $branches->isNotEmpty()) ? 'col-md-5' : 'col-md-7' }}">
                        <label class="form-label text-muted small fw-bold text-uppercase mb-1">
                            <i class="bi bi-person-fill text-primary me-1"></i>Select Employee
                        </label>
                        <select name="employee_id" class="form-select rounded-3 shadow-xs" onchange="document.getElementById('filterForm').submit()">
                            @forelse($employees as $emp)
                                <option value="{{ $emp->id }}" {{ $selectedEmployeeId == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->name }} ({{ $emp->employee_code ?: 'EMP-'.$emp->id }}) — {{ $emp->designation ?: 'Staff' }}
                                </option>
                            @empty
                                <option value="">No active employees found in this branch</option>
                            @endforelse
                        </select>
                    </div>

                    {{-- Pay Month Selector --}}
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold text-uppercase mb-1">
                            <i class="bi bi-calendar-month text-success me-1"></i>Pay Period (Month)
                        </label>
                        <input type="month" name="month" value="{{ $selectedMonth }}" class="form-control rounded-3 shadow-xs" onchange="document.getElementById('filterForm').submit()">
                    </div>

                    {{-- Submit button --}}
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100 rounded-3 shadow-xs fw-semibold" title="Load Details">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($selectedEmployee)
        {{-- 3. Employee Info Banner --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
            <div class="card-body p-3 p-md-4">
                <div class="row g-3 align-items-center">
                    <div class="col-auto">
                        <div class="avatar-lg bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center fw-bold fs-3" style="width: 60px; height: 60px;">
                            {{ strtoupper(substr($selectedEmployee->name, 0, 1)) }}
                        </div>
                    </div>
                    <div class="col">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="fw-bold text-dark mb-0">{{ $selectedEmployee->name }}</h5>
                            <span class="badge bg-secondary-subtle text-dark border rounded-pill px-2.5 py-0.5 small">
                                {{ $selectedEmployee->employee_code ?: 'EMP-'.$selectedEmployee->id }}
                            </span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-0.5 small">
                                {{ $selectedEmployee->designation ?: 'Staff' }}
                            </span>
                            @if($isExisting)
                                <span class="badge bg-success rounded-pill px-2.5 py-0.5 small">
                                    <i class="bi bi-check-circle-fill me-1"></i>Saved for {{ \Carbon\Carbon::parse($selectedMonth.'-01')->format('F Y') }}
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-0.5 small">
                                    <i class="bi bi-lightning-charge-fill me-1"></i>Auto-Calculated Draft
                                </span>
                            @endif
                        </div>
                        <div class="text-muted small mt-1 d-flex flex-wrap gap-3">
                            <span><i class="bi bi-building me-1"></i>{{ $selectedEmployee->branch->name ?? 'N/A' }}</span>
                            <span><i class="bi bi-credit-card me-1"></i>Bank: {{ $selectedEmployee->bank_name ?: 'N/A' }} ({{ $selectedEmployee->bank_account_number ?: 'N/A' }})</span>
                            <span><i class="bi bi-shield-check me-1"></i>UAN: {{ $selectedEmployee->uan_number ?: 'N/A' }}</span>
                            <span><i class="bi bi-file-earmark-text me-1"></i>PF No: {{ $selectedEmployee->pf_number ?: 'N/A' }}</span>
                            <span><i class="bi bi-calendar-check me-1"></i>Joined: {{ $selectedEmployee->joining_date ? \Carbon\Carbon::parse($selectedEmployee->joining_date)->format('d M Y') : 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="col-md-auto text-md-end border-start-md ps-md-4">
                        <div class="text-muted small fw-semibold">Master CTC / Base</div>
                        <div class="fs-4 fw-bold text-dark">₹{{ number_format($selectedEmployee->salary, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. Payslip Edit / Fill Form --}}
        <form method="POST" action="{{ route('hrm.payslips.store') }}" id="payslipForm">
            @csrf
            <input type="hidden" name="employee_id" value="{{ $selectedEmployee->id }}">
            <input type="hidden" name="salary_month" value="{{ $selectedMonth }}">

            <div class="row g-4">
                {{-- Left Column: Attendance & Earnings --}}
                <div class="col-lg-6">
                    {{-- Card: Attendance & Working Days --}}
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-transparent border-0 pt-3 px-4 pb-0">
                            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                <i class="bi bi-calendar3-range-fill text-primary"></i>
                                Attendance & Calendar Days
                            </h6>
                            <p class="text-muted small mb-0">Total calendar days, present days (worked + paid Sundays), and LOP</p>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold text-secondary">Total Calendar Days *</label>
                                    <input type="number" name="total_working_days" id="total_working_days" class="form-control rounded-3" value="{{ old('total_working_days', $payslip->total_working_days ?? 30) }}" min="1" max="31" required onchange="calculatePayroll()">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold text-secondary">Total Days Present *</label>
                                    <input type="number" step="0.5" name="paid_days" id="paid_days" class="form-control rounded-3" value="{{ old('paid_days', $payslip->paid_days ?? 30) }}" min="0" max="31" required onchange="onPresentDaysChange()">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold text-secondary">Loss of Pay (LOP) Days</label>
                                    <input type="number" step="0.5" name="lop_days" id="lop_days" class="form-control rounded-3" value="{{ old('lop_days', $payslip->lop_days ?? 0) }}" min="0" max="31" onchange="onLopDaysChange()">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold text-secondary">LOP Deduction Amount (₹)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted border-end-0">₹</span>
                                        <input type="number" step="0.01" name="lop_deduction" id="lop_deduction" class="form-control rounded-end-3" value="{{ old('lop_deduction', $payslip->lop_deduction ?? 0) }}" min="0" onchange="calculatePayroll()">
                                    </div>
                                    <small class="text-muted" style="font-size: 0.75rem;">Can be auto-calculated or overridden</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Card: Earnings Components --}}
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-transparent border-0 pt-3 px-4 pb-0 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                    <i class="bi bi-wallet2 text-success"></i>
                                    Form XXVIII Earnings Breakdown
                                </h6>
                                <p class="text-muted small mb-0">Statutory earnings: Basic, DA, Bonus, Leave Encashment, Other</p>
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-2.5 py-1 small" onclick="setBasicEqualsMaster()">
                                Basic = Master
                            </button>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold text-secondary">Basic Salary (₹) *</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted border-end-0">₹</span>
                                        <input type="number" step="0.01" name="basic_salary" id="basic_salary" class="form-control rounded-end-3" value="{{ old('basic_salary', $payslip->basic_salary ?? $selectedEmployee->basic_salary ?? $selectedEmployee->salary) }}" min="0" required oninput="calculatePayroll()">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold text-secondary">Dearness Allowance (DA) (₹)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted border-end-0">₹</span>
                                        <input type="number" step="0.01" name="da" id="da" class="form-control rounded-end-3" value="{{ old('da', $payslip->da ?? $selectedEmployee->da ?? 0) }}" min="0" oninput="calculatePayroll()">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold text-secondary">Bonus (₹)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted border-end-0">₹</span>
                                        <input type="number" step="0.01" name="bonus" id="bonus" class="form-control rounded-end-3" value="{{ old('bonus', $payslip->bonus ?? $selectedEmployee->bonus ?? 0) }}" min="0" oninput="calculatePayroll()">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold text-secondary">Leave Encashment (₹)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted border-end-0">₹</span>
                                        <input type="number" step="0.01" name="leave_encashment" id="leave_encashment" class="form-control rounded-end-3" value="{{ old('leave_encashment', $payslip->leave_encashment ?? $selectedEmployee->leave_encashment ?? 0) }}" min="0" oninput="calculatePayroll()">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold text-secondary">Other Allowance (₹)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted border-end-0">₹</span>
                                        <input type="number" step="0.01" name="other_allowance" id="other_allowance" class="form-control rounded-end-3" value="{{ old('other_allowance', $payslip->other_allowance ?? $selectedEmployee->other_allowance ?? 0) }}" min="0" oninput="calculatePayroll()">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small fw-bold text-dark">Total Gross Salary (₹) *</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-success-subtle text-success border-end-0 fw-bold">₹</span>
                                        <input type="number" step="0.01" name="gross_salary" id="gross_salary" class="form-control rounded-end-3 fw-bold bg-light" value="{{ old('gross_salary', $payslip->gross_salary ?? 0) }}" min="0" required readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Deductions & Net Pay Summary --}}
                <div class="col-lg-6">
                    {{-- Card: Deductions --}}
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-transparent border-0 pt-3 px-4 pb-0 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                    <i class="bi bi-dash-circle-fill text-danger"></i>
                                    Form XXVIII Deductions Breakdown
                                </h6>
                                <p class="text-muted small mb-0">PF, ESI, Professional Tax, LOP & Other</p>
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-2.5 py-1 small" onclick="recalcStatutoryDeductions()">
                                Recalc Statutory
                            </button>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold text-secondary">Provident Fund (PF) (₹)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted border-end-0">₹</span>
                                        <input type="number" step="0.01" name="pf_deduction" id="pf_deduction" class="form-control rounded-end-3" value="{{ old('pf_deduction', $payslip->pf_deduction ?? 0) }}" min="0" oninput="calculatePayroll()">
                                    </div>
                                    <small class="text-muted" style="font-size: 0.75rem;">12% Basic (Max ₹1800)</small>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold text-secondary">ESI Deduction (₹)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted border-end-0">₹</span>
                                        <input type="number" step="0.01" name="esi_deduction" id="esi_deduction" class="form-control rounded-end-3" value="{{ old('esi_deduction', $payslip->esi_deduction ?? 0) }}" min="0" oninput="calculatePayroll()">
                                    </div>
                                    <small class="text-muted" style="font-size: 0.75rem;">0.75% of Gross (if Gross ≤ ₹21k)</small>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold text-secondary">Professional Tax (PT) (₹)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted border-end-0">₹</span>
                                        <input type="number" step="0.01" name="professional_tax" id="professional_tax" class="form-control rounded-end-3" value="{{ old('professional_tax', $payslip->professional_tax ?? 200) }}" min="0" oninput="calculatePayroll()">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold text-secondary">Other Deductions (₹)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted border-end-0">₹</span>
                                        <input type="number" step="0.01" name="other_deductions" id="other_deductions" class="form-control rounded-end-3" value="{{ old('other_deductions', $payslip->other_deductions ?? 0) }}" min="0" oninput="calculatePayroll()">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-dark">Total Deductions (₹) *</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-danger-subtle text-danger border-end-0 fw-bold">₹</span>
                                        <input type="number" step="0.01" name="total_deductions" id="total_deductions" class="form-control rounded-end-3 fw-bold bg-light" value="{{ old('total_deductions', $payslip->total_deductions ?? 0) }}" min="0" required readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Card: Net Salary & Payment Details --}}
                    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-light-subtle">
                        <div class="card-body p-4">
                            <div class="p-3 bg-white rounded-3 border mb-3 text-center">
                                <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">Net Salary Payable</div>
                                <div class="fs-2 fw-bolder text-success mt-1" id="displayNetSalary">
                                    ₹{{ number_format($payslip->net_salary ?? 0, 2) }}
                                </div>
                                <input type="hidden" name="net_salary" id="net_salary" value="{{ old('net_salary', $payslip->net_salary ?? 0) }}">
                                <div class="text-muted small mt-1" id="displayNetWords">Form XXVIII Net Pay</div>
                            </div>

                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold text-secondary">Payment Status *</label>
                                    <select name="payment_status" class="form-select rounded-3" required>
                                        <option value="Paid" {{ old('payment_status', $payslip->payment_status ?? 'Paid') == 'Paid' ? 'selected' : '' }}>Paid</option>
                                        <option value="In Progress" {{ old('payment_status', $payslip->payment_status ?? '') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="Processing" {{ old('payment_status', $payslip->payment_status ?? '') == 'Processing' ? 'selected' : '' }}>Processing</option>
                                        <option value="Draft" {{ old('payment_status', $payslip->payment_status ?? '') == 'Draft' ? 'selected' : '' }}>Draft</option>
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold text-secondary">Payment Date</label>
                                    <input type="date" name="payment_date" class="form-control rounded-3" value="{{ old('payment_date', $payslip->payment_date ? \Carbon\Carbon::parse($payslip->payment_date)->format('Y-m-d') : '') }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold text-secondary">Payslip Remarks / Note</label>
                                    <input type="text" name="remarks" class="form-control rounded-3" value="{{ old('remarks', $payslip->remarks ?? '') }}" placeholder="e.g. Regular monthly payroll statement">
                                </div>
                            </div>

                            {{-- Form Actions --}}
                            <div class="d-flex align-items-center justify-content-between mt-4 pt-3 border-top flex-wrap gap-2">
                                <button type="submit" class="btn btn-success rounded-pill px-4 py-2 fw-semibold shadow-xs d-inline-flex align-items-center gap-2">
                                    <i class="bi bi-check2-circle fs-5"></i>
                                    <span>Save Monthly Payslip</span>
                                </button>

                                <div class="d-flex align-items-center gap-2">
                                    <a href="{{ route('hrm.payslips.print', ['employee' => $selectedEmployee->id, 'month' => $selectedMonth]) }}" target="_blank" class="btn btn-outline-primary rounded-pill px-3 py-2 fw-semibold small shadow-xs">
                                        <i class="bi bi-printer me-1"></i>Print Form XXVIII
                                    </a>

                                    @if($isExisting && $payslip->id)
                                        <button type="button" class="btn btn-outline-danger rounded-pill px-3 py-2 small shadow-xs" onclick="confirmResetAuto()">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>Reset to Auto
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        {{-- Hidden Delete Form for Resetting to Auto --}}
        @if($isExisting && $payslip->id)
            <form id="deletePayslipForm" action="{{ route('hrm.payslips.destroy', $payslip->id) }}" method="POST" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
        @endif

    @else
        <div class="card border-0 shadow-sm rounded-4 text-center py-5">
            <div class="card-body">
                <i class="bi bi-people text-muted display-4 mb-3"></i>
                <h5 class="fw-bold text-dark">No Employee Selected</h5>
                <p class="text-muted small">Please select an employee and month from the top filter to generate or customize payslips.</p>
            </div>
        </div>
    @endif

    {{-- 5. Branch Payslips Generated This Month (Overview Table) --}}
    <div class="card border-0 shadow-sm rounded-4 mt-4">
        <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-table text-primary me-2"></i>Saved Payslips for {{ \Carbon\Carbon::parse($selectedMonth.'-01')->format('F Y') }}
                </h5>
                <p class="text-muted small mb-0">Overview of all generated/customized payslips for this period in {{ auth()->user()->branchLabel() }}</p>
            </div>
            <span class="badge bg-light text-dark border px-3 py-1.5 rounded-pill fw-semibold">
                Total: {{ $recentPayslips->count() }} payslips
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                    <thead class="table-light text-muted text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                        <tr>
                            <th class="ps-4">Employee</th>
                            <th>Designation</th>
                            <th>Days (Pres / Cal)</th>
                            <th>Gross (₹)</th>
                            <th>Deductions (₹)</th>
                            <th>Net Salary (₹)</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentPayslips as $rp)
                            <tr class="{{ $selectedEmployeeId == $rp->employee_id ? 'table-active' : '' }}">
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-sm bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.85rem;">
                                            {{ strtoupper(substr($rp->employee->name ?? 'E', 0, 1)) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('hrm.payslips', ['branch_id' => $rp->employee->branch_id ?? '', 'employee_id' => $rp->employee_id, 'month' => $selectedMonth]) }}" class="fw-bold text-dark text-decoration-none">
                                                {{ $rp->employee->name ?? 'N/A' }}
                                            </a>
                                            <div class="text-muted small" style="font-size: 0.75rem;">
                                                {{ $rp->employee->employee_code ?: 'EMP-'.$rp->employee_id }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $rp->employee->designation ?: 'Staff' }}</td>
                                <td>
                                    <span class="fw-semibold text-dark">{{ $rp->paid_days }}</span> / <span class="text-muted">{{ $rp->total_working_days }}</span>
                                    @if($rp->lop_days > 0)
                                        <span class="badge bg-danger-subtle text-danger ms-1" style="font-size: 0.7rem;">-{{ $rp->lop_days }} LOP</span>
                                    @endif
                                </td>
                                <td class="fw-semibold text-dark">₹{{ number_format($rp->gross_salary, 2) }}</td>
                                <td class="text-danger fw-semibold">₹{{ number_format($rp->total_deductions, 2) }}</td>
                                <td>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 fw-bold fs-6">
                                        ₹{{ number_format($rp->net_salary, 2) }}
                                    </span>
                                </td>
                                <td>
                                    @if($rp->payment_status === 'Paid')
                                        <span class="badge bg-success rounded-pill px-2 py-0.5">Paid</span>
                                    @elseif($rp->payment_status === 'In Progress')
                                        <span class="badge bg-primary-subtle text-primary border rounded-pill px-2 py-0.5">In Progress</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2 py-0.5">{{ $rp->payment_status }}</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('hrm.payslips', ['branch_id' => $rp->employee->branch_id ?? '', 'employee_id' => $rp->employee_id, 'month' => $selectedMonth]) }}" class="btn btn-outline-secondary" title="Edit Payslip">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="{{ route('hrm.payslips.print', ['employee' => $rp->employee_id, 'month' => $selectedMonth]) }}" target="_blank" class="btn btn-outline-primary" title="Print Form XXVIII">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    No payslips saved yet for {{ \Carbon\Carbon::parse($selectedMonth.'-01')->format('F Y') }}. Select an employee above to create one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- Dynamic Calculations JavaScript --}}
<script>
const masterSalary = {{ (float) ($selectedEmployee->salary ?? 0) }};

function parseNum(id) {
    const el = document.getElementById(id);
    if (!el) return 0;
    const v = parseFloat(el.value);
    return isNaN(v) ? 0 : v;
}

function setBasicEqualsMaster() {
    if (masterSalary > 0) {
        document.getElementById('basic_salary').value = masterSalary.toFixed(2);
        document.getElementById('da').value = '0.00';
        document.getElementById('bonus').value = '0.00';
        document.getElementById('leave_encashment').value = '0.00';
        document.getElementById('other_allowance').value = '0.00';
        calculatePayroll();
    }
}

function onPresentDaysChange() {
    const totalCal = parseNum('total_working_days');
    const present = parseNum('paid_days');
    const lop = Math.max(0, totalCal - present);
    document.getElementById('lop_days').value = lop.toFixed(1);
    recalcLopAmount();
    calculatePayroll();
}

function onLopDaysChange() {
    const totalCal = parseNum('total_working_days');
    const lop = parseNum('lop_days');
    const present = Math.max(0, totalCal - lop);
    document.getElementById('paid_days').value = present.toFixed(1);
    recalcLopAmount();
    calculatePayroll();
}

function recalcLopAmount() {
    const totalCal = parseNum('total_working_days');
    const lop = parseNum('lop_days');
    const gross = parseNum('gross_salary');
    if (totalCal > 0 && lop > 0 && gross > 0) {
        const rate = gross / totalCal;
        const lopDed = Math.round(lop * rate * 100) / 100;
        document.getElementById('lop_deduction').value = lopDed.toFixed(2);
    } else {
        document.getElementById('lop_deduction').value = '0.00';
    }
}

function recalcStatutoryDeductions() {
    const basic = parseNum('basic_salary');
    const gross = parseNum('gross_salary');

    // PF: 12% of basic capped at 1800
    const pf = Math.min(Math.round(basic * 0.12 * 100) / 100, 1800.00);
    document.getElementById('pf_deduction').value = pf.toFixed(2);

    // ESI: 0.75% of gross if gross <= 21000
    const esi = (gross <= 21000) ? (Math.round(gross * 0.0075 * 100) / 100) : 0.00;
    document.getElementById('esi_deduction').value = esi.toFixed(2);

    // PT: 200 standard
    document.getElementById('professional_tax').value = '200.00';

    calculatePayroll();
}

function calculatePayroll() {
    // 1. Gross Earnings
    const basic = parseNum('basic_salary');
    const da = parseNum('da');
    const bonus = parseNum('bonus');
    const leave = parseNum('leave_encashment');
    const other = parseNum('other_allowance');

    const gross = Math.round((basic + da + bonus + leave + other) * 100) / 100;
    document.getElementById('gross_salary').value = gross.toFixed(2);

    // 2. Total Deductions
    const pf = parseNum('pf_deduction');
    const esi = parseNum('esi_deduction');
    const pt = parseNum('professional_tax');
    const lop = parseNum('lop_deduction');
    const otherDed = parseNum('other_deductions');

    const totalDed = Math.round((pf + esi + pt + lop + otherDed) * 100) / 100;
    document.getElementById('total_deductions').value = totalDed.toFixed(2);

    // 3. Net Salary
    const net = Math.max(0, Math.round((gross - totalDed) * 100) / 100);
    document.getElementById('net_salary').value = net.toFixed(2);
    document.getElementById('displayNetSalary').innerText = '₹' + net.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function confirmResetAuto() {
    if (confirm('Are you sure you want to delete this saved payslip? The system will revert back to auto-calculated attendance values.')) {
        document.getElementById('deletePayslipForm').submit();
    }
}

// Initial calculation on page load
document.addEventListener('DOMContentLoaded', function() {
    calculatePayroll();
});
</script>
@endsection
