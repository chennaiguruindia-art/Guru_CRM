<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indexes = collect(DB::select("SHOW INDEXES FROM leads"))->pluck('Key_name')->unique();

        // 1. Add index on branch_id first so foreign key check is satisfied when unique index is dropped
        if (! $indexes->contains('leads_branch_id_index')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->index('branch_id', 'leads_branch_id_index');
            });
        }

        // 2. Drop old unique index
        Schema::table('leads', function (Blueprint $table) use ($indexes) {
            if ($indexes->contains('leads_branch_id_lead_code_unique')) {
                $table->dropUnique('leads_branch_id_lead_code_unique');
            } elseif ($indexes->contains('leads_lead_code_unique')) {
                $table->dropUnique('leads_lead_code_unique');
            }
        });

        // 3. Ensure any lead with null/empty source has a fallback so grouping works properly
        DB::table('leads')->whereNull('source')->orWhere('source', '')->update(['source' => 'Cold Call']);

        // 4. Temporarily prefix lead_code with TMP- to avoid collision during renumbering
        DB::table('leads')->update([
            'lead_code' => DB::raw("CONCAT('TMP-', id)")
        ]);

        // 5. Renumber existing leads per branch and per channel (source) so each section starts from LEAD-0001
        $branches = DB::table('branches')->pluck('id');
        $channels = DB::table('leads')->distinct()->pluck('source');

        foreach ($branches as $branchId) {
            foreach ($channels as $channel) {
                $leads = DB::table('leads')
                    ->where('branch_id', $branchId)
                    ->where('source', $channel)
                    ->orderBy('id', 'asc')
                    ->get();

                $num = 1;
                foreach ($leads as $lead) {
                    $newCode = 'LEAD-' . str_pad((string) $num, 4, '0', STR_PAD_LEFT);
                    DB::table('leads')->where('id', $lead->id)->update(['lead_code' => $newCode]);
                    $num++;
                }
            }
        }

        // 6. Now add the unique constraint scoped to branch_id, source, and lead_code
        $updatedIndexes = collect(DB::select("SHOW INDEXES FROM leads"))->pluck('Key_name')->unique();
        if (! $updatedIndexes->contains('leads_branch_id_source_lead_code_unique')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->unique(['branch_id', 'source', 'lead_code']);
            });
        }
    }

    public function down(): void
    {
        $indexes = collect(DB::select("SHOW INDEXES FROM leads"))->pluck('Key_name')->unique();

        Schema::table('leads', function (Blueprint $table) use ($indexes) {
            if ($indexes->contains('leads_branch_id_source_lead_code_unique')) {
                $table->dropUnique('leads_branch_id_source_lead_code_unique');
            }
            if (! $indexes->contains('leads_branch_id_lead_code_unique')) {
                $table->unique(['branch_id', 'lead_code']);
            }
        });
    }
};
