@extends('layouts.portal')
@section('title', 'My Payslips')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-receipt-cutoff text-success me-2"></i>My Salary Payslips</h4>
        <div class="text-muted small">View, verify, and print your monthly compensation and payslip statements.</div>
    </div>
    
    <div class="d-flex align-items-center gap-2">
        @if($currentPayslip)
            <a href="{{ route('portal.payslip.show', $currentPayslip->salary_month) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-box-arrow-up-right me-1"></i>Full Screen View
            </a>
            <button onclick="window.print()" class="btn btn-success btn-sm shadow-sm">
                <i class="bi bi-printer-fill me-1"></i>Print / Save PDF
            </button>
        @endif
    </div>
</div>

<!-- Month Selector Bar -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('portal.payslips') }}" class="row g-2 align-items-center">
            <div class="col-auto">
                <label class="fw-semibold text-dark small"><i class="bi bi-calendar3 text-success me-1"></i>Select Pay Period:</label>
            </div>
            <div class="col-12 col-sm-auto">
                <select name="month" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach($availableMonths as $mKey => $mLabel)
                        <option value="{{ $mKey }}" {{ $selectedMonth === $mKey ? 'selected' : '' }}>
                            {{ $mLabel }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-search me-1"></i>Load Statement
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row g-4">
    <!-- Main Payslip Document (Official corporate layout) -->
    <div class="col-12 col-xl-8">
        <div class="card border shadow-sm bg-white" id="printablePayslip">
            <div class="card-body p-4 p-md-5">
                <!-- Company Header & Title -->
                <div class="row pb-3 border-bottom mb-4 align-items-center">
                    <div class="col-sm-7">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <img src="{{ asset('logo/guru.png') }}" alt="Guru Living Assets" style="max-height: 44px; width: auto;">
                        </div>
                        <div class="fw-bold text-dark fs-5">GURU LIVING ASSETS</div>
                        <div class="text-muted small">India's Green Transformation Partner</div>
                        <div class="text-muted" style="font-size: 0.75rem;">
                            {{ $employee?->branch?->location ?? 'Bangalore / Chennai' }} &middot; GST / Corporate Registered
                        </div>
                    </div>
                    <div class="col-sm-5 text-sm-end mt-3 mt-sm-0">
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-6">
                            PAYSLIP
                        </span>
                        <div class="fw-bold text-dark mt-2">
                            {{ $currentPayslip ? $currentPayslip->month_name : \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}
                        </div>
                        <div class="text-muted small">
                            Status: <strong class="text-success">{{ $currentPayslip?->payment_status ?? 'Paid' }}</strong>
                        </div>
                    </div>
                </div>

                <!-- Employee & Salary Details Grid -->
                <div class="p-3 bg-light rounded-3 mb-4 border">
                    <div class="row g-3 small">
                        <div class="col-sm-6 col-md-3">
                            <span class="text-muted d-block">Employee Name:</span>
                            <span class="fw-bold text-dark">{{ $employee?->name ?? '—' }}</span>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <span class="text-muted d-block">Employee ID:</span>
                            <span class="fw-bold text-success font-monospace">{{ $employee?->employee_code ?? '—' }}</span>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <span class="text-muted d-block">Designation:</span>
                            <span class="fw-semibold text-dark">{{ $employee?->designation ?? '—' }}</span>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <span class="text-muted d-block">Department:</span>
                            <span class="fw-semibold text-dark">{{ $employee?->department ?? 'General' }}</span>
                        </div>

                        <div class="col-sm-6 col-md-3">
                            <span class="text-muted d-block">Branch:</span>
                            <span>{{ $employee?->branch?->name ?? 'Headquarters' }}</span>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <span class="text-muted d-block">Bank Account:</span>
                            <span class="font-monospace">{{ $employee?->bank_account_number ? Str::mask($employee->bank_account_number, '*', -4) : '—' }}</span>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <span class="text-muted d-block">Bank Name & IFSC:</span>
                            <span>{{ $employee?->bank_name ?? '—' }} ({{ $employee?->bank_ifsc ?? '—' }})</span>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <span class="text-muted d-block">PAN / UAN:</span>
                            <span class="font-monospace">{{ $employee?->pan_number ?: '—' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Attendance Days Breakdown -->
                <div class="d-flex justify-content-between align-items-center p-2 px-3 bg-white border rounded mb-4 flex-wrap gap-2 small">
                    <div>
                        <span class="text-muted">Total Calendar Days:</span>
                        <strong class="text-dark ms-1">{{ $currentPayslip?->total_working_days ?? 30 }}</strong>
                    </div>
                    <div>
                        <span class="text-muted">Payable Days:</span>
                        <strong class="text-success ms-1">{{ $currentPayslip?->paid_days ?? 30 }}</strong>
                    </div>
                    <div>
                        <span class="text-muted">Loss of Pay (LOP) Days:</span>
                        <strong class="{{ ($currentPayslip?->lop_days ?? 0) > 0 ? 'text-danger' : 'text-dark' }} ms-1">
                            {{ $currentPayslip?->lop_days ?? 0 }}
                        </strong>
                    </div>
                    <div>
                        <span class="text-muted">Payment Date:</span>
                        <strong class="text-dark ms-1">{{ $currentPayslip?->payment_date?->format('d M Y') ?? 'End of Month' }}</strong>
                    </div>
                </div>

                <!-- Earnings vs Deductions Table -->
                <div class="table-responsive mb-4">
                    <table class="table table-bordered mb-0">
                        <thead class="table-light text-center small fw-bold">
                            <tr>
                                <th class="text-start" style="width: 50%;">EARNINGS</th>
                                <th class="text-start" style="width: 50%;">DEDUCTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <!-- Left: Earnings Breakdown -->
                                <td class="p-0 align-top">
                                    <table class="table table-sm table-borderless mb-0 small">
                                        <tbody>
                                            <tr>
                                                <td class="text-muted">Basic Salary</td>
                                                <td class="text-end fw-semibold">₹{{ number_format($currentPayslip?->basic_salary ?? 0, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">House Rent Allowance (HRA)</td>
                                                <td class="text-end fw-semibold">₹{{ number_format($currentPayslip?->hra ?? 0, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Conveyance Allowance</td>
                                                <td class="text-end fw-semibold">₹{{ number_format($currentPayslip?->conveyance_allowance ?? 0, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Special Allowance</td>
                                                <td class="text-end fw-semibold">₹{{ number_format($currentPayslip?->special_allowance ?? 0, 2) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>

                                <!-- Right: Deductions Breakdown -->
                                <td class="p-0 align-top">
                                    <table class="table table-sm table-borderless mb-0 small">
                                        <tbody>
                                            <tr>
                                                <td class="text-muted">Provident Fund (PF)</td>
                                                <td class="text-end fw-semibold text-danger">₹{{ number_format($currentPayslip?->pf_deduction ?? 0, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Employee State Insurance (ESI)</td>
                                                <td class="text-end fw-semibold text-danger">₹{{ number_format($currentPayslip?->esi_deduction ?? 0, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Professional Tax (PT)</td>
                                                <td class="text-end fw-semibold text-danger">₹{{ number_format($currentPayslip?->professional_tax ?? 0, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Loss of Pay (LOP Deductions)</td>
                                                <td class="text-end fw-semibold text-danger">₹{{ number_format($currentPayslip?->lop_deduction ?? 0, 2) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>

                            <!-- Subtotals -->
                            <tr class="table-light fw-bold small">
                                <td class="d-flex justify-content-between">
                                    <span>Total Gross Earnings:</span>
                                    <span class="text-success">₹{{ number_format($currentPayslip?->gross_salary ?? 0, 2) }}</span>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-between text-danger">
                                        <span>Total Deductions:</span>
                                        <span>₹{{ number_format($currentPayslip?->total_deductions ?? 0, 2) }}</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Net Take-Home Pay Banner -->
                <div class="p-3 rounded-3 border bg-success-subtle text-success d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                    <div>
                        <div class="small fw-semibold text-uppercase" style="letter-spacing: 0.05em;">Net Salary Payable</div>
                        <div class="small text-muted">{{ $currentPayslip?->net_salary_in_words ?? 'Calculated on attendance & CTC' }}</div>
                    </div>
                    <div class="text-end">
                        <h2 class="fw-bold mb-0 text-success">
                            ₹{{ number_format($currentPayslip?->net_salary ?? 0, 2) }}
                        </h2>
                    </div>
                </div>

                <!-- Footer Signatory Notes -->
                <div class="row pt-4 mt-4 border-top text-muted small align-items-end">
                    <div class="col-8">
                        <div class="fw-semibold text-dark mb-1">Important Note:</div>
                        <div>This is a system-generated compensation payslip issued by GURU Living Assets HRM Self-Service Portal. No manual signature required.</div>
                    </div>
                    <div class="col-4 text-end">
                        <div class="border-top pt-2 text-dark fw-bold">Authorized Signatory</div>
                        <div style="font-size: 0.7rem;">HR & Finance Dept.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Salary Structure & Quick History -->
    <div class="col-12 col-xl-4">
        <!-- Salary Info Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold text-dark">
                    <i class="bi bi-wallet2 text-success me-2"></i>Compensation Overview
                </h6>
            </div>
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <span class="text-muted small">Fixed Monthly CTC:</span>
                    <strong class="text-dark">₹{{ number_format($employee?->salary ?? 0, 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <span class="text-muted small">Annual CTC:</span>
                    <strong class="text-dark">₹{{ number_format(($employee?->salary ?? 0) * 12, 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <span class="text-muted small">Employment Type:</span>
                    <span class="badge bg-light text-dark border">{{ $employee?->employee_type ?? 'Full-Time' }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small">Salary Mode:</span>
                    <span class="text-success small fw-semibold"><i class="bi bi-check-circle me-1"></i>Direct Bank Transfer</span>
                </div>
            </div>
        </div>

        <!-- Past Months Payslips History -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold text-dark">
                    <i class="bi bi-clock-history text-primary me-2"></i>Payslip History
                </h6>
                <span class="badge bg-light text-muted border small">{{ count($allPayslips) }} statements</span>
            </div>
            <div class="list-group list-group-flush small">
                @forelse($allPayslips as $slip)
                    <a href="{{ route('portal.payslips', ['month' => $slip->salary_month]) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ $selectedMonth === $slip->salary_month ? 'active' : '' }}">
                        <div>
                            <div class="fw-semibold">{{ $slip->month_name }}</div>
                            <small class="{{ $selectedMonth === $slip->salary_month ? 'text-white-50' : 'text-muted' }}">
                                Days: {{ $slip->paid_days }} / {{ $slip->total_working_days }}
                            </small>
                        </div>
                        <div class="text-end">
                            <span class="fw-bold {{ $selectedMonth === $slip->salary_month ? 'text-white' : 'text-success' }}">
                                ₹{{ number_format($slip->net_salary, 0) }}
                            </span>
                            <span class="d-block badge {{ $selectedMonth === $slip->salary_month ? 'bg-light text-dark' : 'bg-success-subtle text-success' }}" style="font-size: 0.65rem;">
                                {{ $slip->payment_status }}
                            </span>
                        </div>
                    </a>
                @empty
                    <div class="p-3 text-center text-muted">
                        No previous payslip statements generated yet.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
