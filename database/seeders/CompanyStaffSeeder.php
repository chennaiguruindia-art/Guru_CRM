<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Role;
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

        // ========================================================
        // A. SUPER ADMIN (System User - No Employee Record)
        // ========================================================
        $superAdmin = User::create([
            'name' => 'Guru Super Admin',
            'email' => 'superadmin@guru.com',
            'username' => 'superadmin',
            'phone' => '9361888174',
            'password' => $defaultPassword,
            'status' => 'active',
        ]);
        $superAdmin->assignRole('super-admin');

        // ========================================================
        // B. BRANCH ADMINS (System Users - No Employee Record)
        // ========================================================

        // 1. Chennai Branch Admin
        $chennaiAdmin = User::create([
            'name' => 'Chennai Branch Admin',
            'email' => 'admin.chennai@guru.com',
            'username' => 'admin.chennai',
            'phone' => '9000000001',
            'branch_id' => $chennai->id,
            'password' => $defaultPassword,
            'status' => 'active',
        ]);
        $chennaiAdmin->assignRole('admin');

        // 2. Bangalore Branch Admin
        $bangaloreAdmin = User::create([
            'name' => 'Bangalore Branch Admin',
            'email' => 'admin.bangalore@guru.com',
            'username' => 'admin.bangalore',
            'phone' => '9000000002',
            'branch_id' => $bangalore->id,
            'password' => $defaultPassword,
            'status' => 'active',
        ]);
        $bangaloreAdmin->assignRole('admin');

        // 3. Coimbatore Branch Admin
        $coimbatoreAdmin = User::create([
            'name' => 'Coimbatore Branch Admin',
            'email' => 'admin.coimbatore@guru.com',
            'username' => 'admin.coimbatore',
            'phone' => '9000000003',
            'branch_id' => $coimbatore->id,
            'password' => $defaultPassword,
            'status' => 'active',
        ]);
        $coimbatoreAdmin->assignRole('admin');

        // ========================================================
        // C. CHENNAI BRANCH EMPLOYEES (On Roster / Attendance Table)
        // ========================================================

        // 1. Chennai - Sales / Field Marketer (HAS CRM ACCESS TO LOG LEADS)
        $cheSalesUser = User::create([
            'name' => 'Suresh Kumar',
            'email' => 'suresh.sales@guru.com',
            'username' => 'EMP-0001',
            'phone' => '9100000001',
            'branch_id' => $chennai->id,
            'password' => $defaultPassword,
            'status' => 'active',
        ]);
        $cheSalesUser->assignRole('fieldmarketer');

        Employee::create([
            'employee_code' => 'EMP-0001',
            'user_id' => $cheSalesUser->id,
            'branch_id' => $chennai->id,
            'name' => 'Suresh Kumar',
            'phone' => '9100000001',
            'email' => 'suresh.sales@guru.com',
            'designation' => 'Sales Executive',
            'department' => 'Sales',
            'employee_type' => 'Full-Time',
            'salary' => 35000,
            'joining_date' => '2024-03-01',
            'status' => 'active',
        ]);

        // 2. Chennai - Software Developer (ATTENDANCE ONLY, NO CRM ACCESS)
        $cheDevUser = User::create([
            'name' => 'Praveen Raj',
            'email' => 'praveen.dev@guru.com',
            'username' => 'EMP-0002',
            'phone' => '9100000002',
            'branch_id' => $chennai->id,
            'password' => $defaultPassword,
            'status' => 'active',
        ]);
        $cheDevUser->assignRole('employee');

        Employee::create([
            'employee_code' => 'EMP-0002',
            'user_id' => $cheDevUser->id,
            'branch_id' => $chennai->id,
            'name' => 'Praveen Raj',
            'phone' => '9100000002',
            'email' => 'praveen.dev@guru.com',
            'designation' => 'Software Developer',
            'department' => 'Operations',
            'employee_type' => 'Full-Time',
            'salary' => 45000,
            'joining_date' => '2024-03-15',
            'status' => 'active',
        ]);

        // 3. Chennai - HR (ATTENDANCE ONLY, NO CRM ACCESS)
        $cheHrUser = User::create([
            'name' => 'Anitha M',
            'email' => 'anitha.hr@guru.com',
            'username' => 'EMP-0003',
            'phone' => '9100000003',
            'branch_id' => $chennai->id,
            'password' => $defaultPassword,
            'status' => 'active',
        ]);
        $cheHrUser->assignRole('employee');

        Employee::create([
            'employee_code' => 'EMP-0003',
            'user_id' => $cheHrUser->id,
            'branch_id' => $chennai->id,
            'name' => 'Anitha M',
            'phone' => '9100000003',
            'email' => 'anitha.hr@guru.com',
            'designation' => 'HR Executive',
            'department' => 'Administration',
            'employee_type' => 'Full-Time',
            'salary' => 38000,
            'joining_date' => '2024-04-01',
            'status' => 'active',
        ]);

        // ========================================================
        // D. BANGALORE BRANCH EMPLOYEES (On Roster / Attendance Table)
        // ========================================================

        // 1. Bangalore - Software Developer (ATTENDANCE ONLY, NO CRM ACCESS)
        $blrDevUser = User::create([
            'name' => 'Karthik N',
            'email' => 'karthik.dev@guru.com',
            'username' => 'EMP-0004',
            'phone' => '9200000001',
            'branch_id' => $bangalore->id,
            'password' => $defaultPassword,
            'status' => 'active',
        ]);
        $blrDevUser->assignRole('employee');

        Employee::create([
            'employee_code' => 'EMP-0004',
            'user_id' => $blrDevUser->id,
            'branch_id' => $bangalore->id,
            'name' => 'Karthik N',
            'phone' => '9200000001',
            'email' => 'karthik.dev@guru.com',
            'designation' => 'Software Developer',
            'department' => 'Operations',
            'employee_type' => 'Full-Time',
            'salary' => 48000,
            'joining_date' => '2024-04-10',
            'status' => 'active',
        ]);
    }
}
