<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Estimation;
use App\Models\Quotation;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The Sites module is gone: five tables and the `site_id` column that eleven
 * other tables carried have been dropped from the schema. Quotations and
 * estimations are the records that used to accept a site, so they are the ones
 * that would notice — a leftover `exists:sites,id` rule would query a table
 * that no longer exists.
 */
class SalesWithoutSitesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->roles()->attach(Role::where('slug', 'super-admin')->firstOrFail());

        return $user;
    }

    private function customer(): Customer
    {
        static $n = 0;
        $n++;

        return Customer::create([
            'customer_code' => 'CUST-NOSITE-' . $n,
            'name' => 'Customer Without Site ' . $n,
            'phone' => '9876500' . str_pad((string) $n, 4, '0', STR_PAD_LEFT),
        ]);
    }

    public function test_the_dropped_tables_and_columns_are_really_gone(): void
    {
        foreach (['sites', 'site_visits', 'site_visit_photos', 'project_plants', 'project_materials'] as $table) {
            $this->assertFalse(Schema::hasTable($table), "{$table} should have been dropped.");
        }

        foreach (['quotations', 'estimations', 'projects', 'invoices', 'work_orders'] as $table) {
            $this->assertFalse(Schema::hasColumn($table, 'site_id'), "{$table}.site_id should have been dropped.");
        }
    }

    public function test_a_quotation_can_still_be_raised_without_a_site(): void
    {
        $actor = $this->superAdmin();
        $customer = $this->customer();

        $this->actingAs($actor)->post('/quotations', [
            'customer_id' => $customer->id,
            'date' => '2026-10-08',
            'valid_until' => '2026-11-07',
            'status' => 'Draft',
            'tax_percent' => 18,
            'terms' => 'Prices valid for 30 days.',
            'items' => [[
                'item_type' => 'Plant',
                'item_name' => 'Areca Palm',
                'quantity' => 10,
                'unit' => 'Nos',
                'unit_price' => 250,
                'tax_percent' => 18,
            ]],
        ])->assertSessionHasNoErrors();

        $quotation = Quotation::latest('id')->firstOrFail();
        $this->assertSame($customer->id, $quotation->customer_id);
        $this->assertDatabaseCount('quotation_items', 1);
    }

    public function test_an_estimation_can_still_be_raised_without_a_site(): void
    {
        $actor = $this->superAdmin();
        $customer = $this->customer();

        $this->actingAs($actor)->post('/estimations', [
            'customer_id' => $customer->id,
            'title' => 'Terrace Garden Estimate',
            'date' => '2026-10-08',
            'plants_total' => 12000,
            'notes' => 'No site involved.',
        ])->assertSessionHasNoErrors();

        $estimation = Estimation::latest('id')->firstOrFail();
        $this->assertSame($customer->id, $estimation->customer_id);
        $this->assertSame('Terrace Garden Estimate', $estimation->title);
    }

    public function test_a_stray_site_id_is_ignored_instead_of_hitting_a_missing_table(): void
    {
        $actor = $this->superAdmin();
        $customer = $this->customer();

        // No form sends site_id any more. Had the old rule survived, this
        // would raise a query exception against a table that is not there.
        $this->actingAs($actor)->post('/quotations', [
            'customer_id' => $customer->id,
            'site_id' => 999,
            'date' => '2026-10-08',
            'status' => 'Draft',
            'tax_percent' => 18,
            'items' => [[
                'item_type' => 'Material',
                'item_name' => 'Drip Irrigation Kit',
                'quantity' => 1,
                'unit' => 'Set',
                'unit_price' => 4500,
                'tax_percent' => 18,
            ]],
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('quotations', ['customer_id' => $customer->id]);
    }
}
