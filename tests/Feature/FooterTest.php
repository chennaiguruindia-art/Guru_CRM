<?php

namespace Tests\Feature;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FooterTest extends TestCase
{
    use RefreshDatabase;

    private function footer(): string
    {
        return view('components.footer')->render();
    }

    public function test_the_footer_reads_name_tagline_and_version_from_config(): void
    {
        $html = $this->footer();

        // e() is what Blade's {{ }} does, so this asserts the configured
        // wording really is what lands in the page rather than a copy of it.
        $this->assertStringContainsString(e(config('app.name')), $html);
        $this->assertStringContainsString(e(config('app.tagline')), $html);
        $this->assertStringContainsString('System Version ' . e(config('app.version')), $html);

        // The year follows the configured timezone rather than a literal.
        $this->assertStringContainsString(now()->format('Y'), $html);
    }

    public function test_the_stack_line_reports_the_framework_and_database_in_use(): void
    {
        $html = $this->footer();

        // The installed version, not a string typed into the template — so a
        // framework upgrade changes the footer without anyone remembering to.
        $this->assertStringContainsString('Laravel ' . Application::VERSION, $html);

        // …and the database it is talking to, by the name a person would use.
        $driver = (string) config('database.connections.' . config('database.default') . '.driver');
        $label = [
            'mysql' => 'MySQL',
            'mariadb' => 'MariaDB',
            'pgsql' => 'PostgreSQL',
            'sqlite' => 'SQLite',
            'sqlsrv' => 'SQL Server',
        ][$driver] ?? $driver;

        $this->assertStringContainsString($label, $html);
    }

    public function test_the_status_badge_is_a_real_check_and_the_environment_chip_is_dev_only(): void
    {
        // RefreshDatabase has a database behind it, so it reports Online.
        $this->assertStringContainsString('System Online', $this->footer());

        // Production renders no environment chip at all.
        config(['app.env' => 'production']);
        $this->assertStringNotContainsString('>LOCAL<', $this->footer());

        // Any other environment is labelled, so a build left on a live box
        // cannot quietly masquerade as the real thing.
        config(['app.env' => 'local']);
        $this->assertStringContainsString('>LOCAL<', $this->footer());
    }

    public function test_a_database_that_cannot_be_reached_is_reported_not_hidden(): void
    {
        $original = (string) config('database.default');

        try {
            config([
                'database.default' => 'unreachable',
                'database.connections.unreachable' => ['driver' => 'not-a-real-driver'],
            ]);

            $this->assertStringContainsString('Database Offline', $this->footer());
        } finally {
            // RefreshDatabase still has to tear down against the real one.
            config(['database.default' => $original]);
        }
    }
}
