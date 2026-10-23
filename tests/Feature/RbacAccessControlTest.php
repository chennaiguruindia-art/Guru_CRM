<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers the access-control rules agreed for this system:
 *
 *   - Super Admin / Admin reach every module.
 *   - A Field Marketer reaches Cold Calls and nothing else in CRM & Clients:
 *     Promotion Email / Call, Existing Client Visit and Client are Admin and
 *     above, and so is converting a Visit into one.
 *   - Assigning a person provisions a login whose sign-in identifier is the
 *     mobile number on the roster.
 */
class RbacAccessControlTest extends TestCase
{
    use RefreshDatabase;

    private function seedRbac(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
    }

    private function userWithRole(string $roleSlug): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->roles()->attach(Role::where('slug', $roleSlug)->firstOrFail());

        return $user;
    }

    public function test_fieldmarketer_is_confined_to_cold_calls(): void
    {
        $this->seedRbac();
        $sales = $this->userWithRole('fieldmarketer');

        // Cold Calls is the one CRM entry they get, and it carries every Visit.
        $this->actingAs($sales)->get('/cold-calls')->assertOk();
        $this->actingAs($sales)->get('/leads')->assertOk();
        $this->actingAs($sales)->get('/leads/create')->assertOk();

        // The other three entries in CRM & Clients are Admin and above.
        $this->actingAs($sales)->get('/promotion-emails')->assertForbidden();
        $this->actingAs($sales)->get('/existing-client-visits')->assertForbidden();
        $this->actingAs($sales)->get('/customers')->assertForbidden();
    }

    public function test_fieldmarketer_is_denied_the_rest_of_sales_and_projects(): void
    {
        $this->seedRbac();
        $sales = $this->userWithRole('fieldmarketer');

        foreach ([
            '/projects', '/estimations', '/quotations',
            '/reports', '/invoices', '/payments', '/employees', '/users',
            '/branches', '/roles', '/settings',
        ] as $uri) {
            $this->actingAs($sales)->get($uri)->assertForbidden();
        }
    }

    public function test_admin_retains_access_to_every_module(): void
    {
        $this->seedRbac();
        $admin = $this->userWithRole('admin');

        foreach ([
            '/dashboard', '/leads', '/customers', '/projects', '/estimations',
            '/quotations', '/branches', '/users', '/roles', '/reports', '/settings',
        ] as $uri) {
            $this->actingAs($admin)->get($uri)->assertOk();
        }
    }

    public function test_super_admin_bypasses_permission_checks_entirely(): void
    {
        $this->seedRbac();
        $super = $this->userWithRole('super-admin');

        $this->actingAs($super)->get('/branches')->assertOk();
        $this->actingAs($super)->get('/projects')->assertOk();
        $this->actingAs($super)->get('/users')->assertOk();
    }

    public function test_admin_can_create_a_branch(): void
    {
        $this->seedRbac();
        $admin = $this->userWithRole('admin');

        $response = $this->actingAs($admin)->post('/branches', [
            'name' => 'Test Branch',
            'location' => 'Indiranagar',
            'phone' => '+91 90000 00000',
            'status' => 'active',
            'notes' => null,
        ]);

        $response->assertRedirect('/branches');
        $this->assertDatabaseHas('branches', ['name' => 'Test Branch']);
        $this->assertNotNull(
            \App\Models\Branch::firstWhere('name', 'Test Branch')->code,
            'A branch code is generated automatically.'
        );
    }

    public function test_assigning_a_person_provisions_a_mobile_login(): void
    {
        $this->seedRbac();
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)->post('/employees', [
            'name' => 'Provisioned Person',
            'phone' => '+91 12345 67890',
            'designation' => 'Sales Executive',
            'department' => 'Sales',
            'employee_type' => 'Full-Time',
            'salary' => 25000,
            'create_login' => 1,
            'role' => 'fieldmarketer',
            'password' => 'secret123',
        ])->assertRedirect('/employees');

        $employee = Employee::firstWhere('name', 'Provisioned Person');

        $this->assertNotNull($employee, 'The person was created.');
        $this->assertNotNull($employee->user_id, 'A login was provisioned.');

        // The roster mobile is carried across in its stored form — country
        // code dropped — so it is what this person signs in with.
        $this->assertSame('1234567890', $employee->user->phone);

        $this->post('/login', [
            'phone' => $employee->phone,
            'password' => 'secret123',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticated();
    }

    public function test_login_by_mobile_rejects_a_wrong_password(): void
    {
        $this->seedRbac();

        // Built directly rather than through the HTTP layer: actingAs() would
        // leave us authenticated, and /login is guest-only.
        $employee = Employee::create([
            'employee_code' => 'EMP-9999',
            'name' => 'Wrong Password Person',
            'phone' => '1234567891',
            'designation' => 'Sales Executive',
            'department' => 'Sales',
            'employee_type' => 'Full-Time',
            'status' => 'active',
        ]);

        $user = User::create([
            'name' => 'Wrong Password Person',
            'email' => 'wrongpw@example.com',
            'username' => 'EMP-9999',
            'phone' => '1234567891',
            'password' => 'secret123',
            'status' => 'active',
        ]);

        $employee->update(['user_id' => $user->id]);

        $this->post('/login', ['phone' => '1234567891', 'password' => 'not-the-password'])
            ->assertSessionHasErrors('phone');

        $this->assertGuest();

        // ...and the correct password does sign them in.
        $this->post('/login', ['phone' => '1234567891', 'password' => 'secret123'])
            ->assertRedirect('/dashboard');

        $this->assertAuthenticated();
    }

    public function test_an_inactive_account_cannot_sign_in(): void
    {
        $this->seedRbac();

        User::factory()->create([
            'email' => 'fired@example.com',
            'phone' => '9999999990',
            'password' => bcrypt('secret123'),
            'status' => 'inactive',
        ]);

        $this->post('/login', [
            'phone' => '9999999990',
            'password' => 'secret123',
        ])->assertSessionHasErrors('phone');

        $this->assertGuest();
    }

    public function test_admin_may_create_admin_and_fieldmarketer_sub_users(): void
    {
        $this->seedRbac();
        $admin = $this->userWithRole('admin');

        $response = $this->actingAs($admin)->get('/users/create');

        $response->assertOk();
        $response->assertSee('Field Marketer', false);
        $response->assertDontSee('Super Admin', false);

        $this->actingAs($admin)->post('/users', [
            'name' => 'Second Admin',
            'email' => 'admin2@example.com',
            'phone' => '9876500001',
            'password' => 'secret123',
            'role_id' => Role::where('slug', 'admin')->firstOrFail()->id,
        ])->assertRedirect('/users');

        $this->assertDatabaseHas('users', ['email' => 'admin2@example.com']);
    }

    public function test_super_admin_role_cannot_be_handed_out(): void
    {
        $this->seedRbac();
        $admin = $this->userWithRole('admin');

        // The real id, submitted by hand — Rule::in() is what must stop it.
        $this->actingAs($admin)->post('/users', [
            'name' => 'Sneaky',
            'email' => 'sneaky@example.com',
            'phone' => '9876500002',
            'password' => 'secret123',
            'role_id' => Role::where('slug', 'super-admin')->firstOrFail()->id,
        ])->assertSessionHasErrors('role_id');

        $this->assertDatabaseMissing('users', ['email' => 'sneaky@example.com']);
    }
}
