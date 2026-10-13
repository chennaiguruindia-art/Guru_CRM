<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Services\TodayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A visit follow-up is a reminder, not a permanent red mark: it nags on the
 * day it is due and then takes itself off the dashboard strip and the bell.
 */
class TodayServiceTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 0;

    private function makeLead(array $overrides = []): Lead
    {
        $this->sequence++;

        return Lead::create(array_merge([
            'lead_code' => Lead::generateCode(),
            'name' => 'Proprietor ' . $this->sequence,
            'phone' => '9000000000' . str_pad((string) $this->sequence, 2, '0', STR_PAD_LEFT),
            'source' => Lead::DEFAULT_SOURCE,
            'status' => 'New',
            'priority' => 'Medium',
        ], $overrides));
    }

    /** @return array<int, array> */
    private function alertsOf(string $type): array
    {
        return array_values(array_filter(
            (new TodayService())->items(100),
            fn (array $item) => $item['type'] === $type
        ));
    }

    public function test_a_visit_follow_up_nags_on_the_day_it_is_due(): void
    {
        $this->makeLead(['follow_up_date' => now()->toDateString()]);

        $alerts = $this->alertsOf('lead');

        $this->assertCount(1, $alerts);
        $this->assertSame('Visit follow-up today', $alerts[0]['label']);
        $this->assertSame('warning', $alerts[0]['severity']);
    }

    public function test_it_removes_itself_the_day_after_the_follow_up_date(): void
    {
        $this->makeLead(['follow_up_date' => now()->subDay()->toDateString()]);
        $this->makeLead(['follow_up_date' => now()->subDays(30)->toDateString()]);

        $this->assertCount(0, $this->alertsOf('lead'));
    }

    public function test_a_follow_up_still_in_the_future_is_held_back_until_its_day(): void
    {
        $this->makeLead(['follow_up_date' => now()->addDays(3)->toDateString()]);

        $this->assertCount(0, $this->alertsOf('lead'));
    }

    public function test_a_converted_or_lost_visit_has_nothing_left_to_chase(): void
    {
        $this->makeLead(['status' => 'Converted', 'follow_up_date' => now()->toDateString()]);
        $this->makeLead(['status' => 'Lost', 'follow_up_date' => now()->toDateString()]);

        $this->assertCount(0, $this->alertsOf('lead'));
    }

    public function test_a_visit_with_no_follow_up_date_is_never_listed(): void
    {
        $this->makeLead();

        $this->assertCount(0, $this->alertsOf('lead'));
    }

    public function test_the_rule_is_scoped_to_visits_and_overdue_tasks_still_nag(): void
    {
        $this->sequence++;
        $n = $this->sequence;

        $customer = Customer::create([
            'customer_code' => 'CUST-TODAY-' . $n,
            'name' => 'Customer ' . $n,
            'phone' => '987650000' . str_pad((string) $n, 2, '0', STR_PAD_LEFT),
        ]);

        $project = Project::create([
            'project_code' => 'PRJ-TODAY-' . $n,
            'name' => 'Project ' . $n,
            'customer_id' => $customer->id,
        ]);

        ProjectTask::create([
            'project_id' => $project->id,
            'title' => 'Task that is now overdue',
            'due_date' => now()->subDay()->toDateString(),
            'status' => 'Pending',
        ]);

        // The visit dropped off the moment its date passed …
        $this->makeLead(['follow_up_date' => now()->subDay()->toDateString()]);

        $this->assertCount(0, $this->alertsOf('lead'));

        // … while the overdue task is still there, because invoices, tasks,
        // AMC and stock are not something you can afford to lose silently.
        $tasks = $this->alertsOf('task');
        $this->assertCount(1, $tasks);
        $this->assertSame('Task overdue', $tasks[0]['label']);
    }
}
