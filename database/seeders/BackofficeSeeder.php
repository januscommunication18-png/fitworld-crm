<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BackofficeSeeder extends Seeder
{
    /**
     * Run the database seeds for backoffice.
     */
    public function run(): void
    {
        // Create super admin
        AdminUser::firstOrCreate(
            ['email' => 'admin@fitcrm.com'],
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'status' => 'active',
                'must_change_password' => false,
                'email_verified_at' => now(),
            ]
        );

        // Create regular admin
        AdminUser::firstOrCreate(
            ['email' => 'backoffice@fitcrm.com'],
            [
                'first_name' => 'Backoffice',
                'last_name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'administrator',
                'status' => 'active',
                'must_change_password' => false,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════════════╗');
        $this->command->info('║         Backoffice Admin Users Created           ║');
        $this->command->info('╠══════════════════════════════════════════════════╣');
        $this->command->info('║  Super Admin:                                    ║');
        $this->command->info('║    Email:    admin@fitcrm.com                    ║');
        $this->command->info('║    Password: password                            ║');
        $this->command->info('║                                                  ║');
        $this->command->info('║  Backoffice Admin:                               ║');
        $this->command->info('║    Email:    backoffice@fitcrm.com               ║');
        $this->command->info('║    Password: password                            ║');
        $this->command->info('╚══════════════════════════════════════════════════╝');
        $this->command->info('');
    }
}
