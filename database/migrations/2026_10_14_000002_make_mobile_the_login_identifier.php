<?php

use App\Support\Phone;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * People sign in with a mobile number instead of an email address.
     *
     * The sales team shares a single inbox, so an email address does not
     * identify a person — a mobile number does, and one number can only ever
     * belong to one login. Hence the unique index.
     */
    public function up(): void
    {
        // Numbers already on file may have been typed in any shape over the
        // years, so settle them into the one form login compares against.
        $seen = [];

        foreach (DB::table('users')->orderBy('id')->pluck('phone', 'id') as $id => $phone) {
            $normalized = Phone::normalize($phone);

            if ($normalized === null) {
                continue;
            }

            // Two logins cannot share a number — that is the whole point of
            // this change. The later one is left without, so an admin gives
            // them their own rather than somebody else's.
            if (isset($seen[$normalized])) {
                DB::table('users')->where('id', $id)->update(['phone' => null]);

                continue;
            }

            $seen[$normalized] = $id;

            if ($normalized !== $phone) {
                DB::table('users')->where('id', $id)->update(['phone' => $normalized]);
            }
        }

        if (! Schema::hasIndex('users', 'users_phone_unique')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('phone');
            });
        }
    }

    /**
     * The phones are left in their normalised form on the way back: there is
     * no record of how they were typed originally to restore.
     */
    public function down(): void
    {
        if (Schema::hasIndex('users', 'users_phone_unique')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique(['phone']);
            });
        }
    }
};
