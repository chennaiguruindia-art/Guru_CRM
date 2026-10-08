@extends('layouts.auth')

@section('title', 'Sign In')

@section('content')
<form method="POST" action="{{ route('login') }}" class="needs-validation">
    @csrf

    <!-- Login ID (email or Employee ID) -->
    <div class="mb-3">
        <label for="email" class="form-label small fw-semibold text-secondary">Login ID</label>
        <div class="input-group">
            <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-person"></i></span>
            <input id="email" type="text" class="form-control border-start-0 @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="EMP-0001 or name@company.com">
        </div>
        <div class="form-text">Use your Employee ID or your email address.</div>
        @error('email')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <!-- Password -->
    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label for="password" class="form-label small fw-semibold text-secondary mb-0">Password</label>
            @if (Route::has('password.request'))
                <a class="small text-success text-decoration-none" href="{{ route('password.request') }}">
                    Forgot Password?
                </a>
            @endif
        </div>
        <div class="input-group">
            <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-shield-lock"></i></span>
            <input id="password" type="password" class="form-control border-start-0 @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="••••••••">
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
        <button type="submit" class="btn btn-success py-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
            <span>Sign In to CRM</span>
            <i class="bi bi-arrow-right"></i>
        </button>
    </div>

    <!-- Shown only until the very first account exists -->
    @if (! empty($needsBootstrap))
        <div class="p-3 bg-light rounded border small">
            <div class="fw-semibold text-dark mb-1 d-flex align-items-center gap-1">
                <i class="bi bi-person-plus-fill text-success"></i> No accounts yet
            </div>
            <div class="text-muted mb-2" style="font-size: 0.8rem;">
                Sign-up is open just long enough to create the first account.
                It becomes <strong>Super Admin</strong>, and after that only an
                Admin can create users.
            </div>
            <a href="{{ route('register') }}" class="btn btn-success btn-sm w-100 fw-semibold">
                <i class="bi bi-rocket-takeoff me-1"></i> Create the first account
            </a>
        </div>
    @endif
</form>
@endsection
