<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'is_auto_checkout')) {
                $table->boolean('is_auto_checkout')->default(false)->after('permission_reason');
            }
            if (!Schema::hasColumn('attendances', 'auto_checkout_at')) {
                $table->dateTime('auto_checkout_at')->nullable()->after('is_auto_checkout');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasColumn('attendances', 'auto_checkout_at')) {
                $table->dropColumn('auto_checkout_at');
            }
            if (Schema::hasColumn('attendances', 'is_auto_checkout')) {
                $table->dropColumn('is_auto_checkout');
            }
        });
    }
};
