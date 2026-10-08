<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A field marketer photographs the visiting card they collect on a hotel /
     * apartment / villa visit, so the contact is recoverable even if the typed
     * details are wrong or incomplete.
     */
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            if (! Schema::hasColumn('leads', 'visiting_card_photo')) {
                $table->string('visiting_card_photo')->nullable()->after('remarks');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            if (Schema::hasColumn('leads', 'visiting_card_photo')) {
                $table->dropColumn('visiting_card_photo');
            }
        });
    }
};
