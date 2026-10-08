@extends('layouts.app')
@section('title', 'Expense — ' . $expense->expense_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-wallet2 text-success me-2"></i>{{ $expense->expense_number }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-0"><li class="breadcrumb-item"><a href="{{ route('expenses.index') }}" class="text-success text-decoration-none">Expenses</a></li><li class="breadcrumb-item active">{{ $expense->expense_number }}</li></ol></nav>
    </div>
    <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle text-success me-2"></i>Expense Voucher</h6>
        <span class="badge bg-success">{{ $expense->status }}</span>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-sm-6"><label class="text-muted small d-block">Category</label><span class="badge bg-light text-dark border">{{ $expense->category }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Amount</label><span class="fw-bold text-danger fs-5">₹{{ number_format($expense->amount, 2) }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Expense Date</label><span>{{ $expense->expense_date?->format('d M Y') }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Project</label><span>{{ $expense->project?->name ?? 'General / Overhead' }}</span></div>
            <div class="col-sm-6"><label class="text-muted small d-block">Vendor / Payee</label><span>{{ $expense->vendor?->name ?? '—' }}</span></div>
            @if($expense->description)
            <div class="col-12"><label class="text-muted small d-block">Description</label><div class="bg-light p-3 rounded small">{{ $expense->description }}</div></div>
            @endif
        </div>
    </div>
</div>
@endsection
