@extends('layouts.auth')

@section('title', 'Access Denied')

@section('content')
<div class="text-center py-2">
    <div class="d-inline-flex align-items-center justify-content-center mb-3"
         style="width:64px;height:64px;border-radius:16px;background:linear-gradient(135deg,#f59e0b,#ef4444);color:#fff;font-size:1.75rem;">
        <i class="bi bi-shield-lock-fill"></i>
    </div>

    <h5 class="fw-bold text-dark mb-2">You don't have access to this page</h5>

    <p class="text-muted small mb-3">
        @isset($exception)
            {{ $exception->getMessage() ?: "Your role doesn't include the permission needed here." }}
        @else
            Your role doesn't include the permission needed here.
        @endisset
    </p>

    @auth
        <p class="text-muted small mb-4">
            Signed in as <strong>{{ auth()->user()->name }}</strong>.
            Ask the Super Admin to grant the permission from
            <em>Roles &amp; Permissions</em> if you need it.
        </p>

        <div class="d-grid gap-2">
            <a href="{{ route('dashboard') }}" class="btn btn-success fw-semibold">
                <i class="bi bi-grid-1x2-fill me-1"></i> Back to Dashboard
            </a>
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Go back
            </a>
        </div>
    @else
        <div class="d-grid">
            <a href="{{ route('login') }}" class="btn btn-success fw-semibold">
                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
            </a>
        </div>
    @endauth
</div>
@endsection
