<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Purpose of the visit, and the engagement type it should turn into when
     * converted (AMC contract vs a single project). The latter drives what
     * LeadConversionService creates.
     */
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            if (! Schema::hasColumn('leads', 'purpose_of_visit')) {
                $table->string('purpose_of_visit')->nullable()->after('source');
            }

            if (! Schema::hasColumn('leads', 'service_type')) {
                $table->string('service_type')->nullable()->after('purpose_of_visit');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            foreach (['service_type', 'purpose_of_visit'] as $column) {
                if (Schema::hasColumn('leads', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
