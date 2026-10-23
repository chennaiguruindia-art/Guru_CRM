<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * System Settings is the Super Admin's alone unless the Super Admin opens it
 * to Admin and to the field marketers from Settings → Access.
 *
 * The switches are deliberately not reachable through Roles & Permissions: a
 * permission row would let any Admin widen their own reach, whereas a switch
 * only the Super Admin can throw keeps that decision where it belongs.
 */
class SettingsAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
    }

    private function userWithRole(string $roleSlug): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->roles()->attach(Role::where('slug', $roleSlug)->firstOrFail());

        return $user;
    }

    /**
     * Flip one of the Access switches the way Settings → Access would.
     */
    private function grant(string $key): void
    {
        Setting::updateOrCreate(['key' => $key], ['value' => 'yes', 'group' => 'access']);
    }

    public function test_the_super_admin_always_reaches_settings(): void
    {
        $this->actingAs($this->userWithRole('super-admin'))
            ->get('/settings')->assertOk();
    }

    public function test_settings_starts_closed_to_admin_and_to_sales(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->get('/settings')->assertForbidden();

        $this->actingAs($this->userWithRole('fieldmarketer'))
            ->get('/settings')->assertForbidden();
    }

    /**
     * Being shut out has to be visible, not just enforced — otherwise an Admin
     * is left hunting for a menu entry that is not there.
     */
    public function test_the_sidebar_offers_settings_only_once_it_is_granted(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)->get('/dashboard')
            ->assertDontSee('System Settings');

        $this->grant('settings_access_admin');

        $this->actingAs($admin->fresh())
            ->get('/dashboard')
            ->assertSee('System Settings');
    }

    public function test_granting_admin_leaves_sales_shut(): void
    {
        $this->grant('settings_access_admin');

        $this->actingAs($this->userWithRole('admin'))
            ->get('/settings')->assertOk();

        $this->actingAs($this->userWithRole('fieldmarketer'))
            ->get('/settings')->assertForbidden();
    }

    public function test_granting_sales_leaves_admin_shut(): void
    {
        $this->grant('settings_access_sales');

        $this->actingAs($this->userWithRole('fieldmarketer'))
            ->get('/settings')->assertOk();

        $this->actingAs($this->userWithRole('admin'))
            ->get('/settings')->assertForbidden();
    }

    public function test_both_can_be_opened_at_the_same_time(): void
    {
        $this->grant('settings_access_admin');
        $this->grant('settings_access_sales');

        $this->actingAs($this->userWithRole('admin'))->get('/settings')->assertOk();
        $this->actingAs($this->userWithRole('fieldmarketer'))->get('/settings')->assertOk();
    }

    /**
     * They are independent switches, not a shared state — closing one must not
     * disturb the other.
     */
    public function test_closing_one_role_leaves_the_other_open(): void
    {
        $this->grant('settings_access_admin');
        $this->grant('settings_access_sales');

        Setting::where('key', 'settings_access_admin')->update(['value' => 'no']);

        $this->actingAs($this->userWithRole('fieldmarketer'))->get('/settings')->assertOk();
        $this->actingAs($this->userWithRole('admin'))->get('/settings')->assertForbidden();
    }

    public function test_only_the_super_admin_is_shown_the_access_tab(): void
    {
        $this->grant('settings_access_admin');

        $this->actingAs($this->userWithRole('super-admin'))
            ->get('/settings')->assertOk()->assertSee('Who can open System Settings');

        // Let in, but not shown the switches that decide who else is.
        $this->actingAs($this->userWithRole('admin'))
            ->get('/settings')->assertOk()->assertDontSee('Who can open System Settings');
    }

    /**
     * The point of the whole arrangement: being let in must not be a way to
     * let anybody else in.
     */
    public function test_an_admin_who_is_let_in_cannot_hand_settings_on(): void
    {
        $this->grant('settings_access_admin');
        $admin = $this->userWithRole('admin');

        // Straight at the switch, with the payload the form would send.
        $this->actingAs($admin)->postJson('/settings/module', [
            'key' => 'settings_access_sales',
            'value' => 'yes',
        ])->assertStatus(403);

        $this->assertDatabaseMissing('settings', [
            'key' => 'settings_access_sales',
            'value' => 'yes',
        ]);

        // …and through the ordinary Save button, which would otherwise let a
        // crafted form post slip the flag past the hidden field.
        $this->actingAs($admin)->post('/settings', [
            'company_name' => 'Changed by an admin',
            'settings_access_sales' => 'yes',
        ])->assertRedirect();

        $this->assertDatabaseHas('settings', ['key' => 'company_name', 'value' => 'Changed by an admin']);
        $this->assertDatabaseMissing('settings', [
            'key' => 'settings_access_sales',
            'value' => 'yes',
        ]);
    }

    public function test_the_super_admin_can_hand_access_on(): void
    {
        $this->actingAs($this->userWithRole('super-admin'))->postJson('/settings/module', [
            'key' => 'settings_access_sales',
            'value' => 'yes',
        ])->assertOk();

        $this->assertDatabaseHas('settings', [
            'key' => 'settings_access_sales',
            'value' => 'yes',
        ]);
    }

    /**
     * The Access switches share their endpoint with the Modules tab, so that
     * tab must not have been disturbed by the new key set.
     */
    public function test_the_module_switches_still_work(): void
    {
        $this->actingAs($this->userWithRole('super-admin'))->postJson('/settings/module', [
            'key' => 'module_reports_analytics',
            'value' => 'no',
        ])->assertOk();

        $this->assertDatabaseHas('settings', [
            'key' => 'module_reports_analytics',
            'value' => 'no',
        ]);

        $this->actingAs($this->userWithRole('super-admin'))->postJson('/settings/module', [
            'key' => 'not_a_real_switch',
            'value' => 'yes',
        ])->assertStatus(422);
    }
}
