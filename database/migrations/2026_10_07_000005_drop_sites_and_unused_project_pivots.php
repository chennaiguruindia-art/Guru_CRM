<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Everything the removed Sites module and an unbuilt feature left behind.
     *
     * Dropped child-first: `site_visit_photos` hangs off `site_visits`, and
     * both hang off `sites`, so the parents can only go once the children are
     * gone. `project_plants` / `project_materials` were never given a model,
     * a route or a view — they are empty pivots for a feature that was never
     * finished.
     */
    private const TABLES = [
        'site_visit_photos',
        'site_visits',
        'project_plants',
        'project_materials',
        'sites',
    ];

    /**
     * Surviving tables that still declare `site_id` with a foreign key to
     * `sites`. Every value is NULL except one legacy quotation, so the column
     * comes off before the parent table can be dropped at all — MySQL will
     * refuse to drop a table that other tables still reference.
     */
    private const WITH_SITE_ID = [
        'complaints',
        'estimations',
        'expenses',
        'invoices',
        'maintenance_contracts',
        'maintenance_records',
        'maintenance_schedules',
        'projects',
        'project_tasks',
        'quotations',
        'work_orders',
    ];

    public function up(): void
    {
        // Two passes per table: the constraint has to be gone before the
        // column it guards, otherwise MySQL reports a missing foreign key.
        foreach (self::WITH_SITE_ID as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            if (Schema::hasColumn($table, 'site_id')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropForeign(['site_id']);
                });
            }

            if (Schema::hasColumn($table, 'site_id')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropColumn('site_id');
                });
            }
        }

        foreach (self::TABLES as $table) {
            Schema::dropIfExists($table);
        }
    }

    public function down(): void
    {
        // Deliberately empty. Recreating these would resurrect a module the
        // application no longer has, and `sites` held a single seeded row.
        // A full schema is still available by reverting the original
        // create_* migrations this one undoes.
    }
};
