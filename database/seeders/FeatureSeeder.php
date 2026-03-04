<?php

namespace Database\Seeders;

use App\Models\Feature;
use Illuminate\Database\Seeder;

class FeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $features = [
            // Tools & Features
            [
                'name' => 'Progress Templates',
                'slug' => 'progress-templates',
                'description' => 'Track and visualize client progress over time with customizable templates. Perfect for martial arts, fitness tracking, and personal training.',
                'icon' => 'chart-line',
                'type' => Feature::TYPE_FREE,
                'category' => Feature::CATEGORY_TOOLS,
                'is_active' => true,
                'sort_order' => 1,
                'config_schema' => [
                    'chart_type' => [
                        'type' => 'select',
                        'label' => 'Default Chart Type',
                        'options' => ['line', 'bar', 'radar'],
                    ],
                    'show_milestones' => [
                        'type' => 'boolean',
                        'label' => 'Show Milestones',
                        'default' => true,
                    ],
                    'primary_color' => [
                        'type' => 'color',
                        'label' => 'Chart Color',
                        'default' => '#6366f1',
                    ],
                ],
                'default_config' => [
                    'chart_type' => 'line',
                    'show_milestones' => true,
                    'primary_color' => '#6366f1',
                ],
            ],
            [
                'name' => 'Online 1:1 Meeting',
                'slug' => 'online-1on1-meeting',
                'description' => 'Lightweight scheduling for 1:1 sessions with your clients. Includes video call integration and automatic reminders.',
                'icon' => 'video',
                'type' => Feature::TYPE_PREMIUM,
                'category' => Feature::CATEGORY_TOOLS,
                'is_active' => true,
                'sort_order' => 2,
                'config_schema' => [
                    'meeting_duration' => [
                        'type' => 'select',
                        'label' => 'Default Duration',
                        'options' => [15, 30, 45, 60],
                    ],
                    'buffer_time' => [
                        'type' => 'select',
                        'label' => 'Buffer Between Meetings',
                        'options' => [0, 5, 10, 15],
                    ],
                ],
                'default_config' => [
                    'meeting_duration' => 30,
                    'buffer_time' => 10,
                ],
            ],

            // Calendar Integrations
            [
                'name' => 'Google Calendar Sync',
                'slug' => 'google-calendar-sync',
                'description' => 'Sync your class schedule with Google Calendar. Clients can add classes directly to their calendar.',
                'icon' => 'brand-google',
                'type' => Feature::TYPE_FREE,
                'category' => Feature::CATEGORY_CALENDAR,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'iCloud Calendar Sync',
                'slug' => 'icloud-calendar-sync',
                'description' => 'Sync your class schedule with Apple iCloud Calendar for seamless integration with Apple devices.',
                'icon' => 'brand-apple',
                'type' => Feature::TYPE_FREE,
                'category' => Feature::CATEGORY_CALENDAR,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Outlook Calendar Sync',
                'slug' => 'outlook-calendar-sync',
                'description' => 'Sync your class schedule with Microsoft Outlook Calendar for enterprise and business users.',
                'icon' => 'brand-windows',
                'type' => Feature::TYPE_FREE,
                'category' => Feature::CATEGORY_CALENDAR,
                'is_active' => true,
                'sort_order' => 3,
            ],

            // Payment Systems (Coming Later)
            [
                'name' => 'European Payment System',
                'slug' => 'european-payment-system',
                'description' => 'Accept payments via European payment methods including SEPA, iDEAL, Bancontact, and more.',
                'icon' => 'currency-euro',
                'type' => Feature::TYPE_PREMIUM,
                'category' => Feature::CATEGORY_PAYMENTS,
                'is_active' => false, // Coming later
                'sort_order' => 1,
            ],
            [
                'name' => 'Australian Payment System',
                'slug' => 'australian-payment-system',
                'description' => 'Accept payments via Australian payment methods including BPAY, POLi, and local bank transfers.',
                'icon' => 'currency-dollar',
                'type' => Feature::TYPE_PREMIUM,
                'category' => Feature::CATEGORY_PAYMENTS,
                'is_active' => false,
                'sort_order' => 2,
            ],
            [
                'name' => 'Indian Payment System',
                'slug' => 'indian-payment-system',
                'description' => 'Accept payments via Indian payment methods including UPI, Paytm, PhonePe, and local banks.',
                'icon' => 'currency-rupee',
                'type' => Feature::TYPE_PREMIUM,
                'category' => Feature::CATEGORY_PAYMENTS,
                'is_active' => false,
                'sort_order' => 3,
            ],
            [
                'name' => 'Price Override',
                'slug' => 'price-override',
                'description' => 'Allow custom pricing for individual clients or bookings. Perfect for corporate deals and special arrangements.',
                'icon' => 'receipt-2',
                'type' => Feature::TYPE_PREMIUM,
                'category' => Feature::CATEGORY_PAYMENTS,
                'is_active' => false,
                'sort_order' => 4,
            ],
        ];

        foreach ($features as $feature) {
            Feature::firstOrCreate(
                ['slug' => $feature['slug']],
                $feature
            );
        }

        $this->command->info('Features seeded successfully!');
    }
}
