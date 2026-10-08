@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold text-dark">
                <i class="bi bi-person-gear me-2 text-primary"></i>Edit User
            </h1>
            <p class="text-muted mb-0 small">Update {{ $user->name }}'s details, role and branch</p>
        </div>
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Users
        </a>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Validation Errors Summary --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <div class="d-flex align-items-start gap-2">
                <i class="bi bi-exclamation-triangle-fill mt-1 flex-shrink-0"></i>
                <div>
                    <strong>Please fix the following errors:</strong>
                    <ul class="mb-0 mt-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8 col-xl-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                    <span class="fw-semibold">
                        <i class="bi bi-person-vcard me-2 text-primary"></i>User Details
                    </span>
                    @if($user->employee)
                        <span class="badge bg-light text-dark border">
                            <i class="bi bi-card-list me-1"></i>{{ $user->employee->employee_code }}
                        </span>
                    @endif
                </div>

                <div class="card-body p-4">
                    <form
                        method="POST"
                        action="{{ route('users.update', $user) }}"
                        id="editUserForm"
                        novalidate
                    >
                        @csrf
                        @method('PUT')

                        {{-- Full Name --}}
                        <div class="mb-4">
                            <label for="name" class="form-label fw-semibold">
                                Full Name <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="bi bi-person text-muted"></i>
                                </span>
                                <input
                                    type="text"
                                    id="name"
                                    name="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    placeholder="e.g. Ravi Kumar"
                                    value="{{ old('name', $user->name) }}"
                                    required
                                    autofocus
                                >
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Email --}}
                        <div class="mb-4">
                            <label for="email" class="form-label fw-semibold">
                                Email Address <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="bi bi-envelope text-muted"></i>
                                </span>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    placeholder="e.g. ravi@example.com"
                                    value="{{ old('email', $user->email) }}"
                                    required
                                >
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Phone --}}
                        <div class="mb-4">
                            <label for="phone" class="form-label fw-semibold">
                                Phone Number
                                <span class="text-muted fw-normal small">(optional)</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="bi bi-telephone text-muted"></i>
                                </span>
                                <input
                                    type="text"
                                    id="phone"
                                    name="phone"
                                    class="form-control @error('phone') is-invalid @enderror"
                                    placeholder="e.g. +91 98765 43210"
                                    value="{{ old('phone', $user->phone) }}"
                                >
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Status --}}
                        <div class="mb-4">
                            <label for="status" class="form-label fw-semibold">
                                Status <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="bi bi-toggle-on text-muted"></i>
                                </span>
                                <select
                                    id="status"
                                    name="status"
                                    class="form-select @error('status') is-invalid @enderror"
                                    required
                                >
                                    <option value="active" {{ old('status', $user->status ?? 'active') === 'active' ? 'selected' : '' }}>
                                        Active — can sign in and be assigned work
                                    </option>
                                    <option value="inactive" {{ old('status', $user->status ?? 'active') === 'inactive' ? 'selected' : '' }}>
                                        Inactive — hidden from assignment dropdowns
                                    </option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            @if(auth()->id() === $user->id)
                                <div class="form-text text-muted">
                                    <i class="bi bi-lock me-1"></i>Your own account always stays Active.
                                </div>
                            @endif
                        </div>

                        {{-- Password (optional on edit) --}}
                        <div class="mb-2">
                            <label for="password" class="form-label fw-semibold">
                                New Password
                                <span class="text-muted fw-normal small">(leave blank to keep the current one)</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="bi bi-lock text-muted"></i>
                                </span>
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    placeholder="Minimum 6 characters"
                                    minlength="6"
                                    autocomplete="new-password"
                                >
                                <button
                                    class="btn btn-outline-secondary"
                                    type="button"
                                    id="togglePassword"
                                    title="Show/hide password"
                                >
                                    <i class="bi bi-eye" id="togglePasswordIcon"></i>
                                </button>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Password Strength Indicator --}}
                        <div class="mb-4">
                            <div class="progress mb-1" style="height:4px;border-radius:4px">
                                <div
                                    id="passwordStrengthBar"
                                    class="progress-bar"
                                    role="progressbar"
                                    style="width:0%;transition:width .3s"
                                ></div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <small id="passwordStrengthText" class="text-muted">
                                    Password strength will appear here
                                </small>
                                <small class="text-muted ms-auto">
                                    <i class="bi bi-info-circle me-1"></i>Min. 6 characters recommended
                                </small>
                            </div>
                        </div>

                        {{-- Role --}}
                        <div class="mb-4">
                            <label for="role_id" class="form-label fw-semibold">
                                Role <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="bi bi-shield-check text-muted"></i>
                                </span>
                                <select
                                    id="role_id"
                                    name="role_id"
                                    class="form-select @error('role_id') is-invalid @enderror"
                                    {{ auth()->id() === $user->id ? 'disabled' : '' }}
                                    required
                                >
                                    @foreach($roles as $role)
                                        <option
                                            value="{{ $role->id }}"
                                            {{ (int) old('role_id', $currentRole?->id) === $role->id ? 'selected' : '' }}
                                        >
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                    {{-- A held role outside ASSIGNABLE_ROLES (Super Admin) still has to render. --}}
                                    @if($currentRole && ! $roles->contains('id', $currentRole->id))
                                        <option value="{{ $currentRole->id }}" selected>
                                            {{ $currentRole->name }}
                                        </option>
                                    @endif
                                </select>
                                @error('role_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text text-muted">
                                @if(auth()->id() === $user->id)
                                    <i class="bi bi-lock me-1"></i>You cannot change your own role.
                                @else
                                    <i class="bi bi-info-circle me-1"></i>
                                    The selected role determines what this user can access in the system.
                                @endif
                            </div>
                            {{-- Disabled selects are not submitted — mirror the value so it still posts. --}}
                            @if(auth()->id() === $user->id)
                                <input type="hidden" name="role_id" value="{{ old('role_id', $currentRole?->id) }}">
                            @endif
                        </div>

                        {{-- Branch --}}
                        <div class="mb-4">
                            <label for="branch_id" class="form-label fw-semibold">
                                Branch
                                <span class="text-muted fw-normal small">(optional)</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="bi bi-diagram-3 text-muted"></i>
                                </span>
                                <select id="branch_id" name="branch_id" class="form-select @error('branch_id') is-invalid @enderror">
                                    <option value="">— No branch —</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}"
                                            {{ (int) old('branch_id', $user->employee?->branch_id) === $branch->id ? 'selected' : '' }}>
                                            {{ $branch->code }} · {{ $branch->name }}@if($branch->location) — {{ $branch->location }}@endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('branch_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text text-muted">
                                @if($branches->isEmpty())
                                    <i class="bi bi-info-circle me-1"></i>No branches yet — <a href="{{ route('branches.create') }}">create one first</a>.
                                @else
                                    <i class="bi bi-info-circle me-1"></i>Sets which branch this person is assigned to.
                                @endif
                            </div>
                        </div>

                        <hr class="my-4">

                        {{-- Form Actions --}}
                        <div class="d-flex align-items-center gap-2 justify-content-end">
                            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary px-4" id="submitBtn">
                                <i class="bi bi-check2-circle me-2"></i>Save Changes
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    // ── Password Visibility Toggle ───────────────────────────────────────────
    const toggleBtn  = document.getElementById('togglePassword');
    const pwdInput   = document.getElementById('password');
    const toggleIcon = document.getElementById('togglePasswordIcon');

    if (toggleBtn && pwdInput) {
        toggleBtn.addEventListener('click', function () {
            const isText = pwdInput.type === 'text';
            pwdInput.type = isText ? 'password' : 'text';
            toggleIcon.className = isText ? 'bi bi-eye' : 'bi bi-eye-slash';
        });
    }

    // ── Password Strength Meter ──────────────────────────────────────────────
    const bar  = document.getElementById('passwordStrengthBar');
    const text = document.getElementById('passwordStrengthText');

    function getStrength(pwd) {
        let score = 0;
        if (pwd.length >= 6)  score++;
        if (pwd.length >= 10) score++;
        if (/[A-Z]/.test(pwd)) score++;
        if (/[0-9]/.test(pwd)) score++;
        if (/[^A-Za-z0-9]/.test(pwd)) score++;
        return score;
    }

    const levels = [
        { label: 'Very Weak',  pct: 20,  cls: 'bg-danger' },
        { label: 'Weak',       pct: 40,  cls: 'bg-warning' },
        { label: 'Fair',       pct: 60,  cls: 'bg-info' },
        { label: 'Strong',     pct: 80,  cls: 'bg-success' },
        { label: 'Very Strong',pct: 100, cls: 'bg-success' }
    ];

    if (pwdInput && bar && text) {
        pwdInput.addEventListener('input', function () {
            const val = this.value;
            if (!val) {
                bar.style.width = '0%';
                bar.className   = 'progress-bar';
                text.textContent = 'Password strength will appear here';
                text.className   = 'text-muted';
                return;
            }
            const score = Math.min(getStrength(val), 5);
            const lvl   = levels[score - 1] || levels[0];
            bar.style.width = lvl.pct + '%';
            bar.className   = 'progress-bar ' + lvl.cls;
            text.textContent = lvl.label;
            text.className   = 'fw-semibold ' + (score >= 3 ? 'text-success' : score === 2 ? 'text-warning' : 'text-danger');
        });
    }

    // ── Submit Button Loader ─────────────────────────────────────────────────
    const form      = document.getElementById('editUserForm');
    const submitBtn = document.getElementById('submitBtn');

    if (form && submitBtn) {
        form.addEventListener('submit', function () {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving…';
        });
    }
})();
</script>
@endpush
