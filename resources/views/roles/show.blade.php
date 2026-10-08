@extends('layouts.app')

@section('title', 'Role: ' . $role->name)

@section('content')
<div class="container-fluid px-4 py-3">

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="fw-bold mb-0">
                <i class="bi bi-shield-check me-2 text-primary"></i>Role: {{ $role->name }}
            </h2>
            <nav aria-label="breadcrumb" class="mt-1">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Roles &amp; Permissions</a></li>
                    <li class="breadcrumb-item active">{{ $role->name }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Roles
        </a>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row g-4">

        {{-- Left Column: Role Info Card --}}
        <div class="col-lg-4">

            {{-- Role Info --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-info-circle me-2 text-secondary"></i>Role Info
                    </h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5 text-muted small">Name</dt>
                        <dd class="col-sm-7 fw-semibold">{{ $role->name }}</dd>

                        <dt class="col-sm-5 text-muted small">Slug</dt>
                        <dd class="col-sm-7">
                            <code class="bg-light px-2 py-1 rounded small">{{ $role->slug }}</code>
                        </dd>

                        <dt class="col-sm-5 text-muted small">Permissions</dt>
                        <dd class="col-sm-7">
                            <span class="badge bg-secondary rounded-pill">
                                {{ $role->permissions->count() }}
                            </span>
                        </dd>

                        <dt class="col-sm-5 text-muted small">Users</dt>
                        <dd class="col-sm-7">
                            <span class="badge bg-info text-dark rounded-pill">
                                {{ $role->users->count() }}
                            </span>
                        </dd>
                    </dl>
                </div>
            </div>

            {{-- Users Assigned Card --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-people me-2 text-secondary"></i>Assigned Users
                    </h5>
                    <span class="badge bg-info text-dark rounded-pill">{{ $role->users->count() }}</span>
                </div>
                <div class="card-body p-0">
                    @forelse($role->users as $user)
                    <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom">
                        <span class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center
                              justify-content-center flex-shrink-0"
                              style="width:36px; height:36px;">
                            <i class="bi bi-person"></i>
                        </span>
                        <div class="overflow-hidden">
                            <div class="fw-semibold text-truncate small">{{ $user->name }}</div>
                            <div class="text-muted" style="font-size:.78rem;">{{ $user->email }}</div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-4 small">
                        <i class="bi bi-person-x fs-4 d-block mb-1"></i>No users assigned to this role yet.
                    </div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- Right Column: Permissions Editor --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-key me-2 text-secondary"></i>Permissions
                    </h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-success" id="selectAllGlobal">
                            <i class="bi bi-check-all me-1"></i> Select All
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" id="deselectAllGlobal">
                            <i class="bi bi-x-circle me-1"></i> Clear All
                        </button>
                    </div>
                </div>

                <form method="POST" action="{{ route('roles.update', $role) }}" id="permissionsForm">
                    @csrf
                    @method('PATCH')

                    <div class="card-body p-3">

                        {{-- Accordion: one section per module --}}
                        <div class="accordion" id="permissionsAccordion">

                            @foreach($allPermissions->keys() as $moduleIndex => $module)
                            @php
                                $modulePerms    = $allPermissions->get($module);
                                $moduleId       = 'module-' . \Str::slug($module);
                                $assignedIds    = $role->permissions->pluck('id')->toArray();
                                $moduleAssigned = $modulePerms->filter(fn($p) => in_array($p->id, $assignedIds))->count();
                                $moduleTotal    = $modulePerms->count();
                            @endphp

                            <div class="accordion-item border mb-2 rounded overflow-hidden">
                                <h2 class="accordion-header" id="heading-{{ $moduleId }}">
                                    <button class="accordion-button {{ $moduleIndex > 0 ? 'collapsed' : '' }} py-2 px-3"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#collapse-{{ $moduleId }}"
                                            aria-expanded="{{ $moduleIndex === 0 ? 'true' : 'false' }}"
                                            aria-controls="collapse-{{ $moduleId }}">
                                        <div class="d-flex align-items-center justify-content-between w-100 me-3">
                                            <span class="fw-semibold text-capitalize">
                                                <i class="bi bi-folder2-open me-2 text-primary"></i>{{ \App\Services\ModuleLabel::for($module) }}
                                            </span>
                                            <span class="badge {{ $moduleAssigned === $moduleTotal ? 'bg-success' : ($moduleAssigned > 0 ? 'bg-warning text-dark' : 'bg-light text-muted border') }} rounded-pill module-badge-{{ $moduleId }}">
                                                {{ $moduleAssigned }}/{{ $moduleTotal }}
                                            </span>
                                        </div>
                                    </button>
                                </h2>

                                <div id="collapse-{{ $moduleId }}"
                                     class="accordion-collapse collapse {{ $moduleIndex === 0 ? 'show' : '' }}"
                                     aria-labelledby="heading-{{ $moduleId }}"
                                     data-bs-parent="">
                                    <div class="accordion-body pt-2 pb-3">

                                        {{-- Per-module Select / Deselect --}}
                                        <div class="d-flex gap-2 mb-3">
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-success module-select-all"
                                                    data-module="{{ $moduleId }}">
                                                <i class="bi bi-check2-all me-1"></i> Select All
                                            </button>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-secondary module-deselect-all"
                                                    data-module="{{ $moduleId }}">
                                                <i class="bi bi-dash-circle me-1"></i> Deselect All
                                            </button>
                                        </div>

                                        <div class="row g-2" data-module-group="{{ $moduleId }}">
                                            @foreach($modulePerms as $perm)
                                            <div class="col-md-6 col-lg-4">
                                                <div class="form-check form-switch mb-0">
                                                    <input class="form-check-input module-perm-{{ $moduleId }}"
                                                           type="checkbox"
                                                           name="permissions[]"
                                                           value="{{ $perm->id }}"
                                                           id="perm-{{ $perm->id }}"
                                                           data-module="{{ $moduleId }}"
                                                           {{ $role->permissions->contains($perm) ? 'checked' : '' }}>
                                                    <label class="form-check-label small" for="perm-{{ $perm->id }}">
                                                        {{ $perm->name }}
                                                    </label>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>

                                    </div>
                                </div>
                            </div>
                            @endforeach

                        </div>{{-- /accordion --}}

                    </div>

                    <div class="card-footer bg-white border-top d-flex align-items-center justify-content-between py-3 px-4">
                        <span class="text-muted small">
                            <i class="bi bi-info-circle me-1"></i>
                            Changes take effect immediately after saving.
                        </span>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-floppy me-2"></i>Save Permissions
                        </button>
                    </div>
                </form>

            </div>
        </div>

    </div>{{-- /row --}}
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    /**
     * Toggle all checkboxes in a given module group.
     * @param {string} moduleId  - data-module attribute value
     * @param {boolean} checked  - true = select, false = deselect
     */
    function toggleModule(moduleId, checked) {
        document.querySelectorAll('.module-perm-' + moduleId).forEach(function (cb) {
            cb.checked = checked;
        });
    }

    // Per-module Select All buttons
    document.querySelectorAll('.module-select-all').forEach(function (btn) {
        btn.addEventListener('click', function () {
            toggleModule(this.dataset.module, true);
        });
    });

    // Per-module Deselect All buttons
    document.querySelectorAll('.module-deselect-all').forEach(function (btn) {
        btn.addEventListener('click', function () {
            toggleModule(this.dataset.module, false);
        });
    });

    // Global Select All
    document.getElementById('selectAllGlobal').addEventListener('click', function () {
        document.querySelectorAll('#permissionsForm input[type="checkbox"]').forEach(function (cb) {
            cb.checked = true;
        });
    });

    // Global Deselect All
    document.getElementById('deselectAllGlobal').addEventListener('click', function () {
        document.querySelectorAll('#permissionsForm input[type="checkbox"]').forEach(function (cb) {
            cb.checked = false;
        });
    });

});
</script>
@endpush
