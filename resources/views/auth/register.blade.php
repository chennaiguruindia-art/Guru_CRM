@extends('layouts.auth')

@section('title', 'Create First Account')

@section('content')
<div class="mb-4">
    <h5 class="fw-bold text-dark mb-1">Create the first account</h5>
    <p class="text-muted small mb-0">
        This becomes the <strong>Super Admin</strong> — full control, and the
        only account that can never be handed out. Once it exists, sign-up
        closes for good and Admins create the rest from
        <em>User Management</em>.
    </p>
</div>

<form method="POST" action="{{ route('register') }}">
    @csrf

    <!-- Name -->
    <div class="mb-3">
        <label for="name" class="form-label small fw-semibold text-secondary">Full Name</label>
        <div class="input-group">
            <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-person"></i></span>
            <input id="name" type="text" class="form-control border-start-0 @error('name') is-invalid @enderror"
                   name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
        </div>
        @error('name')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <!-- Email -->
    <div class="mb-3">
        <label for="email" class="form-label small fw-semibold text-secondary">Email Address</label>
        <div class="input-group">
            <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-envelope"></i></span>
            <input id="email" type="email" class="form-control border-start-0 @error('email') is-invalid @enderror"
                   name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="you@company.com">
        </div>
        @error('email')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <!-- Password -->
    <div class="mb-3">
        <label for="password" class="form-label small fw-semibold text-secondary">Password</label>
        <div class="input-group">
            <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-shield-lock"></i></span>
            <input id="password" type="password" class="form-control border-start-0 @error('password') is-invalid @enderror"
                   name="password" required autocomplete="new-password" placeholder="At least 8 characters">
        </div>
        @error('password')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <!-- Confirm Password -->
    <div class="mb-4">
        <label for="password_confirmation" class="form-label small fw-semibold text-secondary">Confirm Password</label>
        <div class="input-group">
            <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-shield-check"></i></span>
            <input id="password_confirmation" type="password" class="form-control border-start-0"
                   name="password_confirmation" required autocomplete="new-password" placeholder="Repeat it">
        </div>
    </div>

    <!-- Submit -->
    <div class="d-grid mb-3">
        <button type="submit" class="btn btn-success py-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
            <span>Create Super Admin Account</span>
            <i class="bi bi-arrow-right"></i>
        </button>
    </div>

    <div class="text-center small">
        <a class="text-success text-decoration-none" href="{{ route('login') }}">
            <i class="bi bi-arrow-left me-1"></i> Back to Sign In
        </a>
    </div>
</form>
@endsection
