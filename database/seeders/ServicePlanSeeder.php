<?php

namespace Database\Seeders;

use App\Models\ServicePlan;
use App\Models\Host;
use Illuminate\Database\Seeder;

class ServicePlanSeeder extends Seeder
{
    public function run(): void
    {
        $hosts = Host::all();

        $servicePlans = [
            [
                'name' => 'Private Yoga Session',
                'description' => 'One-on-one yoga instruction tailored to your specific needs, goals, and experience level. Perfect for personalized attention and rapid progress.',
                'category' => 'private_training',
                'duration_minutes' => 60,
                'buffer_minutes' => 15,
                'price' => 80.00,
                'deposit_amount' => 20.00,
                'location_type' => 'in_studio',
                'max_participants' => 1,
                'color' => '#6366f1',
                'booking_notice_hours' => 24,
                'cancellation_hours' => 24,
                'is_active' => true,
                'is_visible_on_booking_page' => true,
            ],
            [
                'name' => 'Pilates Assessment',
                'description' => 'Initial consultation and movement assessment for new Pilates students. We\'ll evaluate your fitness level and create a personalized plan.',
                'category' => 'consultation',
                'duration_minutes' => 45,
                'buffer_minutes' => 15,
                'price' => 60.00,
                'deposit_amount' => null,
                'location_type' => 'in_studio',
                'max_participants' => 1,
                'color' => '#8b5cf6',
                'booking_notice_hours' => 48,
                'cancellation_hours' => 24,
                'is_active' => true,
                'is_visible_on_booking_page' => true,
            ],
            [
                'name' => 'Wellness Consultation',
                'description' => 'A holistic wellness consultation covering nutrition, stress management, and lifestyle habits to support your fitness journey.',
                'category' => 'consultation',
                'duration_minutes' => 30,
                'buffer_minutes' => 10,
                'price' => 50.00,
                'deposit_amount' => null,
                'location_type' => 'online',
                'max_participants' => 1,
                'color' => '#10b981',
                'booking_notice_hours' => 24,
                'cancellation_hours' => 12,
                'is_active' => true,
                'is_visible_on_booking_page' => true,
            ],
            [
                'name' => 'Personal Training',
                'description' => 'Intensive one-on-one fitness training session with customized exercises targeting your specific fitness goals.',
                'category' => 'private_training',
                'duration_minutes' => 60,
                'buffer_minutes' => 15,
                'price' => 90.00,
                'deposit_amount' => 25.00,
                'location_type' => 'in_studio',
                'max_participants' => 1,
                'color' => '#ef4444',
                'booking_notice_hours' => 24,
                'cancellation_hours' => 24,
                'is_active' => true,
                'is_visible_on_booking_page' => true,
            ],
            [
                'name' => 'Small Group Training',
                'description' => 'Semi-private training session for 2-4 people. Get personalized attention at a more affordable rate by training with friends.',
                'category' => 'private_training',
                'duration_minutes' => 60,
                'buffer_minutes' => 15,
                'price' => 45.00,
                'deposit_amount' => 15.00,
                'location_type' => 'in_studio',
                'max_participants' => 4,
                'color' => '#f59e0b',
                'booking_notice_hours' => 24,
                'cancellation_hours' => 24,
                'is_active' => true,
                'is_visible_on_booking_page' => true,
            ],
            [
                'name' => 'Sports Massage',
                'description' => 'Therapeutic massage session to help with muscle recovery, reduce tension, and improve flexibility.',
                'category' => 'therapy',
                'duration_minutes' => 60,
                'buffer_minutes' => 20,
                'price' => 95.00,
                'deposit_amount' => 30.00,
                'location_type' => 'in_studio',
                'max_participants' => 1,
                'color' => '#06b6d4',
                'booking_notice_hours' => 48,
                'cancellation_hours' => 48,
                'is_active' => true,
                'is_visible_on_booking_page' => true,
            ],
        ];

        foreach ($hosts as $host) {
            // Get active instructors for this host
            $instructors = $host->instructors()->where('is_active', true)->get();

            foreach ($servicePlans as $index => $plan) {
                $servicePlan = ServicePlan::create(array_merge($plan, [
                    'host_id' => $host->id,
                    'slug' => \Illuminate\Support\Str::slug($plan['name']),
                    'sort_order' => $index,
                ]));

                // Attach some instructors to each service plan
                if ($instructors->isNotEmpty()) {
                    // Randomly assign 1-3 instructors to each service
                    $assignCount = min(rand(1, 3), $instructors->count());
                    $selectedInstructors = $instructors->random($assignCount);

                    foreach ($selectedInstructors as $instructor) {
                        $servicePlan->instructors()->attach($instructor->id, [
                            'custom_price' => null, // Use default price
                            'is_active' => true,
                        ]);
                    }
                }
            }
        }
    }
}
