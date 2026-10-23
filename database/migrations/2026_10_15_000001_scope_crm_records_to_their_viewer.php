<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A CRM record belongs to the person who entered it, and to their branch.
     *
     * A visit already carried both. A client did not, so the Clients list was
     * the same for everybody who could open it — a field marketer could read
     * clients their own colleagues had brought in, and an Admin could read
     * another branch's. Clients now carry the branch and the filer of the
     * visit they came from, so App\Models\Scopes\RecordVisibilityScope can
     * hold every CRM record to one rule: the Super Admin, the Admin of its
     * branch, and whoever entered it.
     *
     * A visit's creator is filled in from whoever it is assigned to, which for
     * every visit on the books is the salesperson who logged it.
     */
    public function up(): void
    {
        $this->backfillLeadCreators();

        if (! Schema::hasColumn('customers', 'branch_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->foreignId('branch_id')->nullable()
                    ->constrained('branches')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('customers', 'created_by_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->foreignId('created_by_id')->nullable()
                    ->constrained('users')->nullOnDelete();
            });
        }

        $this->backfillClients();
    }

    /**
     * A visit with nobody recorded as its author is invisible to every
     * salesperson once the scope lands, so fill in the one person who
     * certainly logged it before that happens.
     */
    private function backfillLeadCreators(): void
    {
        DB::table('leads')
            ->whereNull('created_by_id')
            ->whereNotNull('assigned_to_id')
            ->orderBy('id')
            ->update(['created_by_id' => DB::raw('assigned_to_id')]);
    }

    /**
     * Clients are built from visits, so they take the branch and the author
     * of the visit they came from. Anything with no link to a person stays
     * unbranched and authorless, and remains readable by the Super Admin only.
     */
    private function backfillClients(): void
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

        DB::table('customers')
            ->whereNull('created_by_id')
            ->whereNotNull('assigned_to_id')
            ->orderBy('id')
            ->update(['created_by_id' => DB::raw('assigned_to_id')]);

        DB::table('customers')
            ->whereNull('branch_id')
            ->whereNotNull('created_by_id')
            ->orderBy('id')
            ->get(['id', 'created_by_id'])
            ->each(function ($customer) use ($branchFor) {
                $branch = $branchFor((int) $customer->created_by_id);

                if ($branch) {
                    DB::table('customers')->where('id', $customer->id)->update(['branch_id' => $branch]);
                }
            });
    }

    public function down(): void
    {
        if (Schema::hasColumn('customers', 'created_by_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropConstrainedForeignId('created_by_id');
            });
        }

        if (Schema::hasColumn('customers', 'branch_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropConstrainedForeignId('branch_id');
            });
        }
    }
};
