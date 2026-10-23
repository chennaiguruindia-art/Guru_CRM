<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Role;
use App\Models\User;
use App\Services\LeadConversionService;
use App\Services\TodayService;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\HasBranchStaff;
use Tests\TestCase;

/**
 * A CRM record is read by the Super Admin, by the Admin of its own branch, and
 * by the salesperson who entered it — and by nobody else.
 *
 * So a colleague in the same branch sees nothing of theirs, and an Admin does
 * not read past their own branch. Visits and Clients obey the one rule,
 * because a converted client should not become more widely readable than the
 * visit it came from.
 */
class RecordVisibilityTest extends TestCase
{
    use RefreshDatabase, HasBranchStaff;

    private Branch $bangalore;
    private Branch $chennai;
    private User $super;

    // Two field marketers share Bangalore. That is the case the rule is for:
    // being in the same branch is not a reason to read each other's work.
    private User $salesA;
    private User $salesB;
    private User $salesC;

    private User $bangaloreAdmin;
    private User $chennaiAdmin;
    private User $adminWithoutBranch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);

        $this->bangalore = $this->branch('Bangalore');
        $this->chennai = $this->branch('Chennai');

        $this->super = $this->userWithRole('super-admin');

        $this->salesA = $this->person('fieldmarketer', $this->bangalore);
        $this->salesB = $this->person('fieldmarketer', $this->bangalore);
        $this->salesC = $this->person('fieldmarketer', $this->chennai);

        $this->bangaloreAdmin = $this->person('admin', $this->bangalore);
        $this->chennaiAdmin = $this->person('admin', $this->chennai);
        $this->adminWithoutBranch = $this->person('admin');
    }

    private function userWithRole(string $roleSlug): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->roles()->attach(Role::where('slug', $roleSlug)->firstOrFail());

        return $user;
    }

    /**
     * A login holding a role, posted to a branch. Without the branch the
     * login has no patch, and the scope fails closed for it.
     */
    private function person(string $roleSlug, ?Branch $branch = null): User
    {
        $user = $this->userWithRole($roleSlug);

        if ($branch !== null) {
            $this->putInBranch($user, $branch->id, $roleSlug === 'admin' ? 'Admin' : 'Field Marketer');
        }

        return $user;
    }

    /**
     * Stamped the way LeadController::store stamps it — the filer owns the
     * visit and it sits in the branch they work out of.
     */
    private function makeLead(User $filer, array $overrides = []): Lead
    {
        static $n = 0;
        $n++;

        return Lead::create(array_merge([
            'lead_code' => 'LED-T-' . $n,
            'name' => 'Visit ' . $n,
            'phone' => '98000000' . str_pad((string) $n, 2, '0'),
            'source' => 'Cold Call',
            'status' => 'New',
            'priority' => 'Medium',
            'created_by_id' => $filer->id,
            'assigned_to_id' => $filer->id,
            'branch_id' => $filer->branchId(),
        ], $overrides));
    }

    /**
     * Stamped the way CustomerController::store stamps it.
     */
    private function makeCustomer(User $filer, array $overrides = []): Customer
    {
        static $n = 0;
        $n++;

        return Customer::create(array_merge([
            'customer_code' => 'CUST-T-' . $n,
            'name' => 'Client ' . $n,
            'phone' => '97000000' . str_pad((string) $n, 2, '0'),
            'status' => 'active',
            'created_by_id' => $filer->id,
            'branch_id' => $filer->branchId(),
        ], $overrides));
    }

    private function namesVisibleTo(User $viewer, string $model): array
    {
        $this->actingAs($viewer);

        return $model::query()->orderBy('name')->pluck('name')->all();
    }

    public function test_the_super_admin_reads_every_branch(): void
    {
        $this->makeLead($this->salesA, ['name' => 'A Hotel']);
        $this->makeLead($this->salesB, ['name' => 'B Apartment']);
        $this->makeLead($this->salesC, ['name' => 'C Villa']);

        $this->assertSame(
            ['A Hotel', 'B Apartment', 'C Villa'],
            $this->namesVisibleTo($this->super, Lead::class)
        );
    }

    public function test_a_salesperson_reads_their_own_visits_only(): void
    {
        $this->makeLead($this->salesA, ['name' => 'A Hotel']);
        $this->makeLead($this->salesB, ['name' => 'B Apartment']);
        $this->makeLead($this->salesC, ['name' => 'C Villa']);

        // SalesB sits in the very same branch as SalesA — sharing a branch is
        // not a reason to read each other's work.
        $this->assertSame(['A Hotel'], $this->namesVisibleTo($this->salesA, Lead::class));
        $this->assertSame(['B Apartment'], $this->namesVisibleTo($this->salesB, Lead::class));
        $this->assertSame(['C Villa'], $this->namesVisibleTo($this->salesC, Lead::class));
    }

    public function test_an_admin_reads_their_own_whole_branch(): void
    {
        $this->makeLead($this->salesA, ['name' => 'A Hotel']);
        $this->makeLead($this->salesB, ['name' => 'B Apartment']);
        $this->makeLead($this->salesC, ['name' => 'C Villa']);

        // Both of Bangalore's field marketers, and nothing beyond it.
        $this->assertSame(
            ['A Hotel', 'B Apartment'],
            $this->namesVisibleTo($this->bangaloreAdmin, Lead::class)
        );

        $this->assertSame(['C Villa'], $this->namesVisibleTo($this->chennaiAdmin, Lead::class));
    }

    public function test_an_admin_with_no_branch_reads_nothing(): void
    {
        $this->makeLead($this->salesA, ['name' => 'A Hotel']);
        $this->makeLead($this->salesC, ['name' => 'C Villa']);

        // Fails closed rather than falling back to every branch — a login
        // nobody has posted to a patch has no patch to read.
        $this->assertSame([], $this->namesVisibleTo($this->adminWithoutBranch, Lead::class));
    }

    public function test_clients_obey_the_same_rule(): void
    {
        $this->makeCustomer($this->salesA, ['name' => 'A Client']);
        $this->makeCustomer($this->salesB, ['name' => 'B Client']);
        $this->makeCustomer($this->salesC, ['name' => 'C Client']);

        $this->assertSame(
            ['A Client', 'B Client', 'C Client'],
            $this->namesVisibleTo($this->super, Customer::class)
        );

        $this->assertSame(['A Client'], $this->namesVisibleTo($this->salesA, Customer::class));
        $this->assertSame(['B Client'], $this->namesVisibleTo($this->salesB, Customer::class));

        $this->assertSame(
            ['A Client', 'B Client'],
            $this->namesVisibleTo($this->bangaloreAdmin, Customer::class)
        );

        $this->assertSame(['C Client'], $this->namesVisibleTo($this->chennaiAdmin, Customer::class));
        $this->assertSame([], $this->namesVisibleTo($this->adminWithoutBranch, Customer::class));
    }

    /**
     * A converted client is the visit made permanent, so it must not become
     * more widely readable than the visit it came from.
     */
    public function test_converting_a_visit_hands_the_client_to_the_same_people(): void
    {
        $lead = $this->makeLead($this->salesA, ['name' => 'A Hotel']);

        $this->actingAs($this->super);
        (new LeadConversionService())->convert($lead);

        $visibleTo = fn (User $viewer) => $this->namesVisibleTo($viewer, Customer::class);

        $this->assertSame(['A Hotel'], $visibleTo($this->salesA));
        $this->assertSame(['A Hotel'], $visibleTo($this->bangaloreAdmin));
        $this->assertSame(['A Hotel'], $visibleTo($this->super));

        $this->assertSame([], $visibleTo($this->salesB));   // same branch, different person
        $this->assertSame([], $visibleTo($this->chennaiAdmin));
        $this->assertSame([], $visibleTo($this->adminWithoutBranch));
    }

    /**
     * Narrowing by branch is now the Super Admin's alone — an Admin cannot ask
     * for another branch, and a salesperson for anything but their own patch.
     */
    public function test_the_branch_filter_is_the_super_admins_alone(): void
    {
        $this->actingAs($this->super)->get('/leads')
            ->assertOk()
            ->assertSee('All branches')
            ->assertSee('Chennai')
            ->assertSee('Bangalore');

        foreach ([$this->bangaloreAdmin, $this->chennaiAdmin, $this->salesA] as $viewer) {
            $this->actingAs($viewer)->get('/leads')
                ->assertOk()
                ->assertDontSee('All branches');
        }
    }

    /**
     * The filter is picked by location, because both live branches are named
     * "Guru Integrated" and a filter nobody can tell apart is no filter at all.
     */
    public function test_the_branch_filter_shows_location_not_the_name(): void
    {
        $this->actingAs($this->super)->get('/leads')
            ->assertOk()
            ->assertSee('Chennai')
            ->assertSee('Bangalore')
            ->assertDontSee('Guru Integrated');
    }

    /**
     * The scope sits on the model, so the dashboard's attention strip and the
     * navbar bell are held to it too — not just the visit list.
     */
    public function test_the_rule_reaches_the_dashboard_attention_strip(): void
    {
        $today = now()->toDateString();

        $this->makeLead($this->salesA, ['name' => 'A Hotel', 'follow_up_date' => $today]);
        $this->makeLead($this->salesB, ['name' => 'B Apartment', 'follow_up_date' => $today]);

        $titlesFor = function (User $viewer) use ($today) {
            $this->actingAs($viewer);

            return collect((new TodayService())->items(100))
                ->where('type', 'lead')
                ->pluck('title')
                ->all();
        };

        $this->assertSame(['A Hotel'], $titlesFor($this->salesA));
        $this->assertSame(['B Apartment'], $titlesFor($this->salesB));
        $this->assertSame(['A Hotel', 'B Apartment'], $titlesFor($this->bangaloreAdmin));
        $this->assertSame([], $titlesFor($this->adminWithoutBranch));
    }

    /**
     * Codes are counted company-wide, not from the filer's side of the scope.
     * Otherwise the very first visit a salesperson files collides with
     * somebody else's LEAD-0001 and the insert is refused outright — which is
     * what every salesperson in every branch would hit.
     */
    public function test_a_salesperson_who_has_filed_nothing_still_gets_a_fresh_code(): void
    {
        $this->makeLead($this->salesA, ['name' => 'A Hotel']);

        // A newcomer in the other branch, with no visits to their name — from
        // where they stand, the count of existing visits comes back empty.
        $newcomer = $this->person('fieldmarketer', $this->chennai);

        $this->actingAs($newcomer);

        $this->assertSame('LEAD-0002', Lead::generateCode());

        $this->post('/leads', [
            'name' => 'Newcomer Visit',
            'phone' => '9500000000',
            'status' => 'New',
            'priority' => 'Medium',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('leads', [
            'name' => 'Newcomer Visit',
            'lead_code' => 'LEAD-0002',
        ]);
    }

    /**
     * The same defect on the Clients side: an Admin creating one would be
     * handed a code from an empty count too.
     */
    public function test_a_viewer_who_can_see_no_client_still_gets_a_fresh_code(): void
    {
        $this->makeCustomer($this->salesA, ['name' => 'A Client']);

        $this->actingAs($this->adminWithoutBranch);

        $this->assertSame('CUST-0002', Customer::generateCode());
    }
}
