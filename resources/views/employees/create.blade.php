@extends('layouts.app')
@section('title', 'Add Employee')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-person-badge-fill text-success me-2"></i>Add Employee</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-0"><li class="breadcrumb-item"><a href="{{ route('employees.index') }}" class="text-success text-decoration-none">Employees</a></li><li class="breadcrumb-item active">Add</li></ol></nav>
    </div>
    <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<form method="POST" action="{{ route('employees.store') }}">
    @csrf
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-person text-success me-2"></i>Personal & Job Information</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Phone <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Designation <span class="text-danger">*</span></label>
                            <input type="text" name="designation" class="form-control" placeholder="e.g. Garden Supervisor, Horticulturist" value="{{ old('designation') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Department <span class="text-danger">*</span></label>
                            <select name="department" class="form-select" required>
                                @foreach(['Operations', 'Maintenance', 'Horticulture', 'Sales', 'Accounts', 'Administration'] as $d)
                                    <option value="{{ $d }}">{{ $d }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Branch</label>
                            <select name="branch_id" class="form-select">
                                <option value="">— No branch —</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" {{ (int) old('branch_id') === $b->id ? 'selected' : '' }}>
                                        {{ $b->code }} · {{ $b->name }}@if($b->location) — {{ $b->location }}@endif
                                    </option>
                                @endforeach
                            </select>
                            @if($branches->isEmpty())
                                <div class="form-text">No branches yet — <a href="{{ route('branches.create') }}">create one first</a>.</div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Employment Type</label>
                            <select name="employee_type" class="form-select">
                                @foreach(['Full-Time', 'Contract', 'Daily Wage', 'Part-Time'] as $et)
                                    <option value="{{ $et }}">{{ $et }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Monthly Salary / Rate (₹)</label>
                            <input type="number" step="0.01" name="salary" class="form-control" value="{{ old('salary', 0) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Date of Joining</label>
                            <input type="date" name="joining_date" class="form-control" value="{{ old('joining_date', date('Y-m-d')) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Link to an Existing Account</label>
                            <select name="user_id" class="form-select">
                                <option value="">— None, create a new one below —</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ (int) old('user_id') === $u->id ? 'selected' : '' }}>
                                        {{ $u->name }} ({{ $u->username ?? $u->email }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Leave this empty to have a fresh login created automatically.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Address</label>
                            <textarea name="address" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-key text-success me-2"></i>Login Access</h6>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="create_login" value="1" id="create_login"
                               {{ old('create_login', '1') === '1' ? 'checked' : '' }}>
                        <label class="form-check-label small" for="create_login">Create a login</label>
                    </div>
                </div>
                <div class="card-body p-4" id="login-fields">
                    <p class="small text-muted mb-3">
                        The login <strong>username is this person's Employee ID</strong> (generated as
                        <code>EMP-0001</code> and shown to you once on save). They sign in with it instead of an email.
                    </p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Role <span class="text-danger">*</span></label>
                            <select name="role" class="form-select @error('role') is-invalid @enderror" id="login-role">
                                <option value="">— Select a role —</option>
                                @foreach($roles as $r)
                                    <option value="{{ $r->slug }}" {{ old('role') === $r->slug ? 'selected' : '' }}>
                                        {{ $r->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text">This decides what the person can see and do.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Starting Password</label>
                            <input type="text" name="password" class="form-control @error('password') is-invalid @enderror"
                                   value="{{ old('password') }}" minlength="6" autocomplete="new-password"
                                   placeholder="Leave blank to generate one">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text">Minimum 6 characters. Blank = a random password shown to you once.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success py-2 px-4 fw-semibold">
                    <i class="bi bi-check2-circle me-1"></i> Save Employee
                </button>
                <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
    </div>
</form>

<script>
    // Show / hide the login provisioning fields with the switch.
    (function () {
        const toggle = document.getElementById('create_login');
        const box = document.getElementById('login-fields');
        if (!toggle || !box) return;

        const sync = () => {
            const on = toggle.checked;
            box.style.display = on ? '' : 'none';
            box.querySelectorAll('input, select').forEach(el => { el.disabled = !on; });
            const role = document.getElementById('login-role');
            if (role) role.required = on;
        };

        toggle.addEventListener('change', sync);
        sync();
    })();
</script>
@endsection
