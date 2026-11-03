<?php

use App\Http\Controllers\Api\AmcController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\CalendarController;
use App\Http\Controllers\Api\ComplaintController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\EstimationController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\LookupController;
use App\Http\Controllers\Api\MaintenanceController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PlantController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\QuotationController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VendorController;
use App\Http\Controllers\Api\WorkOrderController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SearchController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Permission Helpers
|--------------------------------------------------------------------------
|
| Resolve dynamic module action permissions based on config/permissions.php
|
*/
$pickPermission = function (string $module, string ...$candidates): string {
    $available = config("permissions.modules.{$module}", []);

    foreach ($candidates as $candidate) {
        if (in_array($candidate, $available, true)) {
            return "{$module}.{$candidate}";
        }
    }

    return "{$module}.view";
};

/**
 * Register an API resource route with granular permission enforcement.
 * Names are prefixed with 'api.' to prevent collision with web resource routes.
 */
$apiResourceWithPerms = function (string $uri, string $controller, string $module) use ($pickPermission): void {
    $resourceName = 'api.' . str_replace('-', '_', $uri);

    Route::apiResource($uri, $controller)
        ->names($resourceName)
        ->only(['index', 'show'])
        ->middleware('permission:' . $pickPermission($module, 'view'));

    Route::apiResource($uri, $controller)
        ->names($resourceName)
        ->only(['store'])
        ->middleware('permission:' . $pickPermission($module, 'create'));

    Route::apiResource($uri, $controller)
        ->names($resourceName)
        ->only(['update'])
        ->middleware('permission:' . $pickPermission($module, 'edit', 'create'));

    Route::apiResource($uri, $controller)
        ->names($resourceName)
        ->only(['destroy'])
        ->middleware('permission:' . $pickPermission($module, 'delete', 'edit'));
};

/*
|--------------------------------------------------------------------------
| API Routes (v1)
|--------------------------------------------------------------------------
|
| Designed specifically for Mobile App (iOS / Android / Flutter / React Native).
| Token-based authentication powered by Laravel Sanctum.
|
*/
Route::prefix('v1')->group(function () use ($pickPermission, $apiResourceWithPerms) {

    // Public authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login'])->name('api.auth.login');
    });

    // Authenticated API routes
    Route::middleware(['auth:sanctum'])->group(function () use ($pickPermission, $apiResourceWithPerms) {

        // User & Auth Management
        Route::prefix('auth')->group(function () {
            Route::get('me', [AuthController::class, 'me'])->name('api.auth.me');
            Route::post('logout', [AuthController::class, 'logout'])->name('api.auth.logout');
            Route::post('profile', [AuthController::class, 'updateProfile'])->name('api.auth.profile');
            Route::post('password', [AuthController::class, 'updatePassword'])->name('api.auth.password');
        });

        // App Lookups (metadata, dropdown choices, status choices)
        Route::get('lookups', [LookupController::class, 'index'])->name('api.lookups');

        // Dashboard & Analytics
        Route::get('dashboard', [DashboardController::class, 'index'])->name('api.dashboard');
        Route::get('dashboard/stats', [DashboardController::class, 'stats'])->name('api.dashboard.stats');

        // Global Search & Notifications
        Route::get('search', SearchController::class)->name('api.search');
        Route::get('notifications', NotificationController::class)->name('api.notifications');

        // ─── CRM & Clients ──────────────────────────────────────────────
        $apiResourceWithPerms('leads', LeadController::class, 'leads');
        Route::post('leads/{lead}/convert', [LeadController::class, 'convert'])
            ->name('api.leads.convert')->middleware('permission:leads.convert');
        Route::post('leads/{lead}/activity', [LeadController::class, 'addActivity'])
            ->name('api.leads.activity')->middleware('permission:' . $pickPermission('leads', 'edit', 'create'));

        // Also allow POST for updating leads with file upload (visiting card)
        Route::post('leads/{lead}', [LeadController::class, 'update'])
            ->name('api.leads.update.post')->middleware('permission:' . $pickPermission('leads', 'edit', 'create'));

        // Lead Channels
        Route::get('cold-calls', [LeadController::class, 'coldCalls'])
            ->name('api.cold_calls.index')->middleware('permission:leads.view');
        Route::get('promotion-emails', [LeadController::class, 'promotionEmails'])
            ->name('api.promotion_emails.index')->middleware('permission:leads.promotion');
        Route::get('client-visits', [LeadController::class, 'clientVisits'])
            ->name('api.client_visits.index')->middleware('permission:leads.existing_client');

        $apiResourceWithPerms('customers', CustomerController::class, 'customers');

        // ─── Sales & Projects ───────────────────────────────────────────
        $apiResourceWithPerms('estimations', EstimationController::class, 'estimations');

        $apiResourceWithPerms('quotations', QuotationController::class, 'quotations');
        Route::patch('quotations/{quotation}/status', [QuotationController::class, 'updateStatus'])
            ->name('api.quotations.status')->middleware('permission:' . $pickPermission('quotations', 'edit', 'create'));

        $apiResourceWithPerms('projects', ProjectController::class, 'projects');
        $apiResourceWithPerms('work-orders', WorkOrderController::class, 'work_orders');

        // ─── Horticulture (Nursery & Garden) ────────────────────────────
        $apiResourceWithPerms('plants', PlantController::class, 'plants');
        Route::post('plants/{plant}', [PlantController::class, 'update'])
            ->name('api.plants.update.post')->middleware('permission:' . $pickPermission('plants', 'edit', 'create'));

        $apiResourceWithPerms('inventory', InventoryController::class, 'inventory');
        Route::post('inventory/{inventory}/adjust', [InventoryController::class, 'adjustStock'])
            ->name('api.inventory.adjust')->middleware('permission:' . $pickPermission('inventory', 'adjust', 'edit'));

        $apiResourceWithPerms('maintenance', MaintenanceController::class, 'maintenance');
        $apiResourceWithPerms('amc', AmcController::class, 'amc');

        // ─── Operations & Field Staff ────────────────────────────────────
        $apiResourceWithPerms('tasks', TaskController::class, 'tasks');
        Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus'])
            ->name('api.tasks.status')->middleware('permission:' . $pickPermission('tasks', 'edit', 'create'));

        Route::get('attendance', [AttendanceController::class, 'index'])
            ->name('api.attendance.index')->middleware('permission:' . $pickPermission('attendance', 'view'));
        Route::post('attendance', [AttendanceController::class, 'store'])
            ->name('api.attendance.store')->middleware('permission:' . $pickPermission('attendance', 'create'));
        Route::post('attendance/punch', [AttendanceController::class, 'punch'])
            ->name('api.attendance.punch')->middleware('permission:' . $pickPermission('attendance', 'create'));
        Route::get('attendance/my-attendance', [AttendanceController::class, 'myAttendance'])
            ->name('api.attendance.my_attendance')->middleware('permission:' . $pickPermission('attendance', 'view'));

        $apiResourceWithPerms('employees', EmployeeController::class, 'employees');
        $apiResourceWithPerms('vendors', VendorController::class, 'vendors');
        $apiResourceWithPerms('purchases', PurchaseController::class, 'purchases');

        // ─── Finance & Billing ──────────────────────────────────────────
        $apiResourceWithPerms('invoices', InvoiceController::class, 'invoices');
        $apiResourceWithPerms('payments', PaymentController::class, 'payments');
        $apiResourceWithPerms('expenses', ExpenseController::class, 'expenses');
        Route::post('expenses/{expense}', [ExpenseController::class, 'update'])
            ->name('api.expenses.update.post')->middleware('permission:' . $pickPermission('expenses', 'edit', 'create'));

        // ─── Support & Schedule ─────────────────────────────────────────
        $apiResourceWithPerms('complaints', ComplaintController::class, 'complaints');
        Route::patch('complaints/{complaint}/resolve', [ComplaintController::class, 'resolve'])
            ->name('api.complaints.resolve')->middleware('permission:' . $pickPermission('complaints', 'resolve', 'edit'));

        $apiResourceWithPerms('documents', DocumentController::class, 'documents');
        Route::get('calendar', [CalendarController::class, 'index'])
            ->name('api.calendar.index')->middleware('permission:' . $pickPermission('calendar', 'view'));

        // ─── Reports & Analytics ────────────────────────────────────────
        Route::get('reports', [ReportController::class, 'index'])
            ->name('api.reports.index')->middleware('permission:' . $pickPermission('reports', 'view'));

        // ─── Administration (Super Admin / Admin) ───────────────────────
        $apiResourceWithPerms('branches', BranchController::class, 'branches');
        $apiResourceWithPerms('users', UserController::class, 'users');
        $apiResourceWithPerms('roles', RoleController::class, 'roles');

        Route::get('settings', [SettingController::class, 'index'])
            ->name('api.settings.index')->middleware('permission:' . $pickPermission('settings', 'view'));
        Route::post('settings', [SettingController::class, 'store'])
            ->name('api.settings.store')->middleware('permission:' . $pickPermission('settings', 'edit'));

        Route::get('audit-logs', [AuditLogController::class, 'index'])
            ->name('api.audit_logs.index')->middleware('permission:' . $pickPermission('audit_logs', 'view'));
    });
});
