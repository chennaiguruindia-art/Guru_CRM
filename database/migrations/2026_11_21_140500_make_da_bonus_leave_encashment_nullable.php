<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('da', 10, 2)->nullable()->default(0)->change();
            $table->decimal('bonus', 10, 2)->nullable()->default(0)->change();
            $table->decimal('leave_encashment', 10, 2)->nullable()->default(0)->change();
        });

        Schema::table('payslips', function (Blueprint $table) {
            $table->decimal('da', 10, 2)->nullable()->default(0)->change();
            $table->decimal('bonus', 10, 2)->nullable()->default(0)->change();
            $table->decimal('leave_encashment', 10, 2)->nullable()->default(0)->change();
        });
    }

    public function down(): void
    {
    }
};
