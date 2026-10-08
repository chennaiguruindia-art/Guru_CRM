@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="container-fluid px-4 py-3">

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="fw-bold mb-0">
                <i class="bi bi-gear me-2 text-primary"></i>CRM Settings
            </h2>
            <nav aria-label="breadcrumb" class="mt-1">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Settings</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Settings Form Card --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom p-0">
            {{-- Nav Tabs --}}
            <ul class="nav nav-tabs border-0 px-3 pt-3" id="settingsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active d-flex align-items-center gap-2"
                            id="company-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#company"
                            type="button"
                            role="tab"
                            aria-controls="company"
                            aria-selected="true">
                        <i class="bi bi-building"></i> Company
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link d-flex align-items-center gap-2"
                            id="branding-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#branding"
                            type="button"
                            role="tab"
                            aria-controls="branding"
                            aria-selected="false">
                        <i class="bi bi-palette"></i> Branding
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link d-flex align-items-center gap-2"
                            id="modules-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#modules"
                            type="button"
                            role="tab"
                            aria-controls="modules"
                            aria-selected="false">
                        <i class="bi bi-toggle-on"></i> Modules
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link d-flex align-items-center gap-2"
                            id="notifications-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#notifications"
                            type="button"
                            role="tab"
                            aria-controls="notifications"
                            aria-selected="false">
                        <i class="bi bi-bell"></i> Notifications
                    </button>
                </li>
            </ul>
        </div>

        <form method="POST"
              action="{{ route('settings.store') }}"
              enctype="multipart/form-data"
              id="settingsForm">
            @csrf

            <div class="card-body p-4">
                <div class="tab-content" id="settingsTabsContent">

                    {{-- ============================================================
                         TAB 1 — Company
                    ============================================================ --}}
                    <div class="tab-pane fade show active"
                         id="company"
                         role="tabpanel"
                         aria-labelledby="company-tab">

                        <h6 class="fw-semibold text-muted text-uppercase small mb-3 border-bottom pb-2">
                            <i class="bi bi-building me-1"></i> Company Details
                        </h6>

                        <div class="row g-3">

                            {{-- Company Name --}}
                            <div class="col-md-6">
                                <label for="company_name" class="form-label fw-semibold small">
                                    Company Name
                                </label>
                                <input type="text"
                                       class="form-control @error('company_name') is-invalid @enderror"
                                       id="company_name"
                                       name="company_name"
                                       value="{{ old('company_name', $settings['company_name'] ?? '') }}"
                                       placeholder="e.g. Green Thumb Horticulture Ltd.">
                                @error('company_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Company Phone --}}
                            <div class="col-md-6">
                                <label for="company_phone" class="form-label fw-semibold small">
                                    Company Phone
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                    <input type="text"
                                           class="form-control @error('company_phone') is-invalid @enderror"
                                           id="company_phone"
                                           name="company_phone"
                                           value="{{ old('company_phone', $settings['company_phone'] ?? '') }}"
                                           placeholder="+91 98765 43210">
                                </div>
                                @error('company_phone')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Company Email --}}
                            <div class="col-md-6">
                                <label for="company_email" class="form-label fw-semibold small">
                                    Company Email
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email"
                                           class="form-control @error('company_email') is-invalid @enderror"
                                           id="company_email"
                                           name="company_email"
                                           value="{{ old('company_email', $settings['company_email'] ?? '') }}"
                                           placeholder="info@company.com">
                                </div>
                                @error('company_email')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- GST Number --}}
                            <div class="col-md-3">
                                <label for="company_gst" class="form-label fw-semibold small">
                                    GST Number
                                </label>
                                <input type="text"
                                       class="form-control @error('company_gst') is-invalid @enderror"
                                       id="company_gst"
                                       name="company_gst"
                                       value="{{ old('company_gst', $settings['company_gst'] ?? '') }}"
                                       placeholder="22AAAAA0000A1Z5"
                                       maxlength="15">
                                @error('company_gst')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- PAN Number --}}
                            <div class="col-md-3">
                                <label for="company_pan" class="form-label fw-semibold small">
                                    PAN Number
                                </label>
                                <input type="text"
                                       class="form-control @error('company_pan') is-invalid @enderror"
                                       id="company_pan"
                                       name="company_pan"
                                       value="{{ old('company_pan', $settings['company_pan'] ?? '') }}"
                                       placeholder="AAAAA9999A"
                                       maxlength="10">
                                @error('company_pan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Company Address --}}
                            <div class="col-12">
                                <label for="company_address" class="form-label fw-semibold small">
                                    Company Address
                                </label>
                                <textarea class="form-control @error('company_address') is-invalid @enderror"
                                          id="company_address"
                                          name="company_address"
                                          rows="3"
                                          placeholder="Full registered address...">{{ old('company_address', $settings['company_address'] ?? '') }}</textarea>
                                @error('company_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>{{-- /row --}}
                    </div>{{-- /tab-pane company --}}

                    {{-- ============================================================
                         TAB 2 — Branding
                    ============================================================ --}}
                    <div class="tab-pane fade"
                         id="branding"
                         role="tabpanel"
                         aria-labelledby="branding-tab">

                        <h6 class="fw-semibold text-muted text-uppercase small mb-3 border-bottom pb-2">
                            <i class="bi bi-palette me-1"></i> Branding &amp; Display
                        </h6>

                        <div class="row g-3">

                            {{-- Company Logo --}}
                            <div class="col-md-6">
                                <label for="company_logo" class="form-label fw-semibold small">
                                    Company Logo
                                </label>
                                <input type="file"
                                       class="form-control @error('company_logo') is-invalid @enderror"
                                       id="company_logo"
                                       name="company_logo"
                                       accept="image/png, image/jpeg, image/svg+xml">
                                <div class="form-text">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Accepted formats: PNG, JPG, SVG. Recommended size: 200×60 px. Max: 2 MB.
                                    Leaving this blank will keep the existing logo.
                                </div>
                                @error('company_logo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                {{-- Existing logo preview --}}
                                @if(!empty($settings['company_logo']))
                                <div class="mt-2">
                                    <span class="small text-muted d-block mb-1">Current Logo:</span>
                                    <img src="{{ asset('storage/' . $settings['company_logo']) }}"
                                         alt="Company Logo"
                                         class="img-thumbnail"
                                         style="max-height: 60px;">
                                </div>
                                @endif
                            </div>

                            {{-- Currency Symbol --}}
                            <div class="col-md-3">
                                <label for="currency_symbol" class="form-label fw-semibold small">
                                    Currency Symbol
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-currency-exchange"></i></span>
                                    <input type="text"
                                           class="form-control @error('currency_symbol') is-invalid @enderror"
                                           id="currency_symbol"
                                           name="currency_symbol"
                                           value="{{ old('currency_symbol', $settings['currency_symbol'] ?? '₹') }}"
                                           maxlength="5"
                                           placeholder="₹">
                                </div>
                                @error('currency_symbol')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Date Format --}}
                            <div class="col-md-3">
                                <label for="date_format" class="form-label fw-semibold small">
                                    Date Format
                                </label>
                                <select class="form-select @error('date_format') is-invalid @enderror"
                                        id="date_format"
                                        name="date_format">
                                    <option value="Y-m-d"
                                        {{ ($settings['date_format'] ?? '') === 'Y-m-d' ? 'selected' : '' }}>
                                        Y-m-d &nbsp;(2025-01-31)
                                    </option>
                                    <option value="d-m-Y"
                                        {{ ($settings['date_format'] ?? '') === 'd-m-Y' ? 'selected' : '' }}>
                                        d-m-Y &nbsp;(31-01-2025)
                                    </option>
                                    <option value="d/m/Y"
                                        {{ ($settings['date_format'] ?? 'd/m/Y') === 'd/m/Y' ? 'selected' : '' }}>
                                        d/m/Y &nbsp;(31/01/2025)
                                    </option>
                                </select>
                                @error('date_format')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </div>{{-- /tab-pane branding --}}

                    {{-- ============================================================
                         TAB 3 — Modules (global sidebar visibility)
                    ============================================================ --}}
                    <div class="tab-pane fade"
                         id="modules"
                         role="tabpanel"
                         aria-labelledby="modules-tab">

                        <h6 class="fw-semibold text-muted text-uppercase small mb-3 border-bottom pb-2">
                            <i class="bi bi-toggle-on me-1"></i> Global Module Visibility
                        </h6>

                        <div class="alert alert-info d-flex align-items-start gap-2 mb-4">
                            <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
                            <div class="small">
                                Every switch <strong>saves instantly</strong> — no Save button needed. Switch one off and
                                its group disappears from the sidebar for <strong>every user</strong> (Super Admin, Admin
                                and all other roles). The routes and data stay untouched, so switching it back on
                                restores the group immediately.
                            </div>
                        </div>

                        <div class="row g-3">
                            @foreach ($modules as $module)
                                <div class="col-md-6 col-xl-4">
                                    <div class="border rounded p-3 h-100 bg-white">
                                        <div class="d-flex align-items-start justify-content-between gap-3">
                                            <div class="d-flex align-items-start gap-2">
                                                <span class="fs-5 text-primary mt-1">
                                                    <i class="bi {{ $module['icon'] }}"></i>
                                                </span>
                                                <div>
                                                    <div class="fw-semibold small">{{ $module['label'] }}</div>
                                                    <div class="text-muted" style="font-size: .78rem;">
                                                        {{ $module['description'] }}
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- hidden field guarantees the key is submitted when switched off --}}
                                            <input type="hidden" name="{{ $module['key'] }}" value="no">
                                            <div class="form-check form-switch m-0 flex-shrink-0 pt-1">
                                                <input class="form-check-input module-switch"
                                                       type="checkbox"
                                                       role="switch"
                                                       id="switch_{{ $module['key'] }}"
                                                       name="{{ $module['key'] }}"
                                                       value="yes"
                                                       style="cursor: pointer;"
                                                       {{ ($settings[$module['key']] ?? 'yes') === 'yes' ? 'checked' : '' }}>
                                            </div>
                                        </div>

                                        <div class="mt-2 small {{ ($settings[$module['key']] ?? 'yes') === 'yes' ? 'text-success' : 'text-danger' }}"
                                             id="state_{{ $module['key'] }}">
                                            <i class="bi bi-circle-fill me-1" style="font-size: .55rem; vertical-align: middle;"></i>
                                            {{ ($settings[$module['key']] ?? 'yes') === 'yes' ? 'Visible in sidebar' : 'Hidden from sidebar' }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="alert alert-warning d-flex align-items-start gap-2 mt-4 mb-0">
                            <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
                            <div class="small">
                                Pinned shortcuts to a hidden module disappear automatically and return when the module
                                is switched back on.
                            </div>
                        </div>

                    </div>{{-- /tab-pane modules --}}

                    {{-- ============================================================
                         TAB 4 — Notifications
                    ============================================================ --}}
                    <div class="tab-pane fade"
                         id="notifications"
                         role="tabpanel"
                         aria-labelledby="notifications-tab">

                        <h6 class="fw-semibold text-muted text-uppercase small mb-3 border-bottom pb-2">
                            <i class="bi bi-bell me-1"></i> Notification Channels
                        </h6>

                        <div class="row g-3">

                            {{-- Email Notifications --}}
                            <div class="col-md-4">
                                <label for="email_notifications" class="form-label fw-semibold small">
                                    <i class="bi bi-envelope me-1 text-primary"></i> Email Notifications
                                </label>
                                <select class="form-select @error('email_notifications') is-invalid @enderror"
                                        id="email_notifications"
                                        name="email_notifications">
                                    <option value="yes"
                                        {{ ($settings['email_notifications'] ?? 'yes') === 'yes' ? 'selected' : '' }}>
                                        ✅ Enabled
                                    </option>
                                    <option value="no"
                                        {{ ($settings['email_notifications'] ?? '') === 'no' ? 'selected' : '' }}>
                                        ❌ Disabled
                                    </option>
                                </select>
                                @error('email_notifications')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Send system alerts and reports via email.</div>
                            </div>

                            {{-- SMS Notifications --}}
                            <div class="col-md-4">
                                <label for="sms_notifications" class="form-label fw-semibold small">
                                    <i class="bi bi-phone me-1 text-success"></i> SMS Notifications
                                </label>
                                <select class="form-select @error('sms_notifications') is-invalid @enderror"
                                        id="sms_notifications"
                                        name="sms_notifications">
                                    <option value="yes"
                                        {{ ($settings['sms_notifications'] ?? '') === 'yes' ? 'selected' : '' }}>
                                        ✅ Enabled
                                    </option>
                                    <option value="no"
                                        {{ ($settings['sms_notifications'] ?? 'no') === 'no' ? 'selected' : '' }}>
                                        ❌ Disabled
                                    </option>
                                </select>
                                @error('sms_notifications')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Send SMS alerts for critical actions.</div>
                            </div>

                            {{-- WhatsApp Notifications --}}
                            <div class="col-md-4">
                                <label for="whatsapp_notifications" class="form-label fw-semibold small">
                                    <i class="bi bi-whatsapp me-1 text-success"></i> WhatsApp Notifications
                                </label>
                                <select class="form-select @error('whatsapp_notifications') is-invalid @enderror"
                                        id="whatsapp_notifications"
                                        name="whatsapp_notifications">
                                    <option value="yes"
                                        {{ ($settings['whatsapp_notifications'] ?? '') === 'yes' ? 'selected' : '' }}>
                                        ✅ Enabled
                                    </option>
                                    <option value="no"
                                        {{ ($settings['whatsapp_notifications'] ?? 'no') === 'no' ? 'selected' : '' }}>
                                        ❌ Disabled
                                    </option>
                                </select>
                                @error('whatsapp_notifications')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Send WhatsApp messages via integrated gateway.</div>
                            </div>

                        </div>

                        {{-- Notification Info Alert --}}
                        <div class="alert alert-info d-flex align-items-start gap-2 mt-4 mb-0">
                            <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
                            <div class="small">
                                Notification channels must also be configured with valid API credentials or SMTP settings
                                in your <code>.env</code> file for delivery to work.
                            </div>
                        </div>

                    </div>{{-- /tab-pane notifications --}}

                </div>{{-- /tab-content --}}
            </div>{{-- /card-body --}}

            {{-- Save Button Footer --}}
            <div class="card-footer bg-white border-top d-flex align-items-center justify-content-between py-3 px-4">
                <span class="text-muted small">
                    <i class="bi bi-shield-check me-1 text-success"></i>
                    All settings are stored securely in the database.
                </span>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-floppy me-2"></i>Save Settings
                </button>
            </div>

        </form>
    </div>{{-- /card --}}

</div>
@endsection

@push('scripts')
<script>
    (function () {
        var endpoint = @json(route('settings.module'));
        var token = @json(csrf_token());

        function setStatus(key, on) {
            var label = document.getElementById('state_' + key);
            if (!label) return;
            label.classList.toggle('text-success', on);
            label.classList.toggle('text-danger', !on);
            label.classList.toggle('text-muted', null);
            label.innerHTML = '<i class="bi bi-circle-fill me-1" style="font-size:.55rem;vertical-align:middle;"></i>' +
                (on ? 'Visible in sidebar' : 'Hidden from sidebar');
        }

        document.querySelectorAll('.module-switch').forEach(function (input) {
            var key = input.id.replace('switch_', '');
            input.addEventListener('change', function () {
                var on = input.checked;
                setStatus(key, on);
                input.disabled = true;

                fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ key: key, value: on ? 'yes' : 'no' })
                })
                    .then(function (res) {
                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        return res.json();
                    })
                    .then(function () {
                        showToast(on ? 'Module enabled — showing in sidebar.' : 'Module hidden from sidebar.', 'success');
                    })
                    .catch(function () {
                        // Roll the switch back so the UI never lies about saved state.
                        input.checked = !on;
                        setStatus(key, !on);
                        showToast('Could not save this switch. Please try again.', 'danger');
                    })
                    .finally(function () {
                        input.disabled = false;
                    });
            });
        });

        function showToast(message, type) {
            var existing = document.getElementById('module-toast');
            if (existing) existing.remove();

            var toast = document.createElement('div');
            toast.id = 'module-toast';
            toast.className = 'alert alert-' + type + ' position-fixed bottom-0 end-0 m-3 shadow-lg d-flex align-items-center gap-2';
            toast.setAttribute('role', 'alert');
            toast.style.zIndex = '2000';
            toast.innerHTML = '<i class="bi ' + (type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill') +
                '"></i><span>' + message + '</span>';
            document.body.appendChild(toast);

            setTimeout(function () { toast.remove(); }, 3500);
        }
    })();
</script>
@endpush
