<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PurchaseOrder uses SoftDeletes, but the create migration never emitted a
 * deleted_at column for purchase_orders — every other soft-deleting table in
 * the schema has one. Eloquent's SoftDeletingScope then failed every query on
 * /purchases with "Unknown column 'purchase_orders.deleted_at'".
 *
 * The create migration now includes softDeletes() for fresh installs, so this
 * one is guarded: it only applies to databases created before that fix.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('purchase_orders', 'deleted_at')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('purchase_orders', 'deleted_at')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
