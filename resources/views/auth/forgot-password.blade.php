@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
<div class="mb-3 text-secondary small">
    Forgot your password? No problem. Just enter your registered email address and we will email you a password reset link.
</div>

<form method="POST" action="{{ route('password.email') }}">
    @csrf

    <!-- Email Address -->
    <div class="mb-3">
        <label for="email" class="form-label small fw-semibold text-secondary">Email Address</label>
        <div class="input-group">
            <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-envelope"></i></span>
            <input id="email" type="email" class="form-control border-start-0 @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autofocus placeholder="name@company.com">
        </div>
        @error('email')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="d-grid mb-3">
        <button type="submit" class="btn btn-success py-2 fw-semibold">
            Send Password Reset Link
        </button>
    </div>

    <div class="text-center">
        <a href="{{ route('login') }}" class="small text-decoration-none text-muted">
            <i class="bi bi-arrow-left me-1"></i> Back to Sign In
        </a>
    </div>
</form>
@endsection
