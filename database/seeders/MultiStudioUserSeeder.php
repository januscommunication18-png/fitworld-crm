<?php

namespace Database\Seeders;

use App\Models\Host;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MultiStudioUserSeeder extends Seeder
{
    /**
     * Create 10 users for each host with 6 users (2 admin, 2 staff, 2 instructor) belonging to both.
     */
    public function run(): void
    {
        $host1 = Host::find(1);
        $host2 = Host::find(2);

        if (!$host1 || !$host2) {
            $this->command->error('Hosts with ID 1 and 2 are required.');
            return;
        }

        $password = Hash::make('password');
        $now = now();

        // 6 Users that belong to BOTH hosts (2 admin, 2 staff, 2 instructor)
        $sharedUsers = [
            // 2 Admins for both hosts
            [
                'first_name' => 'Alex',
                'last_name' => 'Morgan',
                'email' => 'alex.morgan@example.com',
                'role' => User::ROLE_ADMIN,
            ],
            [
                'first_name' => 'Jordan',
                'last_name' => 'Rivera',
                'email' => 'jordan.rivera@example.com',
                'role' => User::ROLE_ADMIN,
            ],
            // 2 Staff for both hosts
            [
                'first_name' => 'Casey',
                'last_name' => 'Williams',
                'email' => 'casey.williams@example.com',
                'role' => User::ROLE_STAFF,
            ],
            [
                'first_name' => 'Taylor',
                'last_name' => 'Brown',
                'email' => 'taylor.brown@example.com',
                'role' => User::ROLE_STAFF,
            ],
            // 2 Instructors for both hosts
            [
                'first_name' => 'Morgan',
                'last_name' => 'Lee',
                'email' => 'morgan.lee@example.com',
                'role' => User::ROLE_INSTRUCTOR,
            ],
            [
                'first_name' => 'Riley',
                'last_name' => 'Garcia',
                'email' => 'riley.garcia@example.com',
                'role' => User::ROLE_INSTRUCTOR,
            ],
        ];

        // 4 Users for Host 1 only (CrossFit)
        $host1OnlyUsers = [
            [
                'first_name' => 'Mike',
                'last_name' => 'Thompson',
                'email' => 'mike.thompson@crossfit.example.com',
                'role' => User::ROLE_ADMIN,
            ],
            [
                'first_name' => 'Sarah',
                'last_name' => 'Johnson',
                'email' => 'sarah.johnson@crossfit.example.com',
                'role' => User::ROLE_STAFF,
            ],
            [
                'first_name' => 'Chris',
                'last_name' => 'Davis',
                'email' => 'chris.davis@crossfit.example.com',
                'role' => User::ROLE_INSTRUCTOR,
            ],
            [
                'first_name' => 'Emily',
                'last_name' => 'Wilson',
                'email' => 'emily.wilson@crossfit.example.com',
                'role' => User::ROLE_STAFF,
            ],
        ];

        // 4 Users for Host 2 only (Zeeshan's Studio)
        $host2OnlyUsers = [
            [
                'first_name' => 'Priya',
                'last_name' => 'Patel',
                'email' => 'priya.patel@zenyoga.example.com',
                'role' => User::ROLE_ADMIN,
            ],
            [
                'first_name' => 'Raj',
                'last_name' => 'Kumar',
                'email' => 'raj.kumar@zenyoga.example.com',
                'role' => User::ROLE_STAFF,
            ],
            [
                'first_name' => 'Aisha',
                'last_name' => 'Khan',
                'email' => 'aisha.khan@zenyoga.example.com',
                'role' => User::ROLE_INSTRUCTOR,
            ],
            [
                'first_name' => 'Omar',
                'last_name' => 'Hassan',
                'email' => 'omar.hassan@zenyoga.example.com',
                'role' => User::ROLE_STAFF,
            ],
        ];

        // Create shared users (belong to both hosts)
        foreach ($sharedUsers as $index => $userData) {
            $user = User::create([
                'host_id' => $host1->id, // Primary host
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
                'email' => $userData['email'],
                'password' => $password,
                'role' => $userData['role'],
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => $now,
            ]);

            // Attach to Host 1 (primary)
            DB::table('host_user')->insert([
                'user_id' => $user->id,
                'host_id' => $host1->id,
                'role' => $userData['role'],
                'is_primary' => true,
                'joined_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Attach to Host 2 (secondary)
            DB::table('host_user')->insert([
                'user_id' => $user->id,
                'host_id' => $host2->id,
                'role' => $userData['role'],
                'is_primary' => false,
                'joined_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $this->command->info("Created shared user: {$user->full_name} ({$userData['role']}) - belongs to both studios");
        }

        // Create Host 1 only users
        foreach ($host1OnlyUsers as $userData) {
            $user = User::create([
                'host_id' => $host1->id,
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
                'email' => $userData['email'],
                'password' => $password,
                'role' => $userData['role'],
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => $now,
            ]);

            DB::table('host_user')->insert([
                'user_id' => $user->id,
                'host_id' => $host1->id,
                'role' => $userData['role'],
                'is_primary' => true,
                'joined_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $this->command->info("Created CrossFit user: {$user->full_name} ({$userData['role']})");
        }

        // Create Host 2 only users
        foreach ($host2OnlyUsers as $userData) {
            $user = User::create([
                'host_id' => $host2->id,
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
                'email' => $userData['email'],
                'password' => $password,
                'role' => $userData['role'],
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => $now,
            ]);

            DB::table('host_user')->insert([
                'user_id' => $user->id,
                'host_id' => $host2->id,
                'role' => $userData['role'],
                'is_primary' => true,
                'joined_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $this->command->info("Created Zeeshan's Studio user: {$user->full_name} ({$userData['role']})");
        }

        $this->command->newLine();
        $this->command->info('Summary:');
        $this->command->info('- 6 users belong to BOTH studios (2 admin, 2 staff, 2 instructor)');
        $this->command->info('- 4 users belong to CrossFit only');
        $this->command->info('- 4 users belong to Zeeshan\'s Studio only');
        $this->command->info('- Total: 14 users created');
        $this->command->info('- Each studio has 10 team members');
        $this->command->newLine();
        $this->command->info('All passwords are: password');
    }
}
