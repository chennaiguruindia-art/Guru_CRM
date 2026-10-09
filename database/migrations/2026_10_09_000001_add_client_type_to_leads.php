<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Whether a Visit is against a brand new prospect or somebody already in
     * Clients. Recorded but never required — a filer arriving on site may not
     * know either way, which is why the column is nullable.
     */
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            if (! Schema::hasColumn('leads', 'client_type')) {
                $table->string('client_type')->nullable()->after('source');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            if (Schema::hasColumn('leads', 'client_type')) {
                $table->dropColumn('client_type');
            }
        });
    }
};
