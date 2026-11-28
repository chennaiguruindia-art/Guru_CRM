<?php

namespace App\Console\Commands;

use App\Services\AttendanceRuleService;
use Illuminate\Console\Command;

class CleanupExpiredAttendanceSelfies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:cleanup-selfies {--days=45 : Number of days to retain attendance selfie images}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete attendance punch-in and punch-out selfie images older than 45 days from storage and database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $result = AttendanceRuleService::cleanupExpiredSelfies($days);

        $this->info("Selfie cleanup complete (cutoff < {$result['cutoff_date']}, retention: {$days} days).");
        $this->line(" - Records cleared in DB: {$result['records_cleared']}");
        $this->line(" - Image files deleted: {$result['files_deleted']}");

        return Command::SUCCESS;
    }
}
