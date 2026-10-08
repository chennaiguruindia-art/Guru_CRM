<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The Sites / Properties module was removed from the application, so no
 * maintenance form offers a site any more. These three columns were created
 * NOT NULL and would reject every insert once the field disappeared.
 *
 * Only the nullability changes — no column, index or row is dropped, so all
 * existing site-linked records keep their values and stay pointed at sites.
 */
return new class extends Migration
{
    private const TABLES = [
        'maintenance_contracts',
        'maintenance_schedules',
        'maintenance_records',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('site_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('site_id')->nullable(false)->change();
            });
        }
    }
};
