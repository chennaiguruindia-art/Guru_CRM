<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A person is assigned to a branch when the Super Admin creates them.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (! Schema::hasColumn('employees', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()
                    ->constrained('branches')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'branch_id')) {
                $table->dropConstrainedForeignId('branch_id');
            }
        });
    }
};
