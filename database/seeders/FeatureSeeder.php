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
                'description' => 'Allow team members to offer personalized 1:1 booking pages. Clients can book meetings directly from instructor profiles.',
                'icon' => 'calendar-user',
                'type' => Feature::TYPE_FREE,
                'category' => Feature::CATEGORY_TOOLS,
                'is_active' => true,
                'sort_order' => 2,
                'config_schema' => [
                    'default_durations' => [
                        'type' => 'multiselect',
                        'label' => 'Available Meeting Durations',
                        'options' => [15, 30, 45, 60],
                    ],
                    'require_phone' => [
                        'type' => 'boolean',
                        'label' => 'Require Guest Phone Number',
                        'default' => false,
                    ],
                    'send_reminders' => [
                        'type' => 'boolean',
                        'label' => 'Send Meeting Reminders',
                        'default' => true,
                    ],
                    'reminder_hours' => [
                        'type' => 'select',
                        'label' => 'Reminder Hours Before Meeting',
                        'options' => [1, 2, 4, 12, 24],
                    ],
                    // Buffer & Limits
                    'buffer_before' => [
                        'type' => 'select',
                        'label' => 'Buffer Before Meetings (minutes)',
                        'options' => [0, 5, 10, 15, 30],
                    ],
                    'buffer_after' => [
                        'type' => 'select',
                        'label' => 'Buffer After Meetings (minutes)',
                        'options' => [0, 5, 10, 15, 30],
                    ],
                    'min_notice_hours' => [
                        'type' => 'select',
                        'label' => 'Minimum Notice Required (hours)',
                        'options' => [1, 2, 4, 12, 24, 48, 72],
                    ],
                    'max_advance_days' => [
                        'type' => 'select',
                        'label' => 'Maximum Advance Booking (days)',
                        'options' => [7, 14, 30, 60, 90],
                    ],
                    // Reschedule & Cancellation
                    'allow_reschedule' => [
                        'type' => 'boolean',
                        'label' => 'Allow Clients to Reschedule',
                        'default' => true,
                    ],
                    'reschedule_cutoff_hours' => [
                        'type' => 'select',
                        'label' => 'Reschedule Cutoff (hours before)',
                        'options' => [1, 2, 4, 12, 24, 48],
                    ],
                    'allow_cancel' => [
                        'type' => 'boolean',
                        'label' => 'Allow Clients to Cancel',
                        'default' => true,
                    ],
                    'cancel_cutoff_hours' => [
                        'type' => 'select',
                        'label' => 'Cancellation Cutoff (hours before)',
                        'options' => [1, 2, 4, 12, 24, 48],
                    ],
                ],
                'default_config' => [
                    'default_durations' => [30, 60],
                    'require_phone' => false,
                    'send_reminders' => true,
                    'reminder_hours' => 24,
                    // Buffer & Limits
                    'buffer_before' => 10,
                    'buffer_after' => 10,
                    'min_notice_hours' => 24,
                    'max_advance_days' => 60,
                    // Reschedule & Cancellation
                    'allow_reschedule' => true,
                    'reschedule_cutoff_hours' => 24,
                    'allow_cancel' => true,
                    'cancel_cutoff_hours' => 24,
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
                'is_active' => true,
                'sort_order' => 4,
            ],

            // Integrations
            [
                'name' => 'FitNearYou Sync',
                'slug' => 'fitnearyou-sync',
                'description' => 'Sync your classes, services, deals, and events with FitNearYou marketplace. Expand your reach and get discovered by new clients in your area.',
                'icon' => 'cloud-share',
                'type' => Feature::TYPE_FREE,
                'category' => Feature::CATEGORY_INTEGRATIONS,
                'is_active' => true,
                'sort_order' => 1,
                'config_schema' => [
                    'api_key' => [
                        'type' => 'text',
                        'label' => 'API Key',
                        'readonly' => true,
                    ],
                    'api_secret' => [
                        'type' => 'password',
                        'label' => 'API Secret',
                        'readonly' => true,
                    ],
                    'sync_classes' => [
                        'type' => 'boolean',
                        'label' => 'Sync Classes',
                        'default' => true,
                    ],
                    'sync_services' => [
                        'type' => 'boolean',
                        'label' => 'Sync Services',
                        'default' => true,
                    ],
                    'sync_deals' => [
                        'type' => 'boolean',
                        'label' => 'Sync Deals & Promotions',
                        'default' => true,
                    ],
                    'sync_events' => [
                        'type' => 'boolean',
                        'label' => 'Sync Events',
                        'default' => true,
                    ],
                    'sync_schedule' => [
                        'type' => 'boolean',
                        'label' => 'Sync Class Schedule',
                        'default' => true,
                    ],
                ],
                'default_config' => [
                    'api_key' => null,
                    'api_secret' => null,
                    'sync_classes' => true,
                    'sync_services' => true,
                    'sync_deals' => true,
                    'sync_events' => true,
                    'sync_schedule' => true,
                ],
            ],
        ];

        foreach ($features as $feature) {
            Feature::firstOrCreate(
                ['slug' => $feature['slug']],
                $feature
            );
        }

        if ($this->command) {
            $this->command->info('Features seeded successfully!');
        }
    }
}
