<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin Backoffice seeders
        $this->call([
            AdminUserSeeder::class,
            PlanSeeder::class,
        ]);

        // Host-specific seeders (only run if hosts exist)
        if (\App\Models\Host::exists()) {
            $this->call([
                ClassPlanSeeder::class,
                ServicePlanSeeder::class,
                ClassSessionSeeder::class,
            ]);
        }
    }
}
