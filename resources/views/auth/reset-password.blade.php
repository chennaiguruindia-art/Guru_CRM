@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
<form method="POST" action="{{ route('password.store') }}">
    @csrf

    <!-- Password Reset Token -->
    <input type="hidden" name="token" value="{{ $request->route('token') }}">

    <!-- Email Address -->
    <div class="mb-3">
        <label for="email" class="form-label small fw-semibold text-secondary">Email Address</label>
        <div class="input-group">
            <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-envelope"></i></span>
            <input id="email" type="email" class="form-control border-start-0 @error('email') is-invalid @enderror" name="email" value="{{ old('email', $request->email) }}" required autofocus placeholder="name@company.com">
        </div>
        @error('email')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <!-- Password -->
    <div class="mb-3">
        <label for="password" class="form-label small fw-semibold text-secondary">New Password</label>
        <div class="input-group">
            <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-shield-lock"></i></span>
            <input id="password" type="password" class="form-control border-start-0 @error('password') is-invalid @enderror" name="password" required placeholder="••••••••">
        </div>
        @error('password')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <!-- Confirm Password -->
    <div class="mb-3">
        <label for="password_confirmation" class="form-label small fw-semibold text-secondary">Confirm Password</label>
        <div class="input-group">
            <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-shield-lock"></i></span>
            <input id="password_confirmation" type="password" class="form-control border-start-0" name="password_confirmation" required placeholder="••••••••">
        </div>
    </div>

    <div class="d-grid mb-3">
        <button type="submit" class="btn btn-success py-2 fw-semibold">
            Reset Password
        </button>
    </div>
</form>
@endsection
