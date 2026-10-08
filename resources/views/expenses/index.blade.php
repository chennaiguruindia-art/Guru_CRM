@extends('layouts.app')
@section('title', 'Expenses')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-wallet2 text-success me-2"></i>Expenses & Project Costs</h4>
        <p class="text-muted small mb-0">Record project operational expenses, labour payments, transport, fuel, and supplies.</p>
    </div>
    <a href="{{ route('expenses.create') }}" class="btn btn-success shadow-sm">
        <i class="bi bi-plus-lg me-1"></i> Add Expense
    </a>
</div>

{{-- KPI cards --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <span class="text-muted small d-block">Total Expenses Incurred</span>
                <h3 class="fw-bold mb-0 text-danger">₹{{ number_format($totalExpenses, 2) }}</h3>
            </div>
            <div class="kpi-icon-box bg-soft-danger"><i class="bi bi-wallet2 fs-4 text-danger"></i></div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-crm align-middle mb-0">
            <thead>
                <tr>
                    <th>Expense No</th>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Project</th>
                    <th>Vendor / Payee</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $exp)
                <tr>
                    <td><a href="{{ route('expenses.show', $exp) }}" class="fw-semibold text-success text-decoration-none">{{ $exp->expense_number }}</a></td>
                    <td class="small">{{ $exp->expense_date?->format('d M Y') }}</td>
                    <td><span class="badge bg-light text-dark border">{{ $exp->category }}</span></td>
                    <td class="small">{{ $exp->project?->name ?? 'General / Overhead' }}</td>
                    <td class="small">{{ $exp->vendor?->name ?? '—' }}</td>
                    <td class="fw-bold text-danger">₹{{ number_format($exp->amount, 2) }}</td>
                    <td><span class="badge bg-success">{{ $exp->status }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('expenses.show', $exp) }}" class="btn btn-sm btn-outline-success"><i class="bi bi-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <i class="bi bi-wallet2 fs-1 text-muted d-block mb-2"></i>
                        <span class="text-muted">No expenses recorded. <a href="{{ route('expenses.create') }}">Add an expense</a>.</span>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($expenses->hasPages())
    <div class="card-footer bg-white py-3">{{ $expenses->withQueryString()->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
