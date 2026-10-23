<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Sidebar groups that can be switched on/off from System Settings → Modules.
     * Keys must match the 'key' values used in components/sidebar.blade.php.
     */
    private const MODULES = [
        ['key' => 'module_crm_clients', 'label' => 'CRM & Clients', 'icon' => 'bi-person-lines-fill',
         'description' => 'Visits and Clients'],
        ['key' => 'module_sales_projects', 'label' => 'Sales & Projects', 'icon' => 'bi-briefcase-fill',
         'description' => 'Cost Estimations, Quotations, Projects, Work Orders'],
        ['key' => 'module_nursery_garden', 'label' => 'Nursery & Garden', 'icon' => 'bi-tree-fill',
         'description' => 'Plants Master, Stock Inventory, Maintenance Logs, AMC Contracts'],
        ['key' => 'module_operations_staff', 'label' => 'Operations & Staff', 'icon' => 'bi-gear-wide-connected',
         'description' => 'Employees, Tasks, Daily Attendance, Vendors, Purchase Orders'],
        ['key' => 'module_finance_billing', 'label' => 'Finance & Billing', 'icon' => 'bi-cash-coin',
         'description' => 'Invoices, Payments, Expenses'],
        ['key' => 'module_support_helpdesk', 'label' => 'Support & Helpdesk', 'icon' => 'bi-headset',
         'description' => 'Complaints / Tickets, Documents Vault, Operations Calendar'],
        ['key' => 'module_reports_analytics', 'label' => 'Reports & Analytics', 'icon' => 'bi-bar-chart-line-fill',
         'description' => 'Reports & Analytics dashboard'],
        ['key' => 'module_administration', 'label' => 'Administration', 'icon' => 'bi-shield-lock-fill',
         'description' => 'Branches, User Management, Roles & Permissions, System Settings, Audit Trail'],
    ];

    /**
     * Who may open System Settings, keyed by the switch that grants them.
     * These are the Super Admin's alone to throw: an Admin who has been let in
     * must not be able to widen the door behind them.
     *
     * @var list<array{key: string, label: string, description: string}>
     */
    private const ACCESS = [
        ['key' => 'settings_access_admin', 'label' => 'Admin',
         'description' => 'Reach System Settings, Company, Branding, Modules and Notifications'],
        ['key' => 'settings_access_sales', 'label' => 'Field Marketer (Sales)',
         'description' => 'Reach System Settings, Company, Branding, Modules and Notifications'],
    ];

    public function index(): View
    {
        $settings = Setting::all()->pluck('value', 'key');
        $modules = self::MODULES;
        $access = self::ACCESS;
        $canGrantAccess = auth()->user()?->hasRole('super-admin') ?? false;

        return view('settings.index', compact('settings', 'modules', 'access', 'canGrantAccess'));
    }

    /**
     * POST /settings/module — instant save for a single Modules or Access tab
     * switch. The switch posts on change so there is no "forgot to hit Save"
     * state.
     */
    public function toggleModule(Request $request): \Illuminate\Http\JsonResponse
    {
        $key = (string) $request->input('key');
        $isAccess = in_array($key, array_column(self::ACCESS, 'key'), true);
        $valid = array_merge(array_column(self::MODULES, 'key'), array_column(self::ACCESS, 'key'));

        if (!in_array($key, $valid, true)) {
            return response()->json(['ok' => false, 'message' => 'Unknown switch.'], 422);
        }

        if ($isAccess && ! $this->mayGrantAccess()) {
            return response()->json([
                'ok' => false,
                'message' => 'Only the Super Admin can decide who reaches System Settings.',
            ], 403);
        }

        $enabled = filter_var($request->input('value'), FILTER_VALIDATE_BOOLEAN);

        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $enabled ? 'yes' : 'no', 'group' => $isAccess ? 'access' : 'modules']
        );

        AuditLogger::log('update', 'settings', 0, null, ['switch' => $key, 'enabled' => $enabled]);

        return response()->json([
            'ok' => true,
            'key' => $key,
            'enabled' => $enabled,
            'message' => $enabled ? 'Switch turned on.' : 'Switch turned off.',
        ]);
    }

    /**
     * Only the Super Admin hands out Settings access.
     */
    private function mayGrantAccess(): bool
    {
        return auth()->user()?->hasRole('super-admin') ?? false;
    }

    public function store(Request $request): RedirectResponse
    {
        $inputs = $request->except(['_token']);

        // Module switches post a hidden "no" + checkbox "yes"; every other
        // field is a plain value. Normalise the switches to yes/no and tag them
        // with their group. Existing groups (company, tax, ...) are left
        // untouched for plain fields.
        $accessKeys = array_column(self::ACCESS, 'key');

        foreach ($inputs as $key => $val) {
            // Handing out Settings access is the Super Admin's alone, so a
            // crafted form post from an Admin is dropped rather than saved.
            if (! $this->mayGrantAccess() && in_array((string) $key, $accessKeys, true)) {
                continue;
            }

            $isModule = str_starts_with((string) $key, 'module_');
            $isAccess = in_array((string) $key, $accessKeys, true);
            $isSwitch = $isModule || $isAccess;

            $payload = ['value' => $isSwitch ? (filter_var($val, FILTER_VALIDATE_BOOLEAN) ? 'yes' : 'no') : $val];

            if ($isModule) {
                $payload['group'] = 'modules';
            } elseif ($isAccess) {
                $payload['group'] = 'access';
            }

            Setting::updateOrCreate(['key' => $key], $payload);
        }

        AuditLogger::log('update', 'settings', 0);

        return back()->with('success', 'CRM settings updated successfully!');
    }
}
