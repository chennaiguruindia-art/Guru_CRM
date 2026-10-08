<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseOneAuthAndDashboardTest extends TestCase
{
    use RefreshDatabase;
    public function test_guest_is_redirected_to_login_from_root(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_login_screen_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Horticulture CRM');
        $response->assertSee('Sign In to CRM');
    }

    public function test_admin_can_authenticate_and_access_dashboard(): void
    {
        $user = User::where('email', 'admin@horticulturecrm.com')->first();
        if (!$user) {
            $user = User::factory()->create([
                'email' => 'admin@horticulturecrm.com',
                'password' => bcrypt('admin123'),
            ]);
        }

        $response = $this->post('/login', [
            'email' => 'admin@horticulturecrm.com',
            'password' => 'admin123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');

        // Check dashboard page rendering
        $dashboardResponse = $this->actingAs($user)->get('/dashboard');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Horticulture Operations Dashboard');
        $dashboardResponse->assertSee('Total Visits');
        $dashboardResponse->assertSee('Active Projects');
        $dashboardResponse->assertSee('Pending Quotations');
        $dashboardResponse->assertSee('Collections');
    }

    public function test_dashboard_stats_endpoint_returns_json_with_all_kpis_and_charts(): void
    {
        $user = User::where('email', 'admin@horticulturecrm.com')->first();
        if (!$user) {
            $user = User::factory()->create();
        }

        $response = $this->actingAs($user)->getJson('/dashboard/stats');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'metrics' => [
                    'total_leads',
                    'new_leads',
                    'converted_leads',
                    'total_customers',
                    'active_projects',
                    'completed_projects',
                    'pending_quotations',
                    'approved_quotations',
                    'active_amc',
                    'pending_invoices',
                    'paid_amount',
                    'pending_amount',
                    'overdue_amount',
                ],
                'charts' => [
                    'leads_by_source',
                    'leads_by_status',
                    'sales_pipeline',
                    'monthly_quotations',
                    'monthly_revenue',
                    'project_status',
                    'amc_status',
                    'pending_payments',
                ],
            ],
        ]);
    }
}
