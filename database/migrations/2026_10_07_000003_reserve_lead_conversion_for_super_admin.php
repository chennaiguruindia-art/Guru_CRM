<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Converting a Visit into a Client hands over the customer record, so
     * `leads.convert` belongs to Super Admin alone. Detach it from the other
     * two roles without disturbing anything else they hold.
     *
     * RoleAndPermissionSeeder now excludes it too, so a fresh seed produces
     * the same shape.
     */
    public function up(): void
    {
        DB::table('permission_role')
            ->whereIn('role_id', function ($query) {
                $query->select('id')->from('roles')
                    ->whereIn('slug', ['admin', 'fieldmarketer']);
            })
            ->whereIn('permission_id', function ($query) {
                $query->select('id')->from('permissions')
                    ->where('slug', 'leads.convert');
            })
            ->delete();
    }

    public function down(): void
    {
        // Deliberately a no-op: nothing here can decide which roles were
        // supposed to have held it. Re-running RoleAndPermissionSeeder
        // restores it for Super Admin only, by design.
    }
};
