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
            $table->decimal('check_in_latitude', 10, 7)->nullable()->after('check_in');
            $table->decimal('check_in_longitude', 11, 7)->nullable()->after('check_in_latitude');
            $table->text('check_in_address')->nullable()->after('check_in_longitude');

            $table->decimal('check_out_latitude', 10, 7)->nullable()->after('check_out');
            $table->decimal('check_out_longitude', 11, 7)->nullable()->after('check_out_latitude');
            $table->text('check_out_address')->nullable()->after('check_out_longitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'check_in_latitude',
                'check_in_longitude',
                'check_in_address',
                'check_out_latitude',
                'check_out_longitude',
                'check_out_address',
            ]);
        });
    }
};
