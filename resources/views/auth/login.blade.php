@extends('layouts.auth')

@section('title', 'Sign In — Employee Punch & Management CRM')

@section('content')
@php
    $isAdminActive = $errors->has('phone') || $errors->has('password');
@endphp

<!-- Dual Login Tabs -->
<ul class="nav nav-pills nav-fill mb-4 p-1 bg-light rounded-pill border shadow-sm" id="loginTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link rounded-pill py-2 fw-semibold {{ !$isAdminActive ? 'active bg-success text-white' : 'text-secondary' }}" 
                id="employee-tab" data-bs-toggle="pill" data-bs-target="#employee-login-pane" type="button" role="tab"
                aria-controls="employee-login-pane" aria-selected="{{ !$isAdminActive ? 'true' : 'false' }}">
            <i class="bi bi-fingerprint me-1"></i> Employee Punch
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link rounded-pill py-2 fw-semibold {{ $isAdminActive ? 'active bg-success text-white' : 'text-secondary' }}" 
                id="admin-tab" data-bs-toggle="pill" data-bs-target="#admin-login-pane" type="button" role="tab"
                aria-controls="admin-login-pane" aria-selected="{{ $isAdminActive ? 'true' : 'false' }}">
            <i class="bi bi-shield-lock me-1"></i> Admin / CRM
        </button>
    </li>
</ul>

<div class="tab-content" id="loginTabsContent">

    <!-- ========================================== -->
    <!-- 1. EMPLOYEE HRM LOGIN (BY EMPLOYEE ID)     -->
    <!-- ========================================== -->
    <div class="tab-pane fade {{ !$isAdminActive ? 'show active' : '' }}" id="employee-login-pane" role="tabpanel" aria-labelledby="employee-tab">
        <div class="text-center mb-3">
            <h5 class="fw-bold text-dark mb-1">Employee Attendance Login</h5>
            <p class="text-muted small mb-0">Enter your Employee ID to punch Check-In / Check-Out.</p>
        </div>

        <form method="POST" action="{{ route('employee.login') }}">
            @csrf

            <div class="mb-3">
                <label for="employee_code" class="form-label small fw-semibold text-secondary">Employee ID Number <span class="text-danger">*</span></label>
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-light text-success border-end-0"><i class="bi bi-person-badge-fill fs-5"></i></span>
                    <input id="employee_code" type="text" 
                           class="form-control border-start-0 font-monospace fw-semibold @error('employee_code') is-invalid @enderror" 
                           name="employee_code" value="{{ old('employee_code') }}" 
                           placeholder="EMP-0001 or EMP-0003" required autofocus autocomplete="off">
                </div>
                <div class="form-text small">Enter your Employee ID (e.g. <code>EMP-0001</code>). No password needed.</div>
                @error('employee_code')
                    <div class="text-danger small mt-1"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                @enderror
            </div>

            <div class="d-grid mb-3 pt-2">
                <button type="submit" class="btn btn-success py-3 fw-bold rounded-3 shadow-sm d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-box-arrow-in-right fs-5"></i>
                    <span>Continue to Attendance Punch</span>
                    <i class="bi bi-arrow-right ms-auto"></i>
                </button>
            </div>

            <div class="text-center">
                <span class="text-muted small">Need an Employee ID? Contact your Branch Admin.</span>
            </div>
        </form>
    </div>

    <!-- ========================================== -->
    <!-- 2. MANAGEMENT & CRM LOGIN (ADMIN / STAFF)  -->
    <!-- ========================================== -->
    <div class="tab-pane fade {{ $isAdminActive ? 'show active' : '' }}" id="admin-login-pane" role="tabpanel" aria-labelledby="admin-tab">
        <div class="text-center mb-3">
            <h5 class="fw-bold text-dark mb-1">CRM Management Sign In</h5>
            <p class="text-muted small mb-0">For Admins, Branch Managers and Sales Marketers.</p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="needs-validation">
            @csrf

            <!-- Mobile or Username -->
            <div class="mb-3">
                <label for="phone" class="form-label small fw-semibold text-secondary">Mobile Number or Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-person-fill"></i></span>
                    <input id="phone" type="text" class="form-control border-start-0 @error('phone') is-invalid @enderror" 
                           name="phone" value="{{ old('phone') }}" required autocomplete="username" placeholder="98765 43210 or admin">
                </div>
                @error('phone')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password -->
            <div class="mb-3">
                <label for="password" class="form-label small fw-semibold text-secondary">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-shield-lock-fill"></i></span>
                    <input id="password" type="password" class="form-control border-start-0 @error('password') is-invalid @enderror" 
                           name="password" required autocomplete="current-password" placeholder="••••••••">
                </div>
                @error('password')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="mb-3 form-check">
                <input id="remember_me" type="checkbox" class="form-check-input" name="remember" {{ old('remember', true) ? 'checked' : '' }}>
                <label for="remember_me" class="form-check-label small text-secondary">
                    Keep me logged in
                </label>
            </div>

            <!-- Submit Button -->
            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-dark py-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
                    <span>Sign In to CRM</span>
                    <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </form>
    </div>

</div>

<!-- Shown only until the very first account exists -->
@if (! empty($needsBootstrap))
    <div class="p-3 bg-light rounded border small mt-4">
        <div class="fw-semibold text-dark mb-1 d-flex align-items-center gap-1">
            <i class="bi bi-person-plus-fill text-success"></i> No accounts yet
        </div>
        <div class="text-muted mb-2" style="font-size: 0.8rem;">
            Sign-up is open just long enough to create the first account. It becomes <strong>Super Admin</strong>.
        </div>
        <a href="{{ route('register') }}" class="btn btn-success btn-sm w-100 fw-semibold">
            <i class="bi bi-rocket-takeoff me-1"></i> Create the first account
        </a>
    </div>
@endif

<script>
    // Tab active styling switcher
    document.addEventListener('DOMContentLoaded', function () {
        const tabs = document.querySelectorAll('#loginTabs button[data-bs-toggle="pill"]');
        tabs.forEach(tab => {
            tab.addEventListener('shown.bs.tab', function (e) {
                tabs.forEach(t => {
                    t.classList.remove('bg-success', 'text-white');
                    t.classList.add('text-secondary');
                });
                e.target.classList.add('bg-success', 'text-white');
                e.target.classList.remove('text-secondary');
            });
        });
    });
</script>
@endsection
