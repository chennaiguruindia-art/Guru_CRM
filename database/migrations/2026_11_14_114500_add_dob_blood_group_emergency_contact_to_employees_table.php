<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->date('dob')->nullable()->after('address');
            $table->string('blood_group', 10)->nullable()->after('dob');
            $table->string('emergency_contact')->nullable()->after('blood_group');
            $table->string('department')->nullable()->default('General')->change();
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['dob', 'blood_group', 'emergency_contact']);
        });
    }
};
