@if(session('login_credentials'))
    <div class="alert alert-info alert-dismissible fade show mb-3 shadow-sm border-start border-primary border-3" role="alert">
        <div class="d-flex align-items-center gap-2 mb-1">
            <i class="bi bi-key-fill fs-5"></i>
            <strong>Login created — pass these on now</strong>
        </div>
        <div class="small mb-2 text-muted">This is the only time the password is shown.</div>
        <div class="d-flex flex-wrap gap-4 small">
            <span>Login ID: <code class="user-select-all bg-white border">{{ session('login_credentials.username') }}</code></span>
            <span>Password: <code class="user-select-all bg-white border">{{ session('login_credentials.password') }}</code></span>
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-3 shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill fs-5"></i>
        <div>{{ session('success') }}</div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-3 shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
        <div>{{ session('error') }}</div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center gap-2 mb-3 shadow-sm" role="alert">
        <i class="bi bi-exclamation-circle-fill fs-5"></i>
        <div>{{ session('warning') }}</div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('status'))
    <div class="alert alert-info alert-dismissible fade show d-flex align-items-center gap-2 mb-3 shadow-sm" role="alert">
        <i class="bi bi-info-circle-fill fs-5"></i>
        <div>{{ session('status') }}</div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-3 shadow-sm" role="alert">
        <div class="d-flex align-items-center gap-2 mb-1">
            <i class="bi bi-x-octagon-fill fs-5"></i>
            <strong>Please fix the following validation errors:</strong>
        </div>
        <ul class="mb-0 ps-3 small">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
