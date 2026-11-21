<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // PF Number
            if (! Schema::hasColumn('employees', 'pf_number')) {
                $table->string('pf_number', 50)->nullable()->after('uan_number');
            }

            // Custom Salary Component Breakdown
            if (! Schema::hasColumn('employees', 'basic_salary')) {
                $table->decimal('basic_salary', 10, 2)->nullable()->after('salary');
            }
            if (! Schema::hasColumn('employees', 'hra')) {
                $table->decimal('hra', 10, 2)->nullable()->after('basic_salary');
            }
            if (! Schema::hasColumn('employees', 'conveyance_allowance')) {
                $table->decimal('conveyance_allowance', 10, 2)->nullable()->after('hra');
            }
            if (! Schema::hasColumn('employees', 'special_allowance')) {
                $table->decimal('special_allowance', 10, 2)->nullable()->after('conveyance_allowance');
            }
            if (! Schema::hasColumn('employees', 'other_allowance')) {
                $table->decimal('other_allowance', 10, 2)->nullable()->after('special_allowance');
            }

            // Custom Statutory Deduction Overrides (Optional)
            if (! Schema::hasColumn('employees', 'custom_pf_deduction')) {
                $table->decimal('custom_pf_deduction', 10, 2)->nullable()->after('other_allowance');
            }
            if (! Schema::hasColumn('employees', 'custom_esi_deduction')) {
                $table->decimal('custom_esi_deduction', 10, 2)->nullable()->after('custom_pf_deduction');
            }
            if (! Schema::hasColumn('employees', 'custom_pt_deduction')) {
                $table->decimal('custom_pt_deduction', 10, 2)->nullable()->after('custom_esi_deduction');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'pf_number',
                'basic_salary',
                'hra',
                'conveyance_allowance',
                'special_allowance',
                'other_allowance',
                'custom_pf_deduction',
                'custom_esi_deduction',
                'custom_pt_deduction'
            ]);
        });
    }
};
