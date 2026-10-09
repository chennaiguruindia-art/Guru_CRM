<!-- App Sidebar — role aware, auto-flattening, pinnable -->
@php
    $user = auth()->user();

    // super-admin bypasses inside User::hasPermission()
    $can = function (?string $permission) use ($user) {
        if ($permission === null) {
            return true; // always visible (Dashboard)
        }
        return $user !== null && $user->hasPermission($permission);
    };

    // Global module switches (System Settings → Modules). A group is hidden
    // for everyone when its switch is off; missing key = enabled.
    $moduleFlags = \App\Models\Setting::query()->pluck('value', 'key');
    $moduleOn = fn (string $key) => ($moduleFlags['module_' . $key] ?? 'yes') !== 'no';

    // section > group > item = [label, icon, routeName, routePatterns, permission|null]
    $sections = [
        [
            'label' => null,
            'groups' => [
                ['label' => null, 'icon' => null, 'active' => ['dashboard*'], 'items' => [
                    ['Dashboard', 'bi-grid-1x2-fill', 'dashboard', ['dashboard*'], null],
                ]],
            ],
        ],
        [
            'label' => 'BUSINESS',
            'groups' => [
                ['label' => 'CRM & Clients', 'key' => 'crm_clients', 'icon' => 'bi-person-lines-fill', 'active' => ['leads*', 'customers*', 'cold_calls*', 'promotion_emails*', 'client_visits*'], 'items' => [
                    // Cold Calls carries every Visit — it replaced the old
                    // Visits entry, which is why it is the only one a Field
                    // Marketer sees. The other three are Admin and above.
                    ['Cold Calls / Direct Call', 'bi-telephone', 'cold_calls.index', ['cold_calls*'], 'leads.view'],
                    ['Promotion Email / Call', 'bi-envelope', 'promotion_emails.index', ['promotion_emails*'], 'leads.promotion'],
                    ['Existing Client Visit', 'bi-person-check', 'client_visits.index', ['client_visits*'], 'leads.existing_client'],
                    ['Client', 'bi-people', 'customers.index', ['customers*'], 'customers.view'],
                ]],
                ['label' => 'Sales & Projects', 'key' => 'sales_projects', 'icon' => 'bi-briefcase-fill', 'active' => ['estimations*', 'quotations*', 'projects*', 'work-orders*'], 'items' => [
                    ['Cost Estimations', 'bi-calculator', 'estimations.index', ['estimations*'], 'estimations.view'],
                    ['Quotations', 'bi-file-earmark-text', 'quotations.index', ['quotations*'], 'quotations.view'],
                    ['Projects', 'bi-kanban', 'projects.index', ['projects*'], 'projects.view'],
                    ['Work Orders', 'bi-card-checklist', 'work-orders.index', ['work-orders*'], 'work_orders.view'],
                ]],
            ],
        ],
        [
            'label' => 'HORTICULTURE',
            'groups' => [
                ['label' => 'Nursery & Garden', 'key' => 'nursery_garden', 'icon' => 'bi-tree-fill', 'active' => ['plants*', 'inventory*', 'maintenance*', 'amc*'], 'items' => [
                    ['Plants Master', 'bi-flower2', 'plants.index', ['plants*'], 'plants.view'],
                    ['Stock Inventory', 'bi-boxes', 'inventory.index', ['inventory*'], 'inventory.view'],
                    ['Maintenance Logs', 'bi-tools', 'maintenance.index', ['maintenance*'], 'maintenance.view'],
                    ['AMC Contracts', 'bi-shield-check', 'amc.index', ['amc*'], 'amc.view'],
                ]],
                ['label' => 'Operations & Staff', 'key' => 'operations_staff', 'icon' => 'bi-gear-wide-connected', 'active' => ['employees*', 'tasks*', 'attendance*', 'vendors*', 'purchases*'], 'items' => [
                    ['Employees', 'bi-person-badge', 'employees.index', ['employees*'], 'employees.view'],
                    ['Tasks', 'bi-check2-square', 'tasks.index', ['tasks*'], 'tasks.view'],
                    ['Daily Attendance', 'bi-calendar-check', 'attendance.index', ['attendance*'], 'attendance.view'],
                    ['Vendors', 'bi-shop', 'vendors.index', ['vendors*'], 'vendors.view'],
                    ['Purchase Orders', 'bi-cart-check', 'purchases.index', ['purchases*'], 'purchases.view'],
                ]],
            ],
        ],
        [
            'label' => 'FINANCE & SUPPORT',
            'groups' => [
                ['label' => 'Finance & Billing', 'key' => 'finance_billing', 'icon' => 'bi-cash-coin', 'active' => ['invoices*', 'payments*', 'expenses*'], 'items' => [
                    ['Invoices', 'bi-receipt', 'invoices.index', ['invoices*'], 'invoices.view'],
                    ['Payments', 'bi-cash-stack', 'payments.index', ['payments*'], 'payments.view'],
                    ['Expenses', 'bi-wallet2', 'expenses.index', ['expenses*'], 'expenses.view'],
                ]],
                ['label' => 'Support & Helpdesk', 'key' => 'support_helpdesk', 'icon' => 'bi-headset', 'active' => ['complaints*', 'documents*', 'calendar*'], 'items' => [
                    ['Complaints / Tickets', 'bi-chat-square-dots', 'complaints.index', ['complaints*'], 'complaints.view'],
                    ['Documents Vault', 'bi-folder2-open', 'documents.index', ['documents*'], 'documents.view'],
                    ['Operations Calendar', 'bi-calendar3', 'calendar.index', ['calendar*'], 'calendar.view'],
                ]],
            ],
        ],
        [
            'label' => 'ANALYTICS & SYSTEM',
            'groups' => [
                ['label' => null, 'key' => 'reports_analytics', 'icon' => null, 'active' => ['reports*'], 'items' => [
                    ['Reports & Analytics', 'bi-bar-chart-line-fill', 'reports.index', ['reports*'], 'reports.view'],
                ]],
                ['label' => 'Administration', 'key' => 'administration', 'icon' => 'bi-shield-lock-fill', 'active' => ['users*', 'roles*', 'settings*', 'audit-logs*', 'branches*'], 'items' => [
                    ['Branches', 'bi-building', 'branches.index', ['branches*'], 'branches.view'],
                    ['User Management', 'bi-person-gear', 'users.index', ['users*'], 'users.view'],
                    ['Roles & Permissions', 'bi-key', 'roles.index', ['roles*'], 'roles.view'],
                    ['System Settings', 'bi-sliders2', 'settings.index', ['settings*'], 'settings.view'],
                    ['Audit Trail', 'bi-journal-text', 'audit-logs.index', ['audit-logs*'], 'audit_logs.view'],
                ]],
            ],
        ],
    ];

    // ── Apply permissions ────────────────────────────────────────────────
    $visibleCount = 0;
    $menu = [];

    foreach ($sections as $section) {
        $groupsOut = [];

        foreach ($section['groups'] as $group) {
            // Global module switch — turned off in System Settings → Modules.
            if (isset($group['key']) && !$moduleOn($group['key'])) {
                continue;
            }

            $itemsOut = [];

            foreach ($group['items'] as $item) {
                if ($can($item[4])) {
                    $itemsOut[] = $item;
                    $visibleCount++;
                }
            }

            if ($itemsOut) {
                $groupsOut[] = array_merge($group, ['items' => $itemsOut]);
            }
        }

        if ($groupsOut) {
            $menu[] = ['label' => $section['label'], 'groups' => $groupsOut];
        }
    }

    // Small menus render flat — no accordions, nothing to expand.
    $flatten = $visibleCount <= 14;

    $isGroupActive = fn (array $group) => request()->routeIs($group['active']);
@endphp

<nav id="sidebar" class="sidebar d-flex flex-column flex-shrink-0" data-flatten="{{ $flatten ? '1' : '0' }}">
    <!-- Brand Header -->
    <div class="sidebar-brand-wrapper d-flex align-items-center justify-content-between">
        <a href="{{ route('dashboard') }}" class="sidebar-brand text-decoration-none d-flex align-items-center gap-3">
            <img src="{{ asset('logo/guru.png') }}" alt="Guru Living Assets" class="sidebar-brand-logo">
        </a>
        <button id="sidebar-close-btn" class="btn btn-sm btn-link text-white-50 p-1 d-lg-none" type="button" aria-label="Close navigation">
            <i class="bi bi-x-lg fs-5"></i>
        </button>
    </div>

    <!-- Navigation Scroll Container -->
    <div class="sidebar-menu flex-grow-1 overflow-y-auto px-2 py-3">
        <!-- Pinned favourites (populated by sidebar.js) -->
        <div id="sidebar-pinned" hidden>
            <div class="sidebar-section-divider"><span><i class="bi bi-star-fill me-1"></i>PINNED</span></div>
            <div id="sidebar-pinned-list"></div>
        </div>

        @foreach ($menu as $section)
            @if ($section['label'])
                <div class="sidebar-section-divider">
                    <span>{{ $section['label'] }}</span>
                </div>
            @endif

            @foreach ($section['groups'] as $group)
                @if ($group['label'] && !$flatten)
                    <div class="sidebar-group mb-1">
                        <button class="sidebar-group-btn {{ $isGroupActive($group) ? '' : 'collapsed' }}" type="button"
                                data-bs-toggle="collapse" data-bs-target="#menu-{{ \Illuminate\Support\Str::slug($group['label']) }}"
                                aria-expanded="{{ $isGroupActive($group) ? 'true' : 'false' }}">
                            <span class="sidebar-icon"><i class="bi {{ $group['icon'] }}"></i></span>
                            <span class="sidebar-text">{{ $group['label'] }}</span>
                            <i class="bi bi-chevron-right sidebar-arrow"></i>
                        </button>
                        <div class="collapse {{ $isGroupActive($group) ? 'show' : '' }}" id="menu-{{ \Illuminate\Support\Str::slug($group['label']) }}">
                            <ul class="sidebar-sub-menu list-unstyled">
                                @foreach ($group['items'] as $item)
                                    <li>
                                        <div class="side-item">
                                            <a href="{{ route($item[2]) }}"
                                               class="sidebar-sub-link {{ request()->routeIs($item[3]) ? 'active' : '' }}"
                                               data-pin-key="{{ $item[2] }}" data-pin-label="{{ $item[0] }}"
                                               data-pin-icon="{{ $item[1] }}" data-pin-href="{{ route($item[2]) }}">
                                                <i class="bi {{ $item[1] }}"></i>
                                                <span>{{ $item[0] }}</span>
                                            </a>
                                            <button type="button" class="pin-btn" data-pin-target="{{ $item[2] }}"
                                                    title="Pin to top" aria-label="Pin {{ $item[0] }} to top">
                                                <i class="bi bi-star"></i>
                                            </button>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @else
                    @foreach ($group['items'] as $item)
                        <div class="nav-item mb-1">
                            <div class="side-item">
                                <a href="{{ route($item[2]) }}"
                                   class="sidebar-link {{ request()->routeIs($item[3]) ? 'active' : '' }}"
                                   data-pin-key="{{ $item[2] }}" data-pin-label="{{ $item[0] }}"
                                   data-pin-icon="{{ $item[1] }}" data-pin-href="{{ route($item[2]) }}">
                                    <span class="sidebar-icon"><i class="bi {{ $item[1] }}"></i></span>
                                    <span class="sidebar-text">{{ $item[0] }}</span>
                                </a>
                                @if ($item[2] !== 'dashboard')
                                    <button type="button" class="pin-btn" data-pin-target="{{ $item[2] }}"
                                            title="Pin to top" aria-label="Pin {{ $item[0] }} to top">
                                        <i class="bi bi-star"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif
            @endforeach
        @endforeach
    </div>

    <!-- User Profile Footer Card -->
    <div class="sidebar-footer">
        <div class="user-card-compact d-flex align-items-center gap-2">
            <div class="user-avatar-wrap position-relative">
                <div class="user-avatar">
                    {{ auth()->check() ? auth()->user()->initials : 'AD' }}
                </div>
                <span class="user-status-dot" title="Online"></span>
            </div>
            <div class="user-details overflow-hidden flex-grow-1">
                <div class="user-name text-truncate">{{ auth()->check() ? auth()->user()->name : 'Administrator' }}</div>
                <div class="user-role text-truncate">{{ auth()->check() ? auth()->user()->role_name : 'Super Admin' }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="btn-logout" title="Sign out of CRM">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>
</nav>
