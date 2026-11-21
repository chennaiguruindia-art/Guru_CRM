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
            background-color: #f1f5f9;
            color: #000;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        .payslip-container {
            max-width: 900px;
            margin: 25px auto;
            background: #fff;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            border-radius: 6px;
        }
        .payslip-box {
            border: 2px solid #000;
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
                padding: 0;
                box-shadow: none;
                border-radius: 0;
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
            <i class="bi bi-printer-fill me-1"></i>Print Form XXVIII / Save as PDF
        </button>
        <button onclick="window.close()" class="btn btn-outline-secondary px-3 py-2">
            <i class="bi bi-x-circle me-1"></i>Close
        </button>
    </div>

    <div class="payslip-container">
        <div class="payslip-box bg-white">
            <!-- 1. Header -->
            <div class="p-3 border-bottom border-2 border-dark d-flex justify-content-between align-items-center">
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
                        <td class="py-2 border border-dark fw-semibold">{{ $employee->bank_name ?? '—' }}</td>
                        <td class="py-2 border border-dark fw-semibold">{{ $employee->name }}</td>
                        <td class="py-2 border border-dark font-monospace fw-semibold">{{ $employee->bank_account_number ?? '—' }}</td>
                        <td class="py-2 border border-dark font-monospace fw-semibold">{{ $employee->bank_ifsc ?? '—' }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- 4. Dark Blue Month Header Bar -->
            <div class="text-center py-2 fw-bold text-white border-top border-bottom border-2 border-dark" style="background-color: #0b3d7d; font-size: 1.15rem; letter-spacing: 0.5px;">
                Pay Slip ({{ $payslip->month_name }})
            </div>

            <!-- 5. Employee Details Grid -->
            <table class="table table-bordered border-dark mb-0 small" style="border-collapse: collapse;">
                <tbody>
                    <tr>
                        <td class="border border-dark fw-bold px-2 py-1" style="width: 14%; background-color: #f8fafc;">Emp Name:</td>
                        <td class="border border-dark fw-semibold px-2 py-1" style="width: 28%;">{{ $employee->name }}</td>
                        <td class="border border-dark fw-bold px-2 py-1 text-nowrap" style="width: 20%; background-color: #f8fafc;">Total Calendar Days:</td>
                        <td class="border border-dark fw-semibold text-center px-2 py-1" style="width: 8%;">{{ $payslip->total_working_days }}</td>
                        <td class="border border-dark fw-bold px-2 py-1 text-nowrap" style="width: 18%; background-color: #f8fafc;">Total Days Present:</td>
                        <td class="border border-dark fw-semibold text-center px-2 py-1" style="width: 12%;">{{ (int) $payslip->paid_days }}</td>
                    </tr>
                    <tr>
                        <td class="border border-dark fw-bold px-2 py-1" style="background-color: #f8fafc;">Emp No:</td>
                        <td class="border border-dark font-monospace fw-bold text-success px-2 py-1">{{ $employee->employee_code }}</td>
                        <td class="border border-dark fw-bold px-2 py-1" style="background-color: #f8fafc;">ESI No:</td>
                        <td class="border border-dark font-monospace fw-semibold text-center px-2 py-1">{{ $employee->pan_number ?: '—' }}</td>
                        <td class="border border-dark fw-bold px-2 py-1" style="background-color: #f8fafc;">UAN No:</td>
                        <td class="border border-dark font-monospace fw-semibold text-center px-2 py-1">{{ $employee->uan_number ?: '—' }}</td>
                    </tr>
                    <tr>
                        <td class="border border-dark fw-bold px-2 py-1" style="background-color: #f8fafc;">Designation:</td>
                        <td class="border border-dark fw-semibold px-2 py-1">{{ $employee->designation }}</td>
                        <td class="border border-dark fw-bold px-2 py-1" style="background-color: #f8fafc;">PF No:</td>
                        <td colspan="3" class="border border-dark font-monospace fw-semibold px-2 py-1">{{ $employee->pf_number ?: '—' }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- 6. Earnings vs Deductions Table -->
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
                        <td class="border border-dark py-2 px-3 text-end fw-semibold">{{ number_format($payslip->basic_salary, 2) }}</td>
                        <td class="border border-dark py-2 px-3 fw-medium">PF</td>
                        <td class="border border-dark py-2 px-3 text-end fw-semibold text-danger">{{ $payslip->pf_deduction > 0 ? number_format($payslip->pf_deduction, 2) : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="border border-dark py-2 px-3 fw-medium">House Rent Allowance (HRA)</td>
                        <td class="border border-dark py-2 px-3 text-end fw-semibold">{{ $payslip->hra > 0 ? number_format($payslip->hra, 2) : '-' }}</td>
                        <td class="border border-dark py-2 px-3 fw-medium">ESI</td>
                        <td class="border border-dark py-2 px-3 text-end fw-semibold text-danger">{{ $payslip->esi_deduction > 0 ? number_format($payslip->esi_deduction, 2) : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="border border-dark py-2 px-3 fw-medium">Conveyance Allowance</td>
                        <td class="border border-dark py-2 px-3 text-end fw-semibold">{{ $payslip->conveyance_allowance > 0 ? number_format($payslip->conveyance_allowance, 2) : '-' }}</td>
                        <td class="border border-dark py-2 px-3 fw-medium">Prof. Tax</td>
                        <td class="border border-dark py-2 px-3 text-end fw-semibold text-danger">{{ $payslip->professional_tax > 0 ? number_format($payslip->professional_tax, 2) : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="border border-dark py-2 px-3 fw-medium">Special Allowance</td>
                        <td class="border border-dark py-2 px-3 text-end fw-semibold">{{ $payslip->special_allowance > 0 ? number_format($payslip->special_allowance, 2) : '-' }}</td>
                        <td class="border border-dark py-2 px-3 fw-medium">LOP</td>
                        <td class="border border-dark py-2 px-3 text-end fw-semibold text-danger">{{ $payslip->lop_deduction > 0 ? number_format($payslip->lop_deduction, 2) : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="border border-dark py-2 px-3 fw-medium">Other Allowance</td>
                        <td class="border border-dark py-2 px-3 text-end fw-semibold">{{ ($payslip->other_allowance ?? 0) > 0 ? number_format($payslip->other_allowance, 2) : '-' }}</td>
                        <td class="border border-dark py-2 px-3 fw-medium">Other Deductions</td>
                        <td class="border border-dark py-2 px-3 text-end fw-semibold text-danger">{{ ($payslip->other_deductions ?? 0) > 0 ? number_format($payslip->other_deductions, 2) : '-' }}</td>
                    </tr>
                    <!-- Total Row -->
                    <tr class="fw-bold" style="background-color: #cfe2ff; font-size: 0.95rem;">
                        <td class="border border-dark py-2 px-3">Total</td>
                        <td class="border border-dark py-2 px-3 text-end text-success">{{ number_format($payslip->gross_salary, 2) }}</td>
                        <td class="border border-dark py-2 px-3">Total</td>
                        <td class="border border-dark py-2 px-3 text-end text-danger">{{ number_format($payslip->total_deductions, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- 7. Net Pay Highlighting Bar -->
            <div class="d-flex justify-content-between align-items-center py-2 px-4 border-bottom border-2 border-dark" style="background-color: #cfe2ff;">
                <span class="fw-bold fs-5 text-dark">Net Pay:</span>
                <span class="fw-bold fs-4 text-dark font-monospace">₹{{ number_format($payslip->net_salary, 2) }}</span>
            </div>

            <!-- 8. Net Payable in Words -->
            <div class="py-2 px-4 border-bottom border-2 border-dark small fw-semibold bg-white d-flex align-items-center flex-wrap gap-2">
                <span class="text-dark">Net Payable in Words:</span>
                <span class="text-dark fw-bold">{{ $payslip->net_salary_in_words }}</span>
            </div>

            <!-- 9. Signatures Footer -->
            <div class="p-4 pt-5 d-flex justify-content-between align-items-end bg-white" style="min-height: 100px;">
                <div class="text-center" style="min-width: 180px;">
                    <div class="border-top border-dark pt-1 fw-bold small text-dark">Receiver's Signature</div>
                </div>
                <div class="text-center" style="min-width: 180px;">
                    <div class="border-top border-dark pt-1 fw-bold small text-dark">Authorized Signatory</div>
                    <div class="text-muted" style="font-size: 0.7rem;">Guru Integrated Services</div>
                </div>
            </div>
        </div>

        @if(!empty($payslip->remarks))
        <div class="p-2 px-3 border border-dark rounded small mt-3 bg-light text-muted">
            <i class="bi bi-info-circle me-1"></i>
            <strong>Note / Remarks:</strong> {{ $payslip->remarks }}
        </div>
        @endif
    </div>
</body>
</html>
