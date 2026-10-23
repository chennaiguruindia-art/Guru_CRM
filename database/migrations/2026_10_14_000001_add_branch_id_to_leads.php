<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A visit belongs to the branch it was filed from.
     *
     * Until now a branch was only a label on the employee record — the original
     * migration said so plainly — so a field marketer in Bangalore could read
     * every other branch's visits. Each visit now carries the branch of
     * whoever logged it, and App\Models\Scopes\RecordVisibilityScope holds everybody to
     * their own.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('leads', 'branch_id')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->foreignId('branch_id')->nullable()
                    ->constrained('branches')->nullOnDelete();
            });
        }

        $this->backfill();
    }

    /**
     * Existing visits take the branch of whoever they are assigned to, falling
     * back to whoever logged them, so that nothing already on the books becomes
     * invisible overnight. Anything with no link to a person stays unbranched
     * and remains an Administration-only record.
     */
    private function backfill(): void
    {
        $cache = [];

        $branchFor = function (int $userId) use (&$cache) {
            if (array_key_exists($userId, $cache)) {
                return $cache[$userId];
            }

            return $cache[$userId] = DB::table('employees')
                ->where('user_id', $userId)
                ->whereNotNull('branch_id')
                ->value('branch_id');
        };

        DB::table('leads')
            ->whereNull('branch_id')
            ->orderBy('id')
            ->get(['id', 'assigned_to_id', 'created_by_id'])
            ->each(function ($lead) use ($branchFor) {
                $branch = ($lead->assigned_to_id ? $branchFor((int) $lead->assigned_to_id) : null)
                    ?? ($lead->created_by_id ? $branchFor((int) $lead->created_by_id) : null);

                if ($branch) {
                    DB::table('leads')->where('id', $lead->id)->update(['branch_id' => $branch]);
                }
            });
    }

    public function down(): void
    {
        if (Schema::hasColumn('leads', 'branch_id')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->dropConstrainedForeignId('branch_id');
            });
        }
    }
};
