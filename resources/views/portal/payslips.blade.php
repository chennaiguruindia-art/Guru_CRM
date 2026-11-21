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
                            {{ $mLabel }} {{ $mKey === now('Asia/Kolkata')->format('Y-m') ? '(Current Month • Live)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-search me-1"></i>Load Statement
                </button>
            </div>
            <div class="col-auto ms-sm-auto">
                <a href="{{ route('portal.payslips', ['month' => $selectedMonth, 'refresh' => 1]) }}" class="btn btn-sm btn-outline-primary" title="Force refresh live calculation with latest attendance">
                    <i class="bi bi-arrow-clockwise me-1"></i>Refresh Live Data
                </a>
            </div>
        </form>
    </div>
</div>

@if($currentPayslip && $currentPayslip->payment_status === 'In Progress')
<div class="alert alert-primary border-primary-subtle bg-primary-subtle bg-opacity-10 shadow-sm d-flex align-items-center justify-content-between p-3 mb-4 rounded-3 flex-wrap gap-2">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle bg-primary bg-opacity-10 p-2 text-primary d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
            <i class="bi bi-lightning-charge-fill fs-5"></i>
        </div>
        <div>
            <div class="fw-bold text-dark">Live Dynamic Statement for {{ $currentPayslip->month_name }}</div>
            <div class="text-muted small">
                This payslip is <strong>100% dynamic</strong>. You can view it anytime without waiting for month-end! As you punch daily, your payable days, LOP, and net salary recalculate automatically. Final salary is locked on <strong>{{ $currentPayslip->payment_date->format('d M Y') }}</strong> (last day of the month).
            </div>
        </div>
    </div>
    <span class="badge bg-primary px-3 py-2 text-white">
        <i class="bi bi-activity me-1"></i>Dynamic Real-Time
    </span>
</div>
@endif

<div class="row g-4">
    <!-- Main Payslip Document (Corporate Statutory Format - Form XXVIII) -->
    <div class="col-12 col-xl-8">
        @if($currentPayslip)
        <div class="card border-0 shadow-sm bg-white overflow-hidden" id="printablePayslip">
            <div class="card-body p-2 p-md-4">
                <div class="payslip-wrapper border border-2 border-dark bg-white" style="color: #000; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
                    
                    <!-- 1. Company Header Box -->
                    <div class="p-3 border-bottom border-2 border-dark d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h4 class="fw-bold mb-1 text-dark" style="font-size: 1.35rem; letter-spacing: -0.3px;">
                                Guru Integrated Services India Pvt Ltd
                            </h4>
                            <div style="font-size: 0.85rem; line-height: 1.35; color: #222;">
                                1st Floor, F 200, First St, Block F, Annanagar East, Chennai,<br>
                                Tamil Nadu – 600102
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="d-flex align-items-center justify-content-end gap-2 mb-1">
                                <img src="{{ asset('logo/guru.png') }}" alt="GURU" style="max-height: 48px; width: auto;">
                            </div>
                            <div class="fw-bold" style="font-size: 0.78rem; color: #15803d; letter-spacing: 0.2px;">
                                India's Green Transformation Partner
                            </div>
                        </div>
                    </div>

                    <!-- 2. Statutory Subtitle Bar -->
                    <div class="text-center py-2 px-1 fw-bold text-dark border-bottom border-2 border-dark" style="background-color: #cfe2ff; font-size: 0.9rem; letter-spacing: 0.3px;">
                        FORM No XXVIII Under Rule 78(1)(b) of the TamilNadu Contract Labour Rules, 1975
                    </div>

                    <!-- 3. Bank Details Grid (4 columns) -->
                    <div class="table-responsive mb-0">
                        <table class="table table-bordered border-dark mb-0 text-center small" style="border-collapse: collapse;">
                            <thead>
                                <tr style="background-color: #f8fafc;">
                                    <th class="py-2 border border-dark fw-bold" style="width: 25%;">Bank Name</th>
                                    <th class="py-2 border border-dark fw-bold" style="width: 25%;">Account Holder Name</th>
                                    <th class="py-2 border border-dark fw-bold" style="width: 25%;">Account Number</th>
                                    <th class="py-2 border border-dark fw-bold" style="width: 25%;">IFSC Code</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="py-2 border border-dark fw-semibold">{{ $employee?->bank_name ?? '—' }}</td>
                                    <td class="py-2 border border-dark fw-semibold">{{ $employee?->name ?? '—' }}</td>
                                    <td class="py-2 border border-dark font-monospace fw-semibold">{{ $employee?->bank_account_number ?? '—' }}</td>
                                    <td class="py-2 border border-dark font-monospace fw-semibold">{{ $employee?->bank_ifsc ?? '—' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- 4. Dark Blue Month Header Bar -->
                    <div class="text-center py-2 fw-bold text-white border-top border-bottom border-2 border-dark" style="background-color: #0b3d7d; font-size: 1.15rem; letter-spacing: 0.5px;">
                        Pay Slip ({{ $currentPayslip->month_name }})
                    </div>

                    <!-- 5. Employee Details Grid -->
                    <div class="table-responsive mb-0">
                        <table class="table table-bordered border-dark mb-0 small" style="border-collapse: collapse;">
                            <tbody>
                                <tr>
                                    <td class="border border-dark fw-bold px-2 py-1" style="width: 14%; background-color: #f8fafc;">Emp Name:</td>
                                    <td class="border border-dark fw-semibold px-2 py-1" style="width: 28%;">{{ $employee?->name ?? '—' }}</td>
                                    <td class="border border-dark fw-bold px-2 py-1 text-nowrap" style="width: 20%; background-color: #f8fafc;">Total Calendar Days:</td>
                                    <td class="border border-dark fw-semibold text-center px-2 py-1" style="width: 8%;">{{ $currentPayslip->total_working_days }}</td>
                                    <td class="border border-dark fw-bold px-2 py-1 text-nowrap" style="width: 18%; background-color: #f8fafc;">Total Days Present:</td>
                                    <td class="border border-dark fw-semibold text-center px-2 py-1" style="width: 12%;">{{ (int) $currentPayslip->paid_days }}</td>
                                </tr>
                                <tr>
                                    <td class="border border-dark fw-bold px-2 py-1" style="background-color: #f8fafc;">Emp No:</td>
                                    <td class="border border-dark font-monospace fw-bold text-success px-2 py-1">{{ $employee?->employee_code ?? '—' }}</td>
                                    <td class="border border-dark fw-bold px-2 py-1" style="background-color: #f8fafc;">ESI No:</td>
                                    <td class="border border-dark font-monospace fw-semibold text-center px-2 py-1">{{ $employee?->pan_number ?: '—' }}</td>
                                    <td class="border border-dark fw-bold px-2 py-1" style="background-color: #f8fafc;">UAN No:</td>
                                    <td class="border border-dark font-monospace fw-semibold text-center px-2 py-1">{{ $employee?->uan_number ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="border border-dark fw-bold px-2 py-1" style="background-color: #f8fafc;">Designation:</td>
                                    <td class="border border-dark fw-semibold px-2 py-1">{{ $employee?->designation ?? '—' }}</td>
                                    <td class="border border-dark fw-bold px-2 py-1" style="background-color: #f8fafc;">PF No:</td>
                                    <td colspan="3" class="border border-dark font-monospace fw-semibold px-2 py-1">{{ $employee?->pf_number ?: '—' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- 6. Earnings vs Deductions Table -->
                    <div class="table-responsive mb-0">
                        <table class="table table-bordered border-dark mb-0" style="border-collapse: collapse;">
                            <thead>
                                <tr style="background-color: #cfe2ff;">
                                    <th class="border border-dark text-center py-2 fw-bold text-dark fs-6" style="width: 33%;">Earnings</th>
                                    <th class="border border-dark text-center py-2 fw-bold text-dark fs-6" style="width: 17%;">Amount (₹)</th>
                                    <th class="border border-dark text-center py-2 fw-bold text-dark fs-6" style="width: 33%;">Deductions</th>
                                    <th class="border border-dark text-center py-2 fw-bold text-dark fs-6" style="width: 17%;">Amount (₹)</th>
                                </tr>
                            </thead>
                            <tbody class="small">
                                <tr>
                                    <td class="border border-dark py-2 px-3 fw-medium">Basic</td>
                                    <td class="border border-dark py-2 px-3 text-end fw-semibold">{{ number_format($currentPayslip->basic_salary, 2) }}</td>
                                    <td class="border border-dark py-2 px-3 fw-medium">PF</td>
                                    <td class="border border-dark py-2 px-3 text-end fw-semibold text-danger">{{ $currentPayslip->pf_deduction > 0 ? number_format($currentPayslip->pf_deduction, 2) : '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="border border-dark py-2 px-3 fw-medium">House Rent Allowance (HRA)</td>
                                    <td class="border border-dark py-2 px-3 text-end fw-semibold">{{ $currentPayslip->hra > 0 ? number_format($currentPayslip->hra, 2) : '-' }}</td>
                                    <td class="border border-dark py-2 px-3 fw-medium">ESI</td>
                                    <td class="border border-dark py-2 px-3 text-end fw-semibold text-danger">{{ $currentPayslip->esi_deduction > 0 ? number_format($currentPayslip->esi_deduction, 2) : '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="border border-dark py-2 px-3 fw-medium">Conveyance Allowance</td>
                                    <td class="border border-dark py-2 px-3 text-end fw-semibold">{{ $currentPayslip->conveyance_allowance > 0 ? number_format($currentPayslip->conveyance_allowance, 2) : '-' }}</td>
                                    <td class="border border-dark py-2 px-3 fw-medium">Prof. Tax</td>
                                    <td class="border border-dark py-2 px-3 text-end fw-semibold text-danger">{{ $currentPayslip->professional_tax > 0 ? number_format($currentPayslip->professional_tax, 2) : '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="border border-dark py-2 px-3 fw-medium">Special Allowance</td>
                                    <td class="border border-dark py-2 px-3 text-end fw-semibold">{{ $currentPayslip->special_allowance > 0 ? number_format($currentPayslip->special_allowance, 2) : '-' }}</td>
                                    <td class="border border-dark py-2 px-3 fw-medium">LOP</td>
                                    <td class="border border-dark py-2 px-3 text-end fw-semibold text-danger">{{ $currentPayslip->lop_deduction > 0 ? number_format($currentPayslip->lop_deduction, 2) : '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="border border-dark py-2 px-3 fw-medium">Other Allowance</td>
                                    <td class="border border-dark py-2 px-3 text-end fw-semibold">{{ ($currentPayslip->other_allowance ?? 0) > 0 ? number_format($currentPayslip->other_allowance, 2) : '-' }}</td>
                                    <td class="border border-dark py-2 px-3 fw-medium">Other Deductions</td>
                                    <td class="border border-dark py-2 px-3 text-end fw-semibold text-danger">{{ ($currentPayslip->other_deductions ?? 0) > 0 ? number_format($currentPayslip->other_deductions, 2) : '-' }}</td>
                                </tr>
                                <!-- Total Row -->
                                <tr class="fw-bold" style="background-color: #cfe2ff; font-size: 0.95rem;">
                                    <td class="border border-dark py-2 px-3">Total</td>
                                    <td class="border border-dark py-2 px-3 text-end text-success">{{ number_format($currentPayslip->gross_salary, 2) }}</td>
                                    <td class="border border-dark py-2 px-3">Total</td>
                                    <td class="border border-dark py-2 px-3 text-end text-danger">{{ number_format($currentPayslip->total_deductions, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- 7. Net Pay Highlighting Bar -->
                    <div class="d-flex justify-content-between align-items-center py-2 px-3 px-md-4 border-bottom border-2 border-dark" style="background-color: #cfe2ff;">
                        <span class="fw-bold fs-5 text-dark">Net Pay:</span>
                        <span class="fw-bold fs-4 text-dark font-monospace">₹{{ number_format($currentPayslip->net_salary, 2) }}</span>
                    </div>

                    <!-- 8. Net Payable in Words -->
                    <div class="py-2 px-3 px-md-4 border-bottom border-2 border-dark small fw-semibold bg-white d-flex align-items-center flex-wrap gap-2">
                        <span class="text-dark">Net Payable in Words:</span>
                        <span class="text-dark fw-bold">{{ $currentPayslip->net_salary_in_words }}</span>
                    </div>

                    <!-- 9. Signatures Footer -->
                    <div class="p-3 p-md-4 pt-4 d-flex justify-content-between align-items-end bg-white" style="min-height: 90px;">
                        <div class="text-center" style="min-width: 170px;">
                            <div class="border-top border-dark pt-1 fw-bold small text-dark">Receiver's Signature</div>
                        </div>
                        <div class="text-center" style="min-width: 170px;">
                            <div class="border-top border-dark pt-1 fw-bold small text-dark">Authorized Signatory</div>
                            <div class="text-muted" style="font-size: 0.7rem;">Guru Integrated Services</div>
                        </div>
                    </div>
                </div>

                <!-- Live Status / Help Note under Document -->
                <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap gap-2 small text-muted">
                    <div>
                        <i class="bi bi-info-circle text-primary me-1"></i>
                        <span>Payment Date: <strong>{{ $currentPayslip->payment_date?->format('d M Y') ?? 'End of Month' }}</strong></span>
                        @if($currentPayslip->payment_status === 'In Progress')
                            &middot; <span class="badge bg-primary-subtle text-primary border">In Progress &middot; Live Attendance Sync</span>
                        @else
                            &middot; <span class="badge bg-success-subtle text-success border">Paid &middot; Official Locked</span>
                        @endif
                    </div>
                    <div>
                        <button onclick="window.print()" class="btn btn-sm btn-outline-dark">
                            <i class="bi bi-printer me-1"></i>Print Form XXVIII
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="card border shadow-sm bg-white p-5 text-center">
            <div class="mb-3">
                <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
            </div>
            <h5 class="fw-bold text-dark">No Payslip Available for {{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}</h5>
            <p class="text-muted small mb-3">
                Your official joining date is <strong>{{ $employee?->joining_date?->format('d M Y') ?? '—' }}</strong>.<br>
                Salary calculations and payslips begin from your joining month onwards.
            </p>
            <div>
                <a href="{{ route('portal.payslips') }}" class="btn btn-sm btn-success">
                    <i class="bi bi-arrow-left me-1"></i>View Current Month ({{ now('Asia/Kolkata')->format('F Y') }})
                </a>
            </div>
        </div>
        @endif
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
