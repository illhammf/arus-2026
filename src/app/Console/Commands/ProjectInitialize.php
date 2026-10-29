<?php

namespace App\Console\Commands;

use Database\Seeders\RoleSeeder;
use Database\Seeders\SystemCategorySeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Console\Command;

class ProjectInitialize extends Command
{
    protected $signature = 'project:install
        {--shield : Generate Filament Shield permissions and policies}
        {--demo : Also seed the default super admin account}';

    protected $description = 'Prepare a safe, non-destructive Arus installation.';

    public function handle(): int
    {
        $this->components->info('Running pending migrations...');
        $this->call('migrate', ['--force' => true]);

        if ($this->option('shield')) {
            $this->components->info('Generating Filament Shield permissions...');
            $this->call('shield:generate', [
                '--all' => true,
                '--panel' => 'admin',
            ]);
        }

        $this->components->info('Seeding roles and system categories...');
        $this->call('db:seed', [
            '--class' => RoleSeeder::class,
            '--force' => true,
        ]);
        $this->call('db:seed', [
            '--class' => SystemCategorySeeder::class,
            '--force' => true,
        ]);

        if ($this->option('demo')) {
            $this->components->info('Seeding the default super admin account...');
            $this->call('db:seed', [
                '--class' => UserSeeder::class,
                '--force' => true,
            ]);
        }

        $this->call('filament:optimize-clear');
        $this->call('optimize:clear');

        $this->components->success('Arus is ready. No data was deleted.');

        return self::SUCCESS;
    }
}
