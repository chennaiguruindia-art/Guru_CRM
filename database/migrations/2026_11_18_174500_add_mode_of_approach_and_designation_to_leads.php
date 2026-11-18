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
        Schema::table('leads', function (Blueprint $table) {
            if (! Schema::hasColumn('leads', 'mode_of_approach')) {
                $table->string('mode_of_approach', 50)->nullable()->after('source');
            }
            if (! Schema::hasColumn('leads', 'designation')) {
                $table->string('designation', 150)->nullable()->after('contact_person');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            if (Schema::hasColumn('leads', 'mode_of_approach')) {
                $table->dropColumn('mode_of_approach');
            }
            if (Schema::hasColumn('leads', 'designation')) {
                $table->dropColumn('designation');
            }
        });
    }
};
