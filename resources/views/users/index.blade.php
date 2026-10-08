@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold text-dark">
                <i class="bi bi-people-fill me-2 text-primary"></i>Users
            </h1>
            <p class="text-muted mb-0 small">Manage system users and their roles</p>
        </div>
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus-fill me-2"></i>Add User
        </a>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Search & Filter Bar --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('users.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input
                            type="text"
                            name="search"
                            class="form-control border-start-0 ps-0"
                            placeholder="Search by name, email or phone…"
                            value="{{ request('search') }}"
                        >
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <select name="role" class="form-select">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ request('role') == $role->id ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                    @if(request()->hasAny(['search','role']))
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary ms-1">
                            <i class="bi bi-x-circle me-1"></i>Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Users Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-3">
            <span class="fw-semibold">
                <i class="bi bi-list-ul me-2 text-primary"></i>All Users
            </span>
            <span class="badge bg-primary rounded-pill">{{ $users->total() }} total</span>
        </div>

        @if($users->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="width:40px">#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role(s)</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $index => $user)
                            <tr>
                                {{-- Row Number --}}
                                <td class="ps-4 text-muted small">
                                    {{ $users->firstItem() + $index }}
                                </td>

                                {{-- Name + You badge --}}
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 text-white fw-bold"
                                             style="width:36px;height:36px;font-size:.8rem;background:{{ '#' . substr(md5($user->name), 0, 6) }}">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark">
                                                {{ $user->name }}
                                                @if(auth()->id() === $user->id)
                                                    <span class="badge bg-info text-white ms-1" style="font-size:.65rem">You</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Email --}}
                                <td>
                                    <a href="mailto:{{ $user->email }}" class="text-decoration-none text-muted">
                                        <i class="bi bi-envelope me-1"></i>{{ $user->email }}
                                    </a>
                                </td>

                                {{-- Phone --}}
                                <td class="text-muted">
                                    @if($user->phone)
                                        <i class="bi bi-telephone me-1"></i>{{ $user->phone }}
                                    @else
                                        <span class="text-muted fst-italic">—</span>
                                    @endif
                                </td>

                                {{-- Role(s) --}}
                                <td>
                                    @forelse($user->roles as $role)
                                        <span class="badge bg-secondary bg-opacity-75 me-1">
                                            {{ $role->name }}
                                        </span>
                                    @empty
                                        <span class="text-muted fst-italic small">No role</span>
                                    @endforelse
                                </td>

                                {{-- Status --}}
                                <td>
                                    @if(isset($user->status) && $user->status === 'active' || !isset($user->status))
                                        <span class="badge bg-success">
                                            <i class="bi bi-circle-fill me-1" style="font-size:.45rem;vertical-align:middle"></i>Active
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-circle-fill me-1" style="font-size:.45rem;vertical-align:middle"></i>Inactive
                                        </span>
                                    @endif
                                </td>

                                {{-- Created At --}}
                                <td class="text-muted small">
                                    <i class="bi bi-calendar3 me-1"></i>
                                    {{ $user->created_at->format('d M Y') }}
                                </td>

                                {{-- Actions --}}
                                <td class="text-end pe-4">
                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                        {{-- Mirrors UserController::mayEdit(): the button is not
                                             rendered where clicking it could only 403. --}}
                                        @can('users.edit')
                                            @if(auth()->user()->hasRole('super-admin') || ! $user->hasRole('super-admin'))
                                                <a
                                                    href="{{ route('users.edit', $user) }}"
                                                    class="btn btn-sm btn-outline-success"
                                                    title="Edit User"
                                                >
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endif
                                        @endcan
                                        @if(auth()->id() !== $user->id)
                                            <form
                                                method="POST"
                                                action="{{ route('users.destroy', $user) }}"
                                                class="d-inline"
                                                onsubmit="return confirm('Are you sure you want to delete {{ addslashes($user->name) }}? This action cannot be undone.');"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Delete User"
                                                >
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </form>
                                        @else
                                            <button
                                                class="btn btn-sm btn-outline-secondary"
                                                disabled
                                                title="Cannot delete your own account"
                                            >
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($users->hasPages())
                <div class="card-footer bg-white border-top d-flex align-items-center justify-content-between py-3">
                    <div class="text-muted small">
                        Showing <strong>{{ $users->firstItem() }}</strong>–<strong>{{ $users->lastItem() }}</strong>
                        of <strong>{{ $users->total() }}</strong> users
                    </div>
                    <div>
                        {{ $users->withQueryString()->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif

        @else
            {{-- Empty State --}}
            <div class="card-body py-5 text-center">
                <div class="mb-3">
                    <i class="bi bi-people display-1 text-muted opacity-25"></i>
                </div>
                <h5 class="text-muted fw-semibold">No users found</h5>
                @if(request()->hasAny(['search','role']))
                    <p class="text-muted mb-3">No users match your current filters.</p>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-x-circle me-1"></i>Clear Filters
                    </a>
                @else
                    <p class="text-muted mb-3">Get started by adding your first user.</p>
                @endif
                <a href="{{ route('users.create') }}" class="btn btn-primary">
                    <i class="bi bi-person-plus-fill me-2"></i>Add User
                </a>
            </div>
        @endif
    </div>

</div>
@endsection
