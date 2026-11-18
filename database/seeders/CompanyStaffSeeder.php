<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CompanyStaffSeeder extends Seeder
{
    /**
     * Run the company staff seeder.
     *
     * Super Admin and Branch Admins are system/management accounts:
     * - They are NOT added to the employees table.
     * - They do NOT require attendance or salary tracking.
     *
     * Only operational staff (Sales, HR, Developers, etc.) are in the employees table
     * and have daily attendance tracking.
     */
    public function run(): void
    {
        // 1. Ensure Roles & Permissions are present
        $this->call([
            RoleAndPermissionSeeder::class,
            BranchSeeder::class,
        ]);

        $chennai = Branch::where('name', 'Chennai')->first();
        $bangalore = Branch::where('name', 'Bangalore')->first();
        $coimbatore = Branch::where('name', 'Coimbatore')->first();

        // 2. Clear previous test records safely
        Schema::disableForeignKeyConstraints();
        DB::table('attendances')->truncate();
        DB::table('role_user')->truncate();
        DB::table('employees')->truncate();
        DB::table('users')->truncate();
        Schema::enableForeignKeyConstraints();

        $defaultPassword = 'password'; // universal password for all seeded accounts

        // Branch admin accounts
        $chennaiAdmin = User::create([
            'name' => 'Ponarasi',
            'email' => 'admin.chennai@guru.com',
            'username' => 'admin.chennai',
            'phone' => '9176625631',
            'branch_id' => $chennai->id,
            'password' => $defaultPassword,
            'status' => 'active',
        ]);
        $chennaiAdmin->assignRole('admin');

        $bangaloreAdmin = User::create([
            'name' => 'Keerthana',
            'email' => 'admin.bangalore@guru.com',
            'username' => 'admin.bangalore',
            'phone' => '9742250341',
            'branch_id' => $bangalore->id,
            'password' => $defaultPassword,
            'status' => 'active',
        ]);
        $bangaloreAdmin->assignRole('admin');

        $coimbatoreAdmin = User::create([
            'name' => 'Sathish',
            'email' => 'admin.coimbatore@guru.com',
            'username' => 'admin.coimbatore',
            'phone' => '9876543210',
            'branch_id' => $coimbatore->id,
            'password' => $defaultPassword,
            'status' => 'active',
        ]);
        $coimbatoreAdmin->assignRole('admin');
    }
}
