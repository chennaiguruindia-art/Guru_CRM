<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'check_in_image')) {
                $table->string('check_in_image')->nullable()->after('check_in_address');
            }
            if (!Schema::hasColumn('attendances', 'check_out_image')) {
                $table->string('check_out_image')->nullable()->after('check_out_address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasColumn('attendances', 'check_out_image')) {
                $table->dropColumn('check_out_image');
            }
            if (Schema::hasColumn('attendances', 'check_in_image')) {
                $table->dropColumn('check_in_image');
            }
        });
    }
};
