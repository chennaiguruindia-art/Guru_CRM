<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Define Roles
        // The system runs on exactly three: whoever registers first becomes
        // Super Admin, and from then on an Admin creates either another Admin
        // or a Field Marketer. No other roles exist.
        $roles = [
            ['name' => 'Super Admin', 'slug' => 'super-admin', 'description' => 'The first account created by signup. Full control, and cannot be assigned to anyone else.'],
            ['name' => 'Admin', 'slug' => 'admin', 'description' => 'Full management access, and may create sub-users'],
            ['name' => 'Field Marketer', 'slug' => 'fieldmarketer', 'description' => 'Out in the field: adds leads and converts them into customers'],
        ];

        $roleModels = [];
        foreach ($roles as $roleData) {
            $roleModels[$roleData['slug']] = Role::updateOrCreate(
                ['slug' => $roleData['slug']],
                ['name' => $roleData['name'], 'description' => $roleData['description']]
            );
        }

        // 2. Define Modules & Actions
        // Single source of truth: config/permissions.php — the same array the
        // routes read to decide which permission guards each endpoint.
        $modules = config('permissions.modules');

        if (! is_array($modules) || $modules === []) {
            throw new \RuntimeException('config/permissions.modules is missing or empty — refusing to seed a partial permission set.');
        }

        $permissionIds = [];
        $displayNames = [
            // Read better in the Roles matrix than the generated
            // "<action> <module>" would.
            'leads.promotion' => 'Promotion Email / Call',
            'leads.existing_client' => 'Existing Client Visit',
        ];
        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $slug = "{$module}.{$action}";
                $name = $displayNames[$slug]
                    ?? ucwords(str_replace('_', ' ', $action)) . ' ' . ucwords(str_replace('_', ' ', $module));
                $permission = Permission::updateOrCreate(
                    ['slug' => $slug],
                    ['name' => $name, 'module' => $module, 'description' => "Permission to {$action} {$module}"]
                );
                $permissionIds[$slug] = $permission->id;
            }
        }

        // 3. Assign Permissions to Roles
        // Super Admin bypasses hasPermission() entirely, but is granted the
        // full set anyway so the Roles & Permissions matrix reads correctly.
        //
        // `leads.convert` is held by Super Admin alone — converting hands over
        // the customer record, so it is withheld from Admin too.
        $superAdminOnly = ['leads.convert'];

        // Held by Admin and Super Admin but never by a Field Marketer: the
        // two extra CRM channels. sync() below replaces each role's set
        // outright, so a re-run also clears anything left behind earlier.
        $adminAndAbove = ['leads.convert', 'leads.promotion', 'leads.existing_client'];

        $adminGranted = array_filter(
            $permissionIds,
            fn ($slug) => ! in_array($slug, $superAdminOnly, true),
            ARRAY_FILTER_USE_KEY
        );

        $roleModels['super-admin']->permissions()->sync(array_values($permissionIds));

        // Admin gets every other permission — this is what lets an Admin
        // create sub-users and manage everything.
        $roleModels['admin']->permissions()->sync(array_values($adminGranted));

        // Field Marketer
        // Deliberately narrow: they log Cold Calls and nothing else, so that
        // is the only entry the CRM & Clients menu shows them. Promotion
        // Email / Call, Existing Client Visit and Client are Admin and above,
        // and so is converting a Visit into one. Sales & Projects detail,
        // finance and administration were never theirs either.
        $fieldPermissions = collect($adminGranted)->filter(function ($id, $slug) use ($adminAndAbove) {
            return str_starts_with($slug, 'leads.')
                && ! in_array($slug, $adminAndAbove, true);
        })->values()->all();
        $roleModels['fieldmarketer']->permissions()->sync($fieldPermissions);

        // 3b. Retire roles that this system no longer uses. Guarded: a role
        // still assigned to somebody is left alone rather than orphaning them.
        $retired = [
            'manager', 'sales-executive', 'project-manager', 'site-supervisor',
            'accountant', 'store-manager', 'hr-employee',
        ];

        $inUse = Role::whereIn('slug', $retired)->withCount('users')
            ->get()
            ->filter(fn ($role) => $role->users_count > 0)
            ->pluck('slug');

        Role::whereIn('slug', array_values(array_diff($retired, $inUse->all())))
            ->get()
            ->each->delete();

        // 4. Users
        // Deliberately creates nobody. The very first account comes from the
        // public signup screen and becomes Super Admin; from then on signup
        // closes and an Admin creates sub-users (Admin or Field Marketer).
        //
        // The old demo logins (admin@, sales@, pm@, supervisor@, accounts@,
        // store@) are intentionally no longer seeded.

        // 5. Default Settings
        $defaultSettings = [
            ['key' => 'company_name', 'value' => 'GreenScape Horticulture & Landscapes', 'group' => 'company', 'description' => 'Company legal name'],
            ['key' => 'company_email', 'value' => 'contact@greenscapehort.com', 'group' => 'company', 'description' => 'Company support email'],
            ['key' => 'company_phone', 'value' => '+91 80 2345 6789', 'group' => 'company', 'description' => 'Company telephone number'],
            ['key' => 'company_address', 'value' => 'GreenScape Gardens, Nursery Road, Bangalore, Karnataka - 560064', 'group' => 'company', 'description' => 'Office address'],
            ['key' => 'company_gst', 'value' => '29AAAAA0000A1Z5', 'group' => 'tax', 'description' => 'GST Identification Number'],
            ['key' => 'currency_symbol', 'value' => '₹', 'group' => 'localization', 'description' => 'Default currency symbol'],
            ['key' => 'currency_code', 'value' => 'INR', 'group' => 'localization', 'description' => 'Default currency code'],
            ['key' => 'tax_rate_default', 'value' => '18', 'group' => 'tax', 'description' => 'Default GST % for landscape services'],
        ];

        foreach ($defaultSettings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'group' => $setting['group'], 'description' => $setting['description']]
            );
        }
    }
}
