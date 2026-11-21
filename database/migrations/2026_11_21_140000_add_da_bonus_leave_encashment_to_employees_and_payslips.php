<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (! Schema::hasColumn('employees', 'da')) {
                $table->decimal('da', 10, 2)->default(0)->after('basic_salary');
            }
            if (! Schema::hasColumn('employees', 'bonus')) {
                $table->decimal('bonus', 10, 2)->default(0)->after('da');
            }
            if (! Schema::hasColumn('employees', 'leave_encashment')) {
                $table->decimal('leave_encashment', 10, 2)->default(0)->after('bonus');
            }
        });

        Schema::table('payslips', function (Blueprint $table) {
            if (! Schema::hasColumn('payslips', 'da')) {
                $table->decimal('da', 10, 2)->default(0)->after('basic_salary');
            }
            if (! Schema::hasColumn('payslips', 'bonus')) {
                $table->decimal('bonus', 10, 2)->default(0)->after('da');
            }
            if (! Schema::hasColumn('payslips', 'leave_encashment')) {
                $table->decimal('leave_encashment', 10, 2)->default(0)->after('bonus');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['da', 'bonus', 'leave_encashment']);
        });

        Schema::table('payslips', function (Blueprint $table) {
            $table->dropColumn(['da', 'bonus', 'leave_encashment']);
        });
    }
};
