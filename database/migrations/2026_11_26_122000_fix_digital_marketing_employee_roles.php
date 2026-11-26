<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        try {
            $employeeRoleId = DB::table('roles')->where('slug', 'employee')->value('id') ?? 1;
            $fieldMarketerRoleId = DB::table('roles')->where('slug', 'fieldmarketer')->value('id') ?? 4;

            $userIds = DB::table('employees')
                ->whereNotNull('user_id')
                ->where(function ($q) {
                    $q->where('designation', 'like', '%digital%')
                      ->orWhere('designation', 'like', '%web%')
                      ->orWhere('designation', 'like', '%seo%');
                })
                ->pluck('user_id');

            if ($userIds->isNotEmpty()) {
                DB::table('role_user')
                    ->whereIn('user_id', $userIds)
                    ->where('role_id', $fieldMarketerRoleId)
                    ->update(['role_id' => $employeeRoleId]);
            }
        } catch (\Throwable $e) {
            // Safe fallback
        }
    }

    public function down(): void
    {
    }
};
