<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\MaintenanceContract;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * CRM & Clients holds exactly four entries and no longer has a Visits one:
 *
 *   - Cold Calls / Direct Call  — carries every Visit; the only one a Field
 *     Marketer sees.
 *   - Promotion Email / Call    — Admin and above.
 *   - Existing Client Visit     — Admin and above.
 *   - Client                    — Admin and above.
 *
 * All four are the one Visit record, the channel is stamped from the page the
 * form was opened on, and converting one into a Client is Super Admin only.
 */
class VisitChannelsTest extends TestCase
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

    private function sidebarFor(string $roleSlug): string
    {
        $this->actingAs($this->userWithRole($roleSlug));

        return view('components.sidebar')->render();
    }

    private function makeLead(array $overrides = []): Lead
    {
        static $sequence = 0;
        $sequence++;

        return Lead::create(array_merge([
            'lead_code' => Lead::generateCode(),
            'name' => 'Proprietor ' . $sequence,
            'phone' => '90000000' . str_pad((string) $sequence, 2, '0', STR_PAD_LEFT),
            'source' => Lead::DEFAULT_SOURCE,
            'status' => 'New',
            'priority' => 'Medium',
        ], $overrides));
    }

    public function test_sidebar_offers_the_four_crm_entries_and_no_visits_item(): void
    {
        $sidebar = $this->sidebarFor('super-admin');

        foreach ([
            'Cold Calls / Direct Call',
            'Promotion Email / Call',
            'Existing Client Visit',
            'Client',
        ] as $label) {
            $this->assertStringContainsString($label, $sidebar);
        }

        foreach (['/cold-calls', '/promotion-emails', '/existing-client-visits'] as $href) {
            $this->assertStringContainsString($href, $sidebar);
        }

        // Visits and Clients are gone as menu entries.
        $this->assertStringNotContainsString('>Visits</span>', $sidebar);
        $this->assertStringNotContainsString('>Clients</span>', $sidebar);
    }

    public function test_a_field_marketer_sees_cold_calls_and_none_of_the_rest(): void
    {
        $sidebar = $this->sidebarFor('fieldmarketer');

        $this->assertStringContainsString('Cold Calls / Direct Call', $sidebar);

        foreach (['Promotion Email / Call', 'Existing Client Visit'] as $label) {
            $this->assertStringNotContainsString($label, $sidebar);
        }
        $this->assertStringNotContainsString('>Client</span>', $sidebar);
        $this->assertStringNotContainsString('/promotion-emails', $sidebar);
        $this->assertStringNotContainsString('/existing-client-visits', $sidebar);
    }

    public function test_cold_calls_carries_every_visit_and_the_other_two_are_subsets(): void
    {
        $this->makeLead(['name' => 'Alpha Prospect', 'source' => Lead::CHANNELS['cold_call']]);
        $this->makeLead(['name' => 'Bravo Prospect', 'source' => Lead::CHANNELS['promotion_email']]);
        $this->makeLead(['name' => 'Charlie Prospect', 'source' => Lead::CHANNELS['existing_client']]);

        $actor = $this->actingAs($this->userWithRole('super-admin'));

        // Cold Calls took over from Visits: every record, whatever its channel.
        $all = $actor->get('/cold-calls')->assertOk()->getContent();
        $this->assertStringContainsString('Cold Calls / Direct Call', $all);
        $this->assertStringContainsString('Alpha Prospect', $all);
        $this->assertStringContainsString('Bravo Prospect', $all);
        $this->assertStringContainsString('Charlie Prospect', $all);

        $mail = $actor->get('/promotion-emails')->assertOk()->getContent();
        $this->assertStringContainsString('Promotion Email / Call', $mail);
        $this->assertStringContainsString('Bravo Prospect', $mail);
        $this->assertStringNotContainsString('Alpha Prospect', $mail);
        $this->assertStringNotContainsString('Charlie Prospect', $mail);

        $existing = $actor->get('/existing-client-visits')->assertOk()->getContent();
        $this->assertStringContainsString('Existing Client Visit', $existing);
        $this->assertStringContainsString('Charlie Prospect', $existing);
        $this->assertStringNotContainsString('Alpha Prospect', $existing);
        $this->assertStringNotContainsString('Bravo Prospect', $existing);

        // /leads is the resource base behind Cold Calls — the same list.
        $this->assertStringContainsString(
            'Bravo Prospect',
            $actor->get('/leads')->assertOk()->getContent()
        );
    }

    public function test_visit_form_drops_the_source_dropdown_and_gains_the_two_new_fields(): void
    {
        $actor = $this->actingAs($this->userWithRole('super-admin'));

        $html = $actor->get('/leads/create')->assertOk()->getContent();

        $this->assertStringContainsString('name="purpose_of_visit"', $html);
        $this->assertStringContainsString('name="service_type"', $html);

        // No dropdown to pick a source from any more — only the hidden field
        // carrying the channel of the page this form was opened on.
        $this->assertStringNotContainsString('<select name="source"', $html);
        $this->assertStringContainsString('<input type="hidden" name="source"', $html);

        $mail = $actor->get('/leads/create?source=promotion_email')->assertOk()->getContent();
        $this->assertStringContainsString('name="source" value="Promotion Email"', $mail);
        $this->assertStringContainsString('Promotion Email / Call', $mail);

        $existing = $actor->get('/leads/create?source=existing_client')->assertOk()->getContent();
        $this->assertStringContainsString('name="source" value="Existing Client"', $existing);
        $this->assertStringContainsString('Existing Client Visit', $existing);

        // An unknown channel falls back to the Cold Call catch-all rather
        // than refusing to render.
        $fallback = $actor->get('/leads/create?source=nonsense')->assertOk()->getContent();
        $this->assertStringContainsString('name="source" value="Cold Call"', $fallback);
    }

    public function test_source_is_stamped_from_the_channel_the_form_was_opened_on(): void
    {
        $actor = $this->actingAs($this->userWithRole('super-admin'));

        $actor->post('/leads', [
            'name' => 'Filed Under Existing Client', 'phone' => '9000000101',
            'source' => Lead::CHANNELS['existing_client'],
            'status' => 'New', 'priority' => 'Medium',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('leads', [
            'name' => 'Filed Under Existing Client',
            'source' => 'Existing Client',
        ]);

        // Nothing specified → the Cold Call catch-all.
        $actor->post('/leads', [
            'name' => 'Filed Under Nothing', 'phone' => '9000000102',
            'status' => 'New', 'priority' => 'Medium',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('leads', [
            'name' => 'Filed Under Nothing',
            'source' => 'Cold Call',
        ]);
    }

    public function test_a_field_marketer_cannot_file_a_visit_under_an_admin_only_channel(): void
    {
        $sales = $this->userWithRole('fieldmarketer');

        $html = $this->actingAs($sales)->get('/leads/create?source=existing_client')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('name="source" value="Cold Call"', $html);
    }

    public function test_purpose_of_visit_and_service_type_round_trip(): void
    {
        $actor = $this->actingAs($this->userWithRole('super-admin'));

        $actor->post('/leads', [
            'name' => 'Classified Visit', 'phone' => '9000000103',
            'status' => 'New', 'priority' => 'Medium',
            'purpose_of_visit' => 'Quotation Submission',
            'service_type' => 'Single Project',
        ])->assertSessionHasNoErrors();

        $lead = Lead::where('name', 'Classified Visit')->firstOrFail();
        $this->assertSame('Quotation Submission', $lead->purpose_of_visit);
        $this->assertSame('Single Project', $lead->service_type);

        $actor->put('/leads/' . $lead->id, [
            'name' => 'Classified Visit', 'phone' => '9000000103',
            'status' => 'New', 'priority' => 'Medium',
            'purpose_of_visit' => 'Contract Renewal',
            'service_type' => 'AMC',
        ])->assertSessionHasNoErrors();

        $lead->refresh();
        $this->assertSame('Contract Renewal', $lead->purpose_of_visit);
        $this->assertSame('AMC', $lead->service_type);
        // Source is not editable, so it survives the edit untouched.
        $this->assertSame(Lead::DEFAULT_SOURCE, $lead->source);
    }

    public function test_service_type_decides_whether_conversion_makes_an_amc_or_a_project(): void
    {
        $actor = $this->actingAs($this->userWithRole('super-admin'));

        $amcLead = $this->makeLead(['name' => 'Hotel AMC Visit', 'service_type' => 'AMC', 'expected_value' => 150000]);
        $actor->post('/leads/' . $amcLead->id . '/convert')->assertSessionHasNoErrors();

        $amcLead->refresh();
        $this->assertSame('Converted', $amcLead->status);
        $this->assertNotNull($amcLead->converted_customer_id);

        $contract = MaintenanceContract::latest('id')->firstOrFail();
        $this->assertSame($amcLead->converted_customer_id, $contract->customer_id);
        $this->assertSame(150000.0, (float) $contract->contract_value);
        $this->assertSame('Active', $contract->status);
        $this->assertDatabaseCount('projects', 0);

        $projectLead = $this->makeLead(['name' => 'Villa Project Visit', 'service_type' => 'Single Project']);
        $actor->post('/leads/' . $projectLead->id . '/convert')->assertSessionHasNoErrors();

        $this->assertDatabaseCount('maintenance_contracts', 1);
        $this->assertDatabaseCount('projects', 1);
        $this->assertDatabaseHas('projects', ['status' => 'Planning']);
    }

    public function test_conversion_works_when_no_service_type_and_no_value_was_recorded(): void
    {
        $actor = $this->actingAs($this->userWithRole('super-admin'));

        // expected_value is NOT NULL with a default of 0, so "never filled in"
        // reaches the converter as a plain zero.
        $lead = $this->makeLead(['name' => 'Bare Visit', 'service_type' => null, 'expected_value' => 0]);

        $actor->post('/leads/' . $lead->id . '/convert', ['create_project' => 1])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('projects', 1);
        $this->assertSame(0.0, (float) Project::latest('id')->firstOrFail()->contract_value);
    }

    public function test_only_super_admin_may_convert_a_visit_into_a_client(): void
    {
        $lead = $this->makeLead(['name' => 'Locked Visit']);

        foreach (['admin', 'fieldmarketer'] as $roleSlug) {
            $this->actingAs($this->userWithRole($roleSlug))
                ->post('/leads/' . $lead->id . '/convert')
                ->assertForbidden();
        }

        $this->assertDatabaseHas('leads', ['id' => $lead->id, 'status' => 'New']);

        $this->actingAs($this->userWithRole('super-admin'))
            ->post('/leads/' . $lead->id . '/convert')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('leads', ['id' => $lead->id, 'status' => 'Converted']);
    }

    public function test_convert_is_withheld_from_the_matrix_of_any_role_but_super_admin(): void
    {
        $convertId = Permission::where('slug', 'leads.convert')->firstOrFail()->id;
        $superAdmin = $this->userWithRole('super-admin');

        foreach (['admin', 'fieldmarketer'] as $roleSlug) {
            $role = Role::where('slug', $roleSlug)->firstOrFail();

            $html = $this->actingAs($superAdmin)
                ->get('/roles/' . $role->id)
                ->assertOk()
                ->getContent();

            $this->assertStringNotContainsString('id="perm-' . $convertId . '"', $html);
        }

        $superAdminRole = Role::where('slug', 'super-admin')->firstOrFail();
        $html = $this->actingAs($superAdmin)
            ->get('/roles/' . $superAdminRole->id)
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('id="perm-' . $convertId . '"', $html);
    }

    public function test_a_role_that_may_not_convert_cannot_be_granted_it_through_the_matrix(): void
    {
        $convertId = Permission::where('slug', 'leads.convert')->firstOrFail()->id;
        $admin = Role::where('slug', 'admin')->firstOrFail();

        $existing = $admin->permissions()->get()->pluck('id')->all();

        $this->actingAs($this->userWithRole('super-admin'))
            ->put('/roles/' . $admin->id, [
                'permissions' => array_merge($existing, [$convertId]),
            ])
            ->assertSessionHas('success');

        $this->assertNotContains($convertId, $admin->permissions()->get()->pluck('id')->all());
    }
}
