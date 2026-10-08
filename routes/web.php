<?php

use App\Http\Controllers\AmcController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EstimationController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PlantController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingController;

use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\WorkOrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Permission helpers
|--------------------------------------------------------------------------
|
| Slugs come from config/permissions.php — the same array the seeder uses to
| create the rows in `permissions`. Routing can therefore never request a
| permission that does not exist, which would 403 administrators too: the
| `admin` role is granted exactly that list, and only `super-admin` bypasses.
|
| A module may not model every action (documents has no "edit"; calendar,
| reports and audit_logs are view-only), so unmodelled actions fall back
| instead of pointing at a slug that was never seeded.
|
*/

$pickPermission = function (string $module, string ...$candidates): string {
    $available = config("permissions.modules.{$module}", []);

    foreach ($candidates as $candidate) {
        if (in_array($candidate, $available, true)) {
            return "{$module}.{$candidate}";
        }
    }

    // Every module in the map declares "view".
    return "{$module}.view";
};

/**
 * Register a resource route split across the four CRUD permission buckets, so
 * e.g. a user with only leads.view cannot POST a new lead.
 *
 * $withShow = false for modules with no detail page (branches are fully
 * described by their index row).
 */
$resourceWithPerms = function (string $uri, string $controller, string $module, bool $withShow = true) use ($pickPermission): void {
    // ORDER MATTERS: `create` must be registered before `show`, otherwise
    // GET /employees/create is swallowed by the {employee} binding route and
    // 404s. This mirrors Laravel's own $resourceDefaults sequence.
    Route::resource($uri, $controller)
        ->only(['index'])
        ->middleware('permission:' . $pickPermission($module, 'view'));

    Route::resource($uri, $controller)
        ->only(['create', 'store'])
        ->middleware('permission:' . $pickPermission($module, 'create'));

    if ($withShow) {
        Route::resource($uri, $controller)
            ->only(['show'])
            ->middleware('permission:' . $pickPermission($module, 'view'));
    }

    Route::resource($uri, $controller)
        ->only(['edit', 'update'])
        ->middleware('permission:' . $pickPermission($module, 'edit', 'create'));

    Route::resource($uri, $controller)
        ->only(['destroy'])
        ->middleware('permission:' . $pickPermission($module, 'delete', 'edit'));
};

// Root redirect
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Authenticated Routes
Route::middleware(['auth'])->group(function () use ($pickPermission, $resourceWithPerms) {

    // Dashboard — every role lands here, so it carries no permission.
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');

    // Profile — you always manage your own account.
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // CRM Section
    $resourceWithPerms('leads', LeadController::class, 'leads');
    Route::post('/leads/{lead}/convert', [LeadController::class, 'convert'])
        ->name('leads.convert')->middleware('permission:leads.convert');
    Route::post('/leads/{lead}/activity', [LeadController::class, 'addActivity'])
        ->name('leads.activity')->middleware('permission:' . $pickPermission('leads', 'edit', 'create'));

    // Sidebar → CRM & Clients: three channels over the very same Visit
    // records, not separate modules. Cold Calls lists every Visit; the other
    // two are filtered subsets, and neither is open to a Field Marketer.
    Route::get('/cold-calls', [LeadController::class, 'coldCalls'])
        ->name('cold_calls.index')->middleware('permission:leads.view');
    Route::get('/promotion-emails', [LeadController::class, 'promotionEmails'])
        ->name('promotion_emails.index')->middleware('permission:leads.promotion');
    Route::get('/existing-client-visits', [LeadController::class, 'clientVisits'])
        ->name('client_visits.index')->middleware('permission:leads.existing_client');

    $resourceWithPerms('customers', CustomerController::class, 'customers');

    // Sales Section
    $resourceWithPerms('estimations', EstimationController::class, 'estimations');

    $resourceWithPerms('quotations', QuotationController::class, 'quotations');
    Route::patch('/quotations/{quotation}/status', [QuotationController::class, 'updateStatus'])
        ->name('quotations.status')->middleware('permission:' . $pickPermission('quotations', 'edit', 'create'));
    Route::get('/quotations/{quotation}/print', [QuotationController::class, 'print'])
        ->name('quotations.print')->middleware('permission:' . $pickPermission('quotations', 'view'));

    $resourceWithPerms('projects', ProjectController::class, 'projects');
    $resourceWithPerms('work-orders', WorkOrderController::class, 'work_orders');

    // Horticulture Section
    $resourceWithPerms('plants', PlantController::class, 'plants');
    $resourceWithPerms('inventory', InventoryController::class, 'inventory');
    $resourceWithPerms('maintenance', MaintenanceController::class, 'maintenance');
    $resourceWithPerms('amc', AmcController::class, 'amc');

    // Operations Section
    $resourceWithPerms('employees', EmployeeController::class, 'employees');
    $resourceWithPerms('tasks', TaskController::class, 'tasks');

    Route::get('/attendance', [AttendanceController::class, 'index'])
        ->name('attendance.index')->middleware('permission:' . $pickPermission('attendance', 'view'));
    Route::post('/attendance', [AttendanceController::class, 'store'])
        ->name('attendance.store')->middleware('permission:' . $pickPermission('attendance', 'create'));

    $resourceWithPerms('vendors', VendorController::class, 'vendors');
    $resourceWithPerms('purchases', PurchaseController::class, 'purchases');

    // Finance Section
    $resourceWithPerms('invoices', InvoiceController::class, 'invoices');
    $resourceWithPerms('payments', PaymentController::class, 'payments');
    $resourceWithPerms('expenses', ExpenseController::class, 'expenses');

    // Support & Schedule
    $resourceWithPerms('complaints', ComplaintController::class, 'complaints');
    $resourceWithPerms('documents', DocumentController::class, 'documents');
    Route::get('/calendar', [CalendarController::class, 'index'])
        ->name('calendar.index')->middleware('permission:' . $pickPermission('calendar', 'view'));

    // Reports & Analytics
    Route::get('/reports', [ReportController::class, 'index'])
        ->name('reports.index')->middleware('permission:' . $pickPermission('reports', 'view'));

    // Administration
    $resourceWithPerms('branches', BranchController::class, 'branches', withShow: false);
    $resourceWithPerms('users', UserController::class, 'users');
    $resourceWithPerms('roles', RoleController::class, 'roles');

    Route::get('/settings', [SettingController::class, 'index'])
        ->name('settings.index')->middleware('permission:' . $pickPermission('settings', 'view'));
    Route::post('/settings', [SettingController::class, 'store'])
        ->name('settings.store')->middleware('permission:' . $pickPermission('settings', 'edit'));
    // Instant save for the Modules tab switches (no full form submit).
    Route::post('/settings/module', [SettingController::class, 'toggleModule'])
        ->name('settings.module')->middleware('permission:' . $pickPermission('settings', 'edit'));

    Route::get('/audit-logs', [AuditLogController::class, 'index'])
        ->name('audit-logs.index')->middleware('permission:' . $pickPermission('audit_logs', 'view'));

    // Global Search & Notifications — every role may search; the controllers
    // filter their own results down to what the caller is allowed to see.
    Route::get('/search', SearchController::class)->name('search');
    Route::get('/notifications', NotificationController::class)->name('notifications');
});

require __DIR__ . '/auth.php';
