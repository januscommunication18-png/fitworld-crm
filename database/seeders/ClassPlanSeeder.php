<?php

namespace Database\Seeders;

use App\Models\ClassPlan;
use App\Models\Host;
use Illuminate\Database\Seeder;

class ClassPlanSeeder extends Seeder
{
    public function run(): void
    {
        $hosts = Host::all();

        $classPlans = [
            [
                'name' => 'Vinyasa Flow Yoga',
                'description' => 'A dynamic practice that links breath with movement. Flow through poses in a fluid sequence while building strength and flexibility.',
                'category' => 'yoga',
                'type' => 'group',
                'default_duration_minutes' => 60,
                'default_capacity' => 20,
                'min_capacity' => 3,
                'default_price' => 25.00,
                'drop_in_price' => 30.00,
                'color' => '#6366f1',
                'difficulty_level' => 'intermediate',
                'equipment_needed' => ['Yoga mat', 'Block (optional)', 'Strap (optional)'],
                'is_active' => true,
                'is_visible_on_booking_page' => true,
            ],
            [
                'name' => 'Gentle Yoga',
                'description' => 'A relaxing class perfect for beginners or those looking for a slower practice. Focus on gentle stretching and breath work.',
                'category' => 'yoga',
                'type' => 'group',
                'default_duration_minutes' => 45,
                'default_capacity' => 15,
                'min_capacity' => 2,
                'default_price' => 20.00,
                'drop_in_price' => 25.00,
                'color' => '#10b981',
                'difficulty_level' => 'beginner',
                'equipment_needed' => ['Yoga mat', 'Blanket (optional)'],
                'is_active' => true,
                'is_visible_on_booking_page' => true,
            ],
            [
                'name' => 'Power Pilates',
                'description' => 'An intense core-focused workout combining traditional Pilates with strength training. Build core stability and total body strength.',
                'category' => 'pilates',
                'type' => 'group',
                'default_duration_minutes' => 50,
                'default_capacity' => 12,
                'min_capacity' => 4,
                'default_price' => 28.00,
                'drop_in_price' => 35.00,
                'color' => '#f59e0b',
                'difficulty_level' => 'advanced',
                'equipment_needed' => ['Mat', 'Resistance band', 'Pilates ring'],
                'is_active' => true,
                'is_visible_on_booking_page' => true,
            ],
            [
                'name' => 'Mat Pilates Fundamentals',
                'description' => 'Learn the core principles of Pilates in this beginner-friendly class. Perfect for those new to Pilates or wanting to refine their technique.',
                'category' => 'pilates',
                'type' => 'group',
                'default_duration_minutes' => 55,
                'default_capacity' => 15,
                'min_capacity' => 3,
                'default_price' => 22.00,
                'drop_in_price' => 28.00,
                'color' => '#8b5cf6',
                'difficulty_level' => 'beginner',
                'equipment_needed' => ['Pilates mat'],
                'is_active' => true,
                'is_visible_on_booking_page' => true,
            ],
            [
                'name' => 'HIIT Bootcamp',
                'description' => 'High-intensity interval training combining cardio and strength exercises. Burn calories and build endurance in this challenging workout.',
                'category' => 'fitness',
                'type' => 'group',
                'default_duration_minutes' => 45,
                'default_capacity' => 25,
                'min_capacity' => 5,
                'default_price' => 30.00,
                'drop_in_price' => 35.00,
                'color' => '#ef4444',
                'difficulty_level' => 'all_levels',
                'equipment_needed' => ['Towel', 'Water bottle'],
                'is_active' => true,
                'is_visible_on_booking_page' => true,
            ],
            [
                'name' => 'Meditation & Breathwork',
                'description' => 'A calming session focused on meditation techniques and breathwork. Reduce stress and improve mental clarity.',
                'category' => 'wellness',
                'type' => 'group',
                'default_duration_minutes' => 30,
                'default_capacity' => 20,
                'min_capacity' => 1,
                'default_price' => 15.00,
                'drop_in_price' => 18.00,
                'color' => '#06b6d4',
                'difficulty_level' => 'all_levels',
                'equipment_needed' => ['Cushion or mat'],
                'is_active' => true,
                'is_visible_on_booking_page' => true,
            ],
        ];

        foreach ($hosts as $host) {
            foreach ($classPlans as $index => $plan) {
                ClassPlan::create(array_merge($plan, [
                    'host_id' => $host->id,
                    'slug' => \Illuminate\Support\Str::slug($plan['name']),
                    'sort_order' => $index,
                ]));
            }
        }
    }
}
