<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Lead;
use App\Models\Role;
use App\Models\User;
use App\Services\TodayService;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\HasBranchStaff;
use Tests\TestCase;

/**
 * A branch sees its own visits and nobody else's; Administration sees all.
 */
class BranchVisibilityTest extends TestCase
{
    use RefreshDatabase, HasBranchStaff;

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

    private function makeLead(array $overrides = []): Lead
    {
        static $n = 0;
        $n++;

        return Lead::create(array_merge([
            'lead_code' => Lead::generateCode(),
            'name' => 'Proprietor ' . $n,
            'phone' => '90000000' . str_pad((string) $n, 2, '0', STR_PAD_LEFT),
            'source' => Lead::DEFAULT_SOURCE,
            'status' => 'New',
            'priority' => 'Medium',
        ], $overrides));
    }

    public function test_a_field_marketer_sees_only_their_own_branch_on_the_list(): void
    {
        $bangalore = $this->branch('Bangalore');
        $chennai = $this->branch('Chennai');

        $sales = $this->userWithRole('fieldmarketer');
        $this->putInBranch($sales, $bangalore->id);

        $this->makeLead(['name' => 'Bangalore Hotel', 'branch_id' => $bangalore->id]);
        $this->makeLead(['name' => 'Chennai Apartment', 'branch_id' => $chennai->id]);

        $html = $this->actingAs($sales)->get('/cold-calls')->assertOk()->getContent();

        $this->assertStringContainsString('Bangalore Hotel', $html);
        $this->assertStringNotContainsString('Chennai Apartment', $html);

        // …and no branch picker either: there is nothing there to choose.
        $this->assertStringNotContainsString('All Branches', $html);
    }

    public function test_a_visit_is_stamped_with_the_branch_that_filed_it(): void
    {
        $bangalore = $this->branch('Bangalore');
        $sales = $this->userWithRole('fieldmarketer');
        $this->putInBranch($sales, $bangalore->id);

        $this->actingAs($sales)->post('/leads', [
            'name' => 'Stamped Visit',
            'phone' => '9000000201',
            'status' => 'New',
            'priority' => 'Medium',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('leads', [
            'name' => 'Stamped Visit',
            'branch_id' => $bangalore->id,
        ]);
    }

    public function test_administration_sees_every_branch_and_can_narrow_to_one(): void
    {
        $bangalore = $this->branch('Bangalore');
        $chennai = $this->branch('Chennai');

        $this->makeLead(['name' => 'Bangalore Hotel', 'branch_id' => $bangalore->id]);
        $this->makeLead(['name' => 'Chennai Apartment', 'branch_id' => $chennai->id]);

        $admin = $this->userWithRole('admin');

        $all = $this->actingAs($admin)->get('/cold-calls')->assertOk()->getContent();
        $this->assertStringContainsString('Bangalore Hotel', $all);
        $this->assertStringContainsString('Chennai Apartment', $all);
        $this->assertStringContainsString('All Branches', $all);

        $one = $this->actingAs($admin)->get('/cold-calls?branch_id=' . $chennai->id)
            ->assertOk()->getContent();
        $this->assertStringContainsString('Chennai Apartment', $one);
        $this->assertStringNotContainsString('Bangalore Hotel', $one);
    }

    public function test_a_login_without_a_branch_sees_nothing_and_is_told_why(): void
    {
        $bangalore = $this->branch('Bangalore');
        $this->makeLead(['name' => 'Unreachable Visit', 'branch_id' => $bangalore->id]);

        $sales = $this->userWithRole('fieldmarketer');

        $html = $this->actingAs($sales)->get('/cold-calls')->assertOk()->getContent();

        $this->assertStringNotContainsString('Unreachable Visit', $html);
        $this->assertStringContainsString('Your account has no branch.', $html);
        $this->assertStringNotContainsString('All Branches', $html);
    }

    public function test_a_field_marketer_cannot_reach_another_branch_by_query_string(): void
    {
        $bangalore = $this->branch('Bangalore');
        $chennai = $this->branch('Chennai');

        $sales = $this->userWithRole('fieldmarketer');
        $this->putInBranch($sales, $bangalore->id);

        $this->makeLead(['name' => 'Chennai Apartment', 'branch_id' => $chennai->id]);
        $this->makeLead(['name' => 'Bangalore Hotel', 'branch_id' => $bangalore->id]);

        // Guessing another branch's id is ignored for anybody but
        // Administration, so it cannot be used to step outside their own.
        $html = $this->actingAs($sales)
            ->get('/cold-calls?branch_id=' . $chennai->id)
            ->assertOk()->getContent();

        $this->assertStringNotContainsString('Chennai Apartment', $html);
        $this->assertStringContainsString('Bangalore Hotel', $html);
    }

    public function test_the_reports_page_offers_the_branch_filter_to_administration(): void
    {
        $bangalore = $this->branch('Bangalore');
        $chennai = $this->branch('Chennai');

        $this->makeLead(['name' => 'Bangalore Hotel', 'branch_id' => $bangalore->id]);
        $this->makeLead(['name' => 'Chennai Apartment', 'branch_id' => $chennai->id]);

        $admin = $this->userWithRole('admin');

        $all = $this->actingAs($admin)->get('/reports')->assertOk()->getContent();

        $this->assertStringContainsString('All Branches', $all);
        $this->assertStringContainsString($bangalore->name, $all);
        $this->assertStringContainsString($chennai->name, $all);

        // …and narrowing the visit figures to one branch leaves the rest of
        // the report standing.
        $this->actingAs($admin)->get('/reports?branch_id=' . $bangalore->id)->assertOk();
    }

    public function test_the_branch_filter_is_picked_by_location_not_by_name(): void
    {
        // Branches are named after the company, so two of them share a name —
        // the city is the only thing that tells them apart.
        $chennai = Branch::create(['code' => 'BR-LOC-1', 'name' => 'Guru Integrated', 'location' => 'Chennai']);
        $bangalore = Branch::create(['code' => 'BR-LOC-2', 'name' => 'Guru Integrated', 'location' => 'Bangalore']);

        $this->makeLead(['branch_id' => $chennai->id]);
        $this->makeLead(['branch_id' => $bangalore->id]);

        $html = $this->actingAs($this->userWithRole('admin'))
            ->get('/cold-calls')->assertOk()->getContent();

        $this->assertStringContainsString('Chennai', $html);
        $this->assertStringContainsString('Bangalore', $html);
        $this->assertStringNotContainsString('Guru Integrated', $html);

        // …and the list swaps itself in place when one is picked, so there is
        // no Filter button to remember.
        $this->assertStringContainsString('id="visit-list-region"', $html);
        $this->assertStringContainsString('visitForm.querySelectorAll', $html);
    }

    public function test_the_branch_holds_everywhere_not_just_on_the_visit_list(): void
    {
        $bangalore = $this->branch('Bangalore');
        $chennai = $this->branch('Chennai');

        $sales = $this->userWithRole('fieldmarketer');
        $this->putInBranch($sales, $bangalore->id);

        $today = now()->toDateString();

        $this->makeLead(['name' => 'Bangalore Hotel', 'branch_id' => $bangalore->id, 'follow_up_date' => $today]);
        $this->makeLead(['name' => 'Chennai Apartment', 'branch_id' => $chennai->id, 'follow_up_date' => $today]);

        // The scope sits on the model, so the dashboard's attention strip and
        // the navbar bell are held to it too — not just this one list.
        $this->actingAs($sales);

        $titles = collect((new TodayService())->items(100))
            ->where('type', 'lead')
            ->pluck('title');

        $this->assertSame(['Bangalore Hotel'], $titles->all());
    }
}
