<!-- Top Navbar Component -->
<header class="top-navbar d-flex align-items-center justify-content-between">
    <!-- Left: Mobile Sidebar Toggle & Page Title Breadcrumb -->
    <div class="d-flex align-items-center gap-3">
        <button id="sidebar-toggle" class="btn btn-outline-secondary btn-sm d-lg-none" type="button" aria-label="Toggle navigation">
            <i class="bi bi-list fs-5"></i>
        </button>
        <div class="d-none d-md-flex align-items-center gap-2">
            <span class="badge bg-soft-success text-success px-2 py-1">
                <i class="bi bi-flower2 me-1"></i> Horticulture ERP
            </span>
            <span class="text-muted small">&bull;</span>
            <span class="text-secondary small fw-medium">{{ auth()->user()?->branchLabel() ?? 'All branches' }}</span>
        </div>

        @if(auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('super-admin')))
            <div class="btn-group btn-group-sm p-1 bg-light rounded-pill border ms-1 shadow-xs">
                <a href="{{ route('dashboard') }}" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold {{ !request()->is('hrm*') && !request()->is('employees*') && !request()->is('attendance*') ? 'btn-success text-white shadow-sm' : 'text-secondary' }}">
                    <i class="bi bi-briefcase-fill me-1"></i> CRM
                </a>
                <a href="{{ route('hrm.dashboard') }}" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold {{ request()->is('hrm*') || request()->is('employees*') || request()->is('attendance*') ? 'btn-success text-white shadow-sm' : 'text-secondary' }}">
                    <i class="bi bi-people-fill me-1"></i> HRM Portal
                </a>
            </div>
        @endif
    </div>

    <!-- Right: Global Search, Quick Actions, Notification Bell, User Menu -->
    <div class="d-flex align-items-center gap-2 gap-md-3">
        <!-- Global Search -->
        <div class="global-search d-none d-sm-block">
            <i class="bi bi-search search-icon" aria-hidden="true"></i>
            <input type="text" id="global-search-input" class="form-control"
                   placeholder="Search visits, invoices..." aria-label="Search everything"
                   autocomplete="off" spellcheck="false" role="combobox" aria-expanded="false"
                   aria-controls="search-panel">
            <kbd class="search-kbd" aria-hidden="true">/</kbd>
            <div id="search-panel" class="search-panel" hidden role="listbox"></div>
        </div>

        <!-- Notifications Dropdown -->
        <div class="dropdown">
            <button id="notif-btn" class="btn btn-light position-relative btn-sm rounded-circle p-2" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Needs your attention">
                <i class="bi bi-bell fs-6"></i>
                <span id="notif-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;" hidden>
                    <span id="notif-badge-num">0</span>
                    <span class="visually-hidden">items need attention</span>
                </span>
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow-sm border-0 p-0" style="width: 360px; max-width: calc(100vw - 20px);">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-light rounded-top">
                    <div class="d-flex align-items-center gap-2">
                        <h6 class="mb-0 fw-semibold">Needs Attention</h6>
                        <span id="notif-count" class="badge bg-success-subtle text-success">…</span>
                    </div>
                    <button type="button" id="notif-mark-all-btn" class="btn btn-link btn-sm text-decoration-none text-muted p-0 small fw-medium" title="Mark all notifications as read" style="font-size: 0.78rem;">
                        Mark all as read
                    </button>
                </div>
                <div id="notif-list" class="list-group list-group-flush small" style="max-height: 340px; overflow-y: auto;">
                    <div class="p-3 text-muted small mb-0">Loading…</div>
                </div>
                <div class="p-2 text-center border-top">
                    <a href="{{ route('dashboard') }}#todo" class="small text-decoration-none text-success fw-medium">Open dashboard</a>
                </div>
            </div>
        </div>

        <!-- User Profile Dropdown -->
        <div class="dropdown">
            <button class="btn btn-light d-flex align-items-center gap-2 p-1 pe-2 rounded-pill border" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="navbar-user-avatar" style="width: 28px; height: 28px; font-size: 0.8rem;">
                    {{ auth()->check() ? auth()->user()->initials : 'U' }}
                </div>
                <span class="d-none d-md-inline small fw-medium text-dark">{{ auth()->check() ? auth()->user()->name : 'User' }}</span>
                <i class="bi bi-chevron-down small text-muted"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                <li class="px-3 py-2 border-bottom">
                    <div class="fw-semibold text-dark">{{ auth()->check() ? auth()->user()->name : 'Guest' }}</div>
                    <div class="small text-muted">{{ auth()->check() ? auth()->user()->email : '' }}</div>
                    <div class="badge bg-soft-success text-success mt-1">{{ auth()->check() ? auth()->user()->role_name : 'Staff' }}</div>
                </li>
                <li><a class="dropdown-item py-2" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2 text-muted"></i> My Profile</a></li>
                <li><a class="dropdown-item py-2" href="{{ route('profile.edit') }}#password"><i class="bi bi-key me-2 text-muted"></i> Change Password</a></li>
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item py-2 text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i> Log Out
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
