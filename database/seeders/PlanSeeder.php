<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Free/Trial Plan
        Plan::firstOrCreate(
            ['slug' => 'free'],
            [
                'name' => 'Free',
                'description' => 'Perfect for getting started with basic features.',
                'price_monthly' => 0,
                'price_yearly' => 0,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 1,
                'features' => [
                    'locations' => 1,
                    'rooms' => 2,
                    'classes' => 5,
                    'students' => 50,
                    'crm' => false,
                    'stripe_payments' => false,
                    'memberships' => false,
                    'intro_offers' => false,
                    'automated_emails' => false,
                    'attendance_insights' => true,
                    'revenue_insights' => false,
                    'manual_payments' => true,
                    'online_payments' => false,
                    'ics_sync' => false,
                    'fitnearyou_attribution' => true,
                    'priority_support' => false,
                ],
            ]
        );

        // Starter Plan
        Plan::firstOrCreate(
            ['slug' => 'starter'],
            [
                'name' => 'Starter',
                'description' => 'Essential features for growing studios.',
                'price_monthly' => 29.00,
                'price_yearly' => 290.00,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 2,
                'features' => [
                    'locations' => 1,
                    'rooms' => 5,
                    'classes' => 20,
                    'students' => 200,
                    'crm' => true,
                    'stripe_payments' => false,
                    'memberships' => false,
                    'intro_offers' => false,
                    'automated_emails' => true,
                    'attendance_insights' => true,
                    'revenue_insights' => false,
                    'manual_payments' => true,
                    'online_payments' => false,
                    'ics_sync' => true,
                    'fitnearyou_attribution' => true,
                    'priority_support' => false,
                ],
            ]
        );

        // Premium Plan
        Plan::firstOrCreate(
            ['slug' => 'premium'],
            [
                'name' => 'Premium',
                'description' => 'Full-featured plan for established studios.',
                'price_monthly' => 79.00,
                'price_yearly' => 790.00,
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 3,
                'features' => [
                    'locations' => 3,
                    'rooms' => 0, // unlimited
                    'classes' => 0, // unlimited
                    'students' => 0, // unlimited
                    'crm' => true,
                    'stripe_payments' => true,
                    'memberships' => true,
                    'intro_offers' => true,
                    'automated_emails' => true,
                    'attendance_insights' => true,
                    'revenue_insights' => true,
                    'manual_payments' => true,
                    'online_payments' => true,
                    'ics_sync' => true,
                    'fitnearyou_attribution' => false, // no branding
                    'priority_support' => true,
                ],
            ]
        );

        // Enterprise Plan
        Plan::firstOrCreate(
            ['slug' => 'enterprise'],
            [
                'name' => 'Enterprise',
                'description' => 'Unlimited everything for large organizations.',
                'price_monthly' => 199.00,
                'price_yearly' => 1990.00,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 4,
                'features' => [
                    'locations' => 0, // unlimited
                    'rooms' => 0,
                    'classes' => 0,
                    'students' => 0,
                    'crm' => true,
                    'stripe_payments' => true,
                    'memberships' => true,
                    'intro_offers' => true,
                    'automated_emails' => true,
                    'attendance_insights' => true,
                    'revenue_insights' => true,
                    'manual_payments' => true,
                    'online_payments' => true,
                    'ics_sync' => true,
                    'fitnearyou_attribution' => false,
                    'priority_support' => true,
                ],
            ]
        );

        $this->command->info('Default plans created: Free, Starter, Premium, Enterprise');
    }
}
