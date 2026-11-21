<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payslip - {{ $employee->employee_code }} - {{ $payslip->month_name }}</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f8fafc;
            color: #1e293b;
            font-family: system-ui, -apple-system, sans-serif;
        }
        .payslip-container {
            max-width: 850px;
            margin: 20px auto;
            background: #fff;
            padding: 35px 45px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        @media print {
            body {
                background: #fff;
                margin: 0;
                padding: 0;
            }
            .payslip-container {
                max-width: 100%;
                margin: 0;
                border: none;
                box-shadow: none;
                padding: 20px;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="container no-print py-3 text-center">
        <button onclick="window.print()" class="btn btn-success px-4 py-2 fw-semibold shadow-sm me-2">
            <i class="bi bi-printer-fill me-1"></i>Print / Save as PDF
        </button>
        <button onclick="window.close()" class="btn btn-outline-secondary px-3 py-2">
            <i class="bi bi-x-circle me-1"></i>Close
        </button>
    </div>

    <div class="payslip-container">
        <!-- Header -->
        <div class="row pb-3 border-bottom mb-3 align-items-center">
            <div class="col-8">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <img src="{{ asset('logo/guru.png') }}" alt="Guru Living Assets" style="max-height: 48px; width: auto;">
                </div>
                <h4 class="fw-bold text-dark mb-0">GURU LIVING ASSETS</h4>
                <div class="text-muted small">India's Green Transformation Partner</div>
                <div class="text-muted" style="font-size: 0.8rem;">
                    Branch: {{ $employee->branch?->name ?? 'HQ' }} &middot; {{ $employee->branch?->location ?? 'Karnataka / Tamil Nadu' }}
                </div>
            </div>
            <div class="col-4 text-end">
                <span class="badge bg-success px-3 py-2 fs-6 mb-2">SALARY PAYSLIP</span>
                <div class="fw-bold fs-5 text-dark">{{ $payslip->month_name }}</div>
                <div class="text-muted small">Status: <strong class="text-success">{{ $payslip->payment_status }}</strong></div>
            </div>
        </div>

        <!-- Employee Info Summary Box -->
        <div class="p-3 bg-light rounded border mb-3">
            <div class="row g-2 small">
                <div class="col-3">
                    <span class="text-muted d-block">Employee Name:</span>
                    <strong class="text-dark">{{ $employee->name }}</strong>
                </div>
                <div class="col-3">
                    <span class="text-muted d-block">Employee Code:</span>
                    <strong class="text-success font-monospace">{{ $employee->employee_code }}</strong>
                </div>
                <div class="col-3">
                    <span class="text-muted d-block">Designation:</span>
                    <span class="fw-semibold text-dark">{{ $employee->designation }}</span>
                </div>
                <div class="col-3">
                    <span class="text-muted d-block">Department:</span>
                    <span>{{ $employee->department ?? 'General' }}</span>
                </div>

                <div class="col-3 mt-2">
                    <span class="text-muted d-block">Bank Name:</span>
                    <span>{{ $employee->bank_name ?? '—' }}</span>
                </div>
                <div class="col-3 mt-2">
                    <span class="text-muted d-block">Account No:</span>
                    <span class="font-monospace">{{ $employee->bank_account_number ? Str::mask($employee->bank_account_number, '*', -4) : '—' }}</span>
                </div>
                <div class="col-3 mt-2">
                    <span class="text-muted d-block">Bank IFSC:</span>
                    <span class="font-monospace">{{ $employee->bank_ifsc ?? '—' }}</span>
                </div>
                <div class="col-3 mt-2">
                    <span class="text-muted d-block">PAN Number:</span>
                    <span class="font-monospace">{{ $employee->pan_number ?? '—' }}</span>
                </div>

                <div class="col-3 mt-2">
                    <span class="text-muted d-block">PF Number:</span>
                    <span class="font-monospace">{{ $employee->pf_number ?? '—' }}</span>
                </div>
                <div class="col-3 mt-2">
                    <span class="text-muted d-block">UAN Number:</span>
                    <span class="font-monospace">{{ $employee->uan_number ?? '—' }}</span>
                </div>
                <div class="col-3 mt-2">
                    <span class="text-muted d-block">Joining Date:</span>
                    <span>{{ $employee->joining_date?->format('d M Y') ?? '—' }}</span>
                </div>
                <div class="col-3 mt-2">
                    <span class="text-muted d-block">Paid Sundays:</span>
                    <span class="text-success fw-semibold"><i class="bi bi-check-circle-fill me-1"></i>Included in Payable Days</span>
                </div>
            </div>
        </div>

        <!-- Attendance Stats -->
        <div class="d-flex justify-content-between p-2 px-3 border rounded mb-3 small bg-white">
            <div><span class="text-muted">Total Calendar Days:</span> <strong>{{ $payslip->total_working_days }}</strong></div>
            <div><span class="text-muted">Payable Days:</span> <strong class="text-success">{{ $payslip->paid_days }}</strong></div>
            <div><span class="text-muted">Loss of Pay (LOP):</span> <strong class="{{ $payslip->lop_days > 0 ? 'text-danger' : 'text-dark' }}">{{ $payslip->lop_days }}</strong></div>
            <div><span class="text-muted">Payment Date:</span> <strong>{{ $payslip->payment_date?->format('d M Y') ?? '—' }}</strong></div>
        </div>

        <!-- Earnings and Deductions Table -->
        <table class="table table-bordered mb-3">
            <thead class="table-light small fw-bold">
                <tr>
                    <th style="width: 50%;">EARNINGS</th>
                    <th style="width: 50%;">DEDUCTIONS</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="p-0 align-top">
                        <table class="table table-sm table-borderless mb-0 small">
                            <tbody>
                                <tr>
                                    <td class="text-muted">Basic Salary</td>
                                    <td class="text-end fw-semibold">₹{{ number_format($payslip->basic_salary, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">House Rent Allowance (HRA)</td>
                                    <td class="text-end fw-semibold">₹{{ number_format($payslip->hra, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Conveyance Allowance</td>
                                    <td class="text-end fw-semibold">₹{{ number_format($payslip->conveyance_allowance, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Special Allowance</td>
                                    <td class="text-end fw-semibold">₹{{ number_format($payslip->special_allowance, 2) }}</td>
                                </tr>
                                @if(($payslip->other_allowance ?? 0) > 0)
                                <tr>
                                    <td class="text-muted">Other Allowance</td>
                                    <td class="text-end fw-semibold">₹{{ number_format($payslip->other_allowance, 2) }}</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </td>
                    <td class="p-0 align-top">
                        <table class="table table-sm table-borderless mb-0 small">
                            <tbody>
                                <tr>
                                    <td class="text-muted">Provident Fund (PF)</td>
                                    <td class="text-end fw-semibold text-danger">₹{{ number_format($payslip->pf_deduction, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Employee State Insurance (ESI)</td>
                                    <td class="text-end fw-semibold text-danger">₹{{ number_format($payslip->esi_deduction, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Professional Tax (PT)</td>
                                    <td class="text-end fw-semibold text-danger">₹{{ number_format($payslip->professional_tax, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Loss of Pay (LOP Deduction)</td>
                                    <td class="text-end fw-semibold text-danger">₹{{ number_format($payslip->lop_deduction, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr class="table-light fw-bold small">
                    <td class="d-flex justify-content-between">
                        <span>Total Gross Earnings:</span>
                        <span class="text-success">₹{{ number_format($payslip->gross_salary, 2) }}</span>
                    </td>
                    <td>
                        <div class="d-flex justify-content-between text-danger">
                            <span>Total Deductions:</span>
                            <span>₹{{ number_format($payslip->total_deductions, 2) }}</span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Net Salary Amount -->
        <div class="p-3 bg-light border rounded d-flex justify-content-between align-items-center mb-4">
            <div>
                <div class="small fw-semibold text-uppercase text-muted">Net Salary Payable:</div>
                <div class="fw-bold text-dark small">{{ $payslip->net_salary_in_words }}</div>
            </div>
            <div class="text-end">
                <h3 class="fw-bold text-success mb-0">₹{{ number_format($payslip->net_salary, 2) }}</h3>
            </div>
        </div>

        @if(!empty($payslip->remarks))
        <div class="p-2 px-3 border border-warning-subtle bg-warning-subtle rounded small mb-4 text-dark">
            <i class="bi bi-info-circle-fill text-warning me-1"></i>
            <strong>Note / Remarks:</strong> {{ $payslip->remarks }}
        </div>
        @endif

        <!-- Signatures & Disclaimer -->
        <div class="row pt-4 mt-4 border-top small text-muted align-items-end">
            <div class="col-8">
                <p class="mb-0" style="font-size: 0.75rem;">
                    This document is computer-generated and issued by GURU Living Assets HRM Portal. If you have any inquiries regarding your salary or tax deductions, please contact the HR Department within 7 days of receipt.
                </p>
            </div>
            <div class="col-4 text-end">
                <div class="border-top pt-2 text-dark fw-bold">Authorized Signatory</div>
                <div style="font-size: 0.7rem;">HR & Finance Dept.</div>
            </div>
        </div>
    </div>
</body>
</html>
