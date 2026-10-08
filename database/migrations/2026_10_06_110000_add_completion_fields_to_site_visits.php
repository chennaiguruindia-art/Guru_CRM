<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets the person who was sent out close a visit from their dashboard
 * notification, and lets Admin see when it was actually done.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_visits', function (Blueprint $table) {
            $table->text('completion_note')->nullable()->after('remarks');
            $table->timestamp('completed_at')->nullable()->after('completion_note');
        });
    }

    public function down(): void
    {
        Schema::table('site_visits', function (Blueprint $table) {
            $table->dropColumn(['completion_note', 'completed_at']);
        });
    }
};
