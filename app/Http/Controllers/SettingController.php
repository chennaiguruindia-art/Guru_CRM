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

    public function index(): View
    {
        $settings = Setting::all()->pluck('value', 'key');
        $modules = self::MODULES;

        return view('settings.index', compact('settings', 'modules'));
    }

    /**
     * POST /settings/module — instant save for a single Modules tab switch.
     * The switch posts on change so there is no "forgot to hit Save" state.
     */
    public function toggleModule(Request $request): \Illuminate\Http\JsonResponse
    {
        $key = (string) $request->input('key');
        $valid = array_column(self::MODULES, 'key');

        if (!in_array($key, $valid, true)) {
            return response()->json(['ok' => false, 'message' => 'Unknown module.'], 422);
        }

        $enabled = filter_var($request->input('value'), FILTER_VALIDATE_BOOLEAN);

        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $enabled ? 'yes' : 'no', 'group' => 'modules']
        );

        AuditLogger::log('update', 'settings', 0, null, ['module' => $key, 'enabled' => $enabled]);

        return response()->json([
            'ok' => true,
            'key' => $key,
            'enabled' => $enabled,
            'message' => $enabled ? 'Module enabled for everyone.' : 'Module hidden for everyone.',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $inputs = $request->except(['_token']);

        // Module switches post a hidden "no" + checkbox "yes"; every other
        // field is a plain value. Normalise module flags to yes/no and tag
        // them with the 'modules' group. Existing groups (company, tax, ...)
        // are left untouched for non-module keys.
        foreach ($inputs as $key => $val) {
            $isModule = str_starts_with((string) $key, 'module_');

            $payload = ['value' => $isModule ? (filter_var($val, FILTER_VALIDATE_BOOLEAN) ? 'yes' : 'no') : $val];

            if ($isModule) {
                $payload['group'] = 'modules';
            }

            Setting::updateOrCreate(['key' => $key], $payload);
        }

        AuditLogger::log('update', 'settings', 0);

        return back()->with('success', 'CRM settings updated successfully!');
    }
}
