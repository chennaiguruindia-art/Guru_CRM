<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            // Drop global unique constraint on lead_code
            $table->dropUnique('leads_lead_code_unique');
            // Scope uniqueness to branch
            $table->unique(['branch_id', 'lead_code']);
        });

        // Renumber existing leads per branch so every branch starts from 0001
        $branches = DB::table('branches')->pluck('id');
        foreach ($branches as $branchId) {
            // Check if this branch needs renumbering (e.g. branch 1 Chennai having LEAD-0026, LEAD-0027)
            $leads = DB::table('leads')
                ->where('branch_id', $branchId)
                ->whereNull('deleted_at')
                ->orderBy('id', 'asc')
                ->get();

            // Only renumber if the first lead is not LEAD-0001
            if ($leads->isNotEmpty() && $leads->first()->lead_code !== 'LEAD-0001') {
                $num = 1;
                foreach ($leads as $lead) {
                    $newCode = 'LEAD-' . str_pad((string) $num, 4, '0', STR_PAD_LEFT);
                    DB::table('leads')->where('id', $lead->id)->update(['lead_code' => $newCode]);
                    $num++;
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropUnique(['branch_id', 'lead_code']);
            $table->unique('lead_code');
        });
    }
};
