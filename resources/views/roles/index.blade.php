@extends('layouts.app')

@section('title', 'Roles & Permissions')

@section('content')
<div class="container-fluid px-4 py-3">

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="fw-bold mb-0">
                <i class="bi bi-shield-lock me-2 text-primary"></i>Roles &amp; Permissions
            </h2>
            <nav aria-label="breadcrumb" class="mt-1">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Roles &amp; Permissions</li>
                </ol>
            </nav>
        </div>
        {{-- No "Add Role" button: RoleController has no create/store action and
             there is no roles/create view. Roles are managed from the matrix
             below, so the button only ever led to a 500. --}}
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Roles Table Card --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-semibold">
                <i class="bi bi-list-ul me-2 text-secondary"></i>All Roles
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="width: 40px;">#</th>
                            <th>Role Name</th>
                            <th>Slug</th>
                            <th class="text-center">Users</th>
                            <th class="text-center">Permissions</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $index => $role)
                        <tr>
                            <td class="ps-4 text-muted small">{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center"
                                        style="width:36px; height:36px; font-size:1rem;">
                                        <i class="bi bi-shield"></i>
                                    </span>
                                    <span class="fw-semibold">{{ $role->name }}</span>
                                </div>
                            </td>
                            <td>
                                <code class="bg-light px-2 py-1 rounded text-secondary small">{{ $role->slug }}</code>
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill bg-info text-dark">
                                    <i class="bi bi-people me-1"></i>{{ $role->users_count ?? 0 }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill bg-secondary">
                                    <i class="bi bi-key me-1"></i>{{ $role->permissions_count ?? 0 }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('roles.show', $role) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i> View / Edit
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-shield-x fs-2 d-block mb-2"></i>
                                No roles found. <a href="{{ route('roles.create') }}">Create the first role</a>.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Permission Matrix Card --}}
    @if($permissions->count() > 0 && $roles->count() > 0)
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
            <h5 class="mb-0 fw-semibold">
                <i class="bi bi-grid-3x3 me-2 text-secondary"></i>Permission Matrix
            </h5>
            <span class="badge bg-light text-muted border">Quick Overview</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle mb-0" id="permissionMatrix">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="min-width: 160px;">Module</th>
                            @foreach($roles as $role)
                            <th class="text-center" style="min-width: 110px;">
                                <span class="small fw-semibold">{{ $role->name }}</span>
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($permissions->keys() as $module)
                        <tr>
                            <td class="ps-3">
                                <span class="badge bg-primary bg-opacity-10 text-primary text-capitalize">
                                    <i class="bi bi-folder me-1"></i>{{ \App\Services\ModuleLabel::for($module) }}
                                </span>
                            </td>
                            @foreach($roles as $role)
                            @php
                                // Count how many permissions this role has in this module
                                $modulePerms   = $permissions->get($module);
                                $modulePermIds = $modulePerms->pluck('id')->toArray();
                                $rolePermIds   = $role->permissions->pluck('id')->toArray();
                                $matchCount    = count(array_intersect($modulePermIds, $rolePermIds));
                                $totalCount    = count($modulePermIds);
                            @endphp
                            <td class="text-center">
                                @if($matchCount > 0)
                                    <i class="bi bi-check-circle-fill text-success me-1"
                                       title="{{ $matchCount }} of {{ $totalCount }} permissions granted"></i>
                                    <span class="small text-muted">{{ $matchCount }}/{{ $totalCount }}</span>
                                @else
                                    <span class="text-muted" title="No permissions for this module">
                                        &mdash;
                                    </span>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white text-muted small py-2 px-3">
            <i class="bi bi-info-circle me-1"></i>
            Numbers indicate how many permissions are assigned out of the total available for each module.
        </div>
    </div>
    @endif

</div>
@endsection
