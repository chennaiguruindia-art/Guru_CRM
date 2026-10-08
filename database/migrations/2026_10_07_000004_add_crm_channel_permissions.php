<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Promotion Email / Call and Existing Client Visit are the two CRM channels
     * a Field Marketer may not reach. That split has to exist in the database,
     * not just in RoleAndPermissionSeeder, or the live roles never change.
     */
    public function up(): void
    {
        $now = now();

        $channels = [
            'leads.promotion' => 'Promotion Email / Call',
            'leads.existing_client' => 'Existing Client Visit',
        ];

        foreach ($channels as $slug => $name) {
            DB::table('permissions')->insertOrIgnore([
                'slug' => $slug,
                'name' => $name,
                'module' => 'leads',
                'description' => "Permission to {$slug} leads",
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Admin and Super Admin gain both; sync() already covers this on a
        // fresh seed, so only the two new rows are added here.
        $roleIds = DB::table('roles')->whereIn('slug', ['super-admin', 'admin'])->pluck('id');
        $permissionIds = DB::table('permissions')->whereIn('slug', array_keys($channels))->pluck('id');

        foreach ($roleIds as $roleId) {
            foreach ($permissionIds as $permissionId) {
                DB::table('permission_role')->insertOrIgnore([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // The Field Marketer is Cold Calls only: drop the two new channels,
        // conversion, and every Client permission they still held — the Client
        // entry is no longer in their sidebar either.
        $fieldMarketerId = DB::table('roles')->where('slug', 'fieldmarketer')->value('id');

        if ($fieldMarketerId !== null) {
            DB::table('permission_role')
                ->where('role_id', $fieldMarketerId)
                ->whereIn('permission_id', function ($query) {
                    $query->select('id')->from('permissions')->where(function ($inner) {
                        $inner->where('module', 'customers')
                            ->orWhereIn('slug', [
                                'leads.convert',
                                'leads.promotion',
                                'leads.existing_client',
                            ]);
                    });
                })
                ->delete();
        }
    }

    public function down(): void
    {
        // Re-running RoleAndPermissionSeeder restores the intended shape for
        // every role; removing the two permission rows would orphan the
        // grants that already reference them.
    }
};
