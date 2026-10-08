<?php

namespace App\Console\Commands;

use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Console\Command;

class InitCrmCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crm:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize Horticulture CRM roles, permissions, admin user and settings';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Initializing Horticulture CRM Roles, Permissions, and Admin...');
        
        $seeder = new RoleAndPermissionSeeder();
        $seeder->run();

        $this->info('✓ CRM Initialization completed successfully!');
        $this->info('Default Super Admin credentials:');
        $this->line('Email:    admin@horticulturecrm.com');
        $this->line('Password: admin123');

        return Command::SUCCESS;
    }
}
