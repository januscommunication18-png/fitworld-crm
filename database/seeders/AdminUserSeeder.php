<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin user
        AdminUser::firstOrCreate(
            ['email' => 'admin@fitcrm.com'],
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'password' => Hash::make('password'),
                'role' => 'administrator',
                'status' => 'active',
                'must_change_password' => true,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Default admin user created:');
        $this->command->info('Email: admin@fitcrm.com');
        $this->command->info('Password: password');
        $this->command->warn('Please change the password after first login!');
    }
}
