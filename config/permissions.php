<?php

/*
 |--------------------------------------------------------------------------
 | Permission Map
 |--------------------------------------------------------------------------
 |
 | Single source of truth for which permission slugs exist per module.
 |
 | Two consumers read this:
 |
 |   1. RoleAndPermissionSeeder — creates the rows in the `permissions` table
 |      and syncs them onto roles.
 |   2. routes/web.php — maps each resource action onto a permission so the
 |      `permission:` middleware can enforce it.
 |
 | Keeping both on one array means a route can never ask for a permission that
 | was never created, which would otherwise 403 administrators too (the `admin`
 | role is granted exactly this list, and only `super-admin` bypasses it).
 |
 */

return [

    'modules' => [
        'leads' => ['view', 'create', 'edit', 'delete', 'export', 'assign', 'convert', 'promotion', 'existing_client'],
        'customers' => ['view', 'create', 'edit', 'delete', 'export'],
        'plants' => ['view', 'create', 'edit', 'delete'],
        'inventory' => ['view', 'create', 'edit', 'delete', 'adjust'],
        'estimations' => ['view', 'create', 'edit', 'delete'],
        'quotations' => ['view', 'create', 'edit', 'delete', 'approve'],
        'projects' => ['view', 'create', 'edit', 'delete'],
        'work_orders' => ['view', 'create', 'edit', 'delete'],
        'employees' => ['view', 'create', 'edit', 'delete'],
        'tasks' => ['view', 'create', 'edit', 'delete'],
        'attendance' => ['view', 'create', 'edit', 'delete'],
        'maintenance' => ['view', 'create', 'edit', 'delete'],
        'amc' => ['view', 'create', 'edit', 'delete'],
        'purchases' => ['view', 'create', 'edit', 'delete'],
        'vendors' => ['view', 'create', 'edit', 'delete'],
        'invoices' => ['view', 'create', 'edit', 'delete', 'export'],
        'payments' => ['view', 'create', 'edit', 'delete'],
        'expenses' => ['view', 'create', 'edit', 'delete', 'approve'],
        'complaints' => ['view', 'create', 'edit', 'delete', 'resolve'],
        'documents' => ['view', 'create', 'delete'],
        'calendar' => ['view'],
        'reports' => ['view', 'export'],
        'users' => ['view', 'create', 'edit', 'delete'],
        'roles' => ['view', 'create', 'edit', 'delete'],
        'settings' => ['view', 'edit'],
        'audit_logs' => ['view'],
        'branches' => ['view', 'create', 'edit', 'delete'],
    ],

];
