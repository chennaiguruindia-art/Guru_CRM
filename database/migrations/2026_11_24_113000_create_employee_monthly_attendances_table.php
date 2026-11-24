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
        Schema::create('employee_monthly_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name');
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('employee_code');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('branch')->nullable();
            $table->string('month', 7); // e.g. '2026-11'
            $table->decimal('total_present', 4, 1)->default(0);
            $table->decimal('total_absent', 4, 1)->default(0);
            $table->integer('no_of_permission')->default(0);
            $table->timestamps();

            $table->unique(['employee_id', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_monthly_attendances');
    }
};
