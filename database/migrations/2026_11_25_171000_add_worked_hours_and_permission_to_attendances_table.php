<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (! Schema::hasColumn('attendances', 'worked_hours')) {
                $table->decimal('worked_hours', 4, 2)->nullable()->after('check_out');
            }
            if (! Schema::hasColumn('attendances', 'has_permission')) {
                $table->boolean('has_permission')->default(false)->after('worked_hours');
            }
            if (! Schema::hasColumn('attendances', 'permission_reason')) {
                $table->string('permission_reason')->nullable()->after('has_permission');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('attendances', 'permission_reason')) {
                $cols[] = 'permission_reason';
            }
            if (Schema::hasColumn('attendances', 'has_permission')) {
                $cols[] = 'has_permission';
            }
            if (Schema::hasColumn('attendances', 'worked_hours')) {
                $cols[] = 'worked_hours';
            }
            if (! empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
