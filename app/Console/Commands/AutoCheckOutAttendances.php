<?php

namespace App\Console\Commands;

use App\Services\AttendanceRuleService;
use Illuminate\Console\Command;

class AutoCheckOutAttendances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:auto-checkout {--employee= : Optional employee ID} {--force : Force auto checkout even before 19:00}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically punch-out/logout employees who did not logout before 7:00 PM';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $employeeId = $this->option('employee') ? (int) $this->option('employee') : null;
        $force = (bool) $this->option('force');

        $this->info("Running attendance auto-checkout check (cutoff: 07:00 PM)...");

        $processed = AttendanceRuleService::autoCheckOutPendingAttendances($employeeId, null, $force);

        $count = count($processed);
        if ($count > 0) {
            $this->info("Successfully auto-checked out {$count} pending attendance record(s).");
            foreach ($processed as $p) {
                $this->line(" - Attendance #{$p['attendance_id']} (Emp: {$p['employee_id']}, Date: {$p['date']}) -> Status: {$p['status']}, Worked: {$p['worked_hours']} hrs");
            }
        } else {
            $this->line("No pending attendances met the auto-checkout condition (either already checked out or before 7:00 PM).");
        }

        return Command::SUCCESS;
    }
}
