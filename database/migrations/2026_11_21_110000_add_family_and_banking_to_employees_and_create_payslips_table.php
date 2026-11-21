<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Family & Parents Details
            if (! Schema::hasColumn('employees', 'father_name')) {
                $table->string('father_name')->nullable()->after('emergency_contact');
            }
            if (! Schema::hasColumn('employees', 'mother_name')) {
                $table->string('mother_name')->nullable()->after('father_name');
            }
            if (! Schema::hasColumn('employees', 'father_phone')) {
                $table->string('father_phone')->nullable()->after('mother_name');
            }
            if (! Schema::hasColumn('employees', 'mother_phone')) {
                $table->string('mother_phone')->nullable()->after('father_phone');
            }
            if (! Schema::hasColumn('employees', 'father_occupation')) {
                $table->string('father_occupation')->nullable()->after('mother_phone');
            }
            if (! Schema::hasColumn('employees', 'mother_occupation')) {
                $table->string('mother_occupation')->nullable()->after('father_occupation');
            }
            if (! Schema::hasColumn('employees', 'parent_address')) {
                $table->text('parent_address')->nullable()->after('mother_occupation');
            }
            if (! Schema::hasColumn('employees', 'emergency_contact_relation')) {
                $table->string('emergency_contact_relation')->nullable()->after('parent_address');
            }
            if (! Schema::hasColumn('employees', 'marital_status')) {
                $table->string('marital_status')->nullable()->default('Single')->after('emergency_contact_relation');
            }
            if (! Schema::hasColumn('employees', 'spouse_name')) {
                $table->string('spouse_name')->nullable()->after('marital_status');
            }

            // Banking & Statutory info for Payslip
            if (! Schema::hasColumn('employees', 'bank_name')) {
                $table->string('bank_name')->nullable()->after('spouse_name');
            }
            if (! Schema::hasColumn('employees', 'bank_account_number')) {
                $table->string('bank_account_number')->nullable()->after('bank_name');
            }
            if (! Schema::hasColumn('employees', 'bank_ifsc')) {
                $table->string('bank_ifsc')->nullable()->after('bank_account_number');
            }
            if (! Schema::hasColumn('employees', 'pan_number')) {
                $table->string('pan_number')->nullable()->after('bank_ifsc');
            }
            if (! Schema::hasColumn('employees', 'uan_number')) {
                $table->string('uan_number')->nullable()->after('pan_number');
            }
        });

        if (! Schema::hasTable('payslips')) {
            Schema::create('payslips', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
                $table->string('salary_month', 7); // e.g. '2026-11'
                $table->string('month_name'); // e.g. 'November 2026'
                $table->decimal('basic_salary', 10, 2)->default(0);
                $table->decimal('hra', 10, 2)->default(0);
                $table->decimal('conveyance_allowance', 10, 2)->default(0);
                $table->decimal('special_allowance', 10, 2)->default(0);
                $table->decimal('gross_salary', 10, 2)->default(0);
                $table->decimal('pf_deduction', 10, 2)->default(0);
                $table->decimal('esi_deduction', 10, 2)->default(0);
                $table->decimal('professional_tax', 10, 2)->default(0);
                $table->decimal('lop_days', 4, 1)->default(0);
                $table->decimal('lop_deduction', 10, 2)->default(0);
                $table->decimal('other_deductions', 10, 2)->default(0);
                $table->decimal('total_deductions', 10, 2)->default(0);
                $table->decimal('net_salary', 10, 2)->default(0);
                $table->integer('total_working_days')->default(30);
                $table->decimal('paid_days', 4, 1)->default(30);
                $table->string('payment_status')->default('Paid');
                $table->date('payment_date')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();

                $table->unique(['employee_id', 'salary_month']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'father_name', 'mother_name', 'father_phone', 'mother_phone',
                'father_occupation', 'mother_occupation', 'parent_address',
                'emergency_contact_relation', 'marital_status', 'spouse_name',
                'bank_name', 'bank_account_number', 'bank_ifsc', 'pan_number', 'uan_number'
            ]);
        });
    }
};
