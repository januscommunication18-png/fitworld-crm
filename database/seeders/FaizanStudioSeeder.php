<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Host;
use App\Models\Instructor;
use App\Models\Client;
use App\Models\StudioClass;
use App\Models\ClassSession;
use App\Models\ClassPlan;
use App\Models\Service;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Location;
use App\Models\Room;
use App\Models\BookingProfile;
use App\Models\Feature;
use App\Models\HostFeature;
use App\Models\Plan;
use App\Models\ServicePlan;
use App\Models\ClassPass;
use App\Models\RentalItem;
use App\Models\Booking;
use App\Models\ServiceSlot;
use App\Models\SpaceRental;
use App\Models\SpaceRentalConfig;
use App\Models\RentalBooking;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FaizanStudioSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Owner User
        $owner = User::updateOrCreate(
            ['email' => 'faizanhumayun486@gmail.com'],
            [
                'first_name' => 'Faizan',
                'last_name' => 'Humayun',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        $this->command->info("Owner user created: {$owner->email}");

        // 2. Create Host/Studio
        $host = Host::updateOrCreate(
            ['subdomain' => 'crossfit'],
            [
                'studio_name' => 'Faizan Fitness Studio',
                'studio_types' => ['gym', 'fitness'],
                'phone' => '+1 (555) 123-4567',
                'studio_email' => 'info@faizanfitness.com',
                'about' => 'Welcome to Faizan Fitness Studio - your premier destination for fitness excellence. We offer a wide range of classes, personal training, and wellness services.',
                'address' => '123 Fitness Street, Los Angeles, CA 90001',
                'city' => 'Los Angeles',
                'country' => 'US',
                'timezone' => 'America/Los_Angeles',
                'default_currency' => 'USD',
                'currencies' => ['USD'],
                'status' => 'active',
                'is_live' => true,
                'plan_id' => Plan::where('slug', 'premium')->first()?->id ?? Plan::first()?->id,
                'onboarding_completed_at' => now(),
            ]
        );

        // Link owner to host via pivot table
        $owner->hosts()->syncWithoutDetaching([
            $host->id => [
                'role' => User::ROLE_OWNER,
                'is_primary' => true,
                'joined_at' => now(),
            ]
        ]);
        $owner->update(['host_id' => $host->id]);

        $this->command->info("Studio created: {$host->studio_name} ({$host->subdomain})");

        // 3. Create Location
        $location = Location::updateOrCreate(
            ['host_id' => $host->id, 'name' => 'Main Studio'],
            [
                'address_line_1' => '123 Fitness Street',
                'city' => 'Los Angeles',
                'state' => 'CA',
                'postal_code' => '90001',
                'country' => 'US',
                'location_type' => 'in_person',
                'is_default' => true,
            ]
        );

        // 4. Create Rooms
        $rooms = [
            ['name' => 'Main Floor', 'capacity' => 30, 'description' => 'Large open space for group classes'],
            ['name' => 'Yoga Studio', 'capacity' => 20, 'description' => 'Peaceful studio with mirrors and props'],
            ['name' => 'Spin Room', 'capacity' => 25, 'description' => 'Dedicated cycling studio'],
            ['name' => 'Private Training', 'capacity' => 4, 'description' => 'Personal training sessions'],
        ];

        foreach ($rooms as $roomData) {
            Room::updateOrCreate(
                ['location_id' => $location->id, 'name' => $roomData['name']],
                array_merge($roomData, ['location_id' => $location->id, 'is_active' => true])
            );
        }

        $this->command->info("Location and rooms created");

        // 5. Create Instructors
        $instructors = [
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah@faizanfitness.com',
                'phone' => '+1 (555) 111-1111',
                'bio' => 'Certified yoga instructor with 10 years of experience. Specializes in Vinyasa and Restorative yoga.',
                'specialties' => ['Yoga', 'Pilates', 'Meditation'],
                'is_active' => true,
            ],
            [
                'name' => 'Mike Thompson',
                'email' => 'mike@faizanfitness.com',
                'phone' => '+1 (555) 222-2222',
                'bio' => 'Former professional athlete turned fitness coach. Expert in strength training and HIIT.',
                'specialties' => ['CrossFit', 'Strength Training', 'HIIT'],
                'is_active' => true,
            ],
            [
                'name' => 'Emily Chen',
                'email' => 'emily@faizanfitness.com',
                'phone' => '+1 (555) 333-3333',
                'bio' => 'Dance fitness enthusiast with certifications in Zumba and dance cardio.',
                'specialties' => ['Zumba', 'Dance Fitness', 'Cardio'],
                'is_active' => true,
            ],
            [
                'name' => 'David Martinez',
                'email' => 'david@faizanfitness.com',
                'phone' => '+1 (555) 444-4444',
                'bio' => 'Spinning instructor and cycling coach. Brings high energy to every class.',
                'specialties' => ['Spinning', 'Cycling', 'Endurance'],
                'is_active' => true,
            ],
        ];

        $createdInstructors = [];
        foreach ($instructors as $instructorData) {
            $instructor = Instructor::updateOrCreate(
                ['host_id' => $host->id, 'email' => $instructorData['email']],
                array_merge($instructorData, ['host_id' => $host->id])
            );
            $createdInstructors[] = $instructor;
        }

        // Also create instructor record for owner
        $ownerInstructor = Instructor::updateOrCreate(
            ['host_id' => $host->id, 'user_id' => $owner->id],
            [
                'host_id' => $host->id,
                'user_id' => $owner->id,
                'name' => $owner->name,
                'email' => $owner->email,
                'bio' => 'Studio owner and head trainer with over 15 years of experience in fitness and wellness.',
                'specialties' => ['Personal Training', 'Nutrition', 'Wellness Coaching'],
                'is_active' => true,
            ]
        );
        $createdInstructors[] = $ownerInstructor;

        $this->command->info("Instructors created: " . count($createdInstructors));

        // 6. Create Class Plans (templates)
        $classPlans = [
            [
                'name' => 'Morning Yoga Flow',
                'description' => 'Start your day with an energizing yoga session. Suitable for all levels.',
                'default_duration_minutes' => 60,
                'default_capacity' => 20,
                'default_price' => 15.00,
                'category' => 'yoga',
                'difficulty_level' => 'beginner',
                'is_active' => true,
            ],
            [
                'name' => 'Power HIIT',
                'description' => 'High-intensity interval training to burn calories and build strength.',
                'default_duration_minutes' => 45,
                'default_capacity' => 25,
                'default_price' => 20.00,
                'category' => 'hiit',
                'difficulty_level' => 'intermediate',
                'is_active' => true,
            ],
            [
                'name' => 'Zumba Party',
                'description' => 'Dance your way to fitness with this fun Latin-inspired workout.',
                'default_duration_minutes' => 60,
                'default_capacity' => 30,
                'default_price' => 18.00,
                'category' => 'dance',
                'difficulty_level' => 'beginner',
                'is_active' => true,
            ],
            [
                'name' => 'Spin & Burn',
                'description' => 'Indoor cycling class with great music and motivation.',
                'default_duration_minutes' => 45,
                'default_capacity' => 25,
                'default_price' => 22.00,
                'category' => 'cycling',
                'difficulty_level' => 'intermediate',
                'is_active' => true,
            ],
            [
                'name' => 'CrossFit WOD',
                'description' => 'Workout of the day featuring functional movements at high intensity.',
                'default_duration_minutes' => 60,
                'default_capacity' => 15,
                'default_price' => 25.00,
                'category' => 'crossfit',
                'difficulty_level' => 'advanced',
                'is_active' => true,
            ],
            [
                'name' => 'Restorative Yoga',
                'description' => 'Gentle yoga practice focused on relaxation and recovery.',
                'default_duration_minutes' => 75,
                'default_capacity' => 15,
                'default_price' => 18.00,
                'category' => 'yoga',
                'difficulty_level' => 'beginner',
                'is_active' => true,
            ],
        ];

        $createdClassPlans = [];
        foreach ($classPlans as $planData) {
            $classPlan = ClassPlan::updateOrCreate(
                ['host_id' => $host->id, 'name' => $planData['name']],
                array_merge($planData, ['host_id' => $host->id])
            );
            $createdClassPlans[] = $classPlan;
        }

        $this->command->info("Class plans created: " . count($createdClassPlans));

        // 7. Create Class Sessions (Schedule for next 2 weeks)
        $roomIds = Room::where('location_id', $location->id)->pluck('id', 'name')->toArray();
        $sessionCount = 0;

        // Mapping class plans to instructors
        $classInstructors = [
            0 => $createdInstructors[0]->id, // Morning Yoga -> Sarah
            1 => $createdInstructors[1]->id, // Power HIIT -> Mike
            2 => $createdInstructors[2]->id, // Zumba -> Emily
            3 => $createdInstructors[3]->id, // Spin -> David
            4 => $createdInstructors[1]->id, // CrossFit -> Mike
            5 => $createdInstructors[0]->id, // Restorative Yoga -> Sarah
        ];

        for ($day = 0; $day < 14; $day++) {
            $date = Carbon::today()->addDays($day);

            // Morning Yoga - Mon, Wed, Fri, Sat at 7:00 AM
            if (in_array($date->dayOfWeek, [1, 3, 5, 6])) {
                ClassSession::updateOrCreate(
                    ['host_id' => $host->id, 'class_plan_id' => $createdClassPlans[0]->id, 'start_time' => $date->copy()->setTime(7, 0)],
                    [
                        'host_id' => $host->id,
                        'class_plan_id' => $createdClassPlans[0]->id,
                        'primary_instructor_id' => $classInstructors[0],
                        'location_id' => $location->id,
                        'room_id' => $roomIds['Yoga Studio'] ?? null,
                        'start_time' => $date->copy()->setTime(7, 0),
                        'end_time' => $date->copy()->setTime(8, 0),
                        'duration_minutes' => 60,
                        'capacity' => 20,
                        'status' => 'scheduled',
                    ]
                );
                $sessionCount++;
            }

            // Power HIIT - Mon, Tue, Thu at 6:00 PM
            if (in_array($date->dayOfWeek, [1, 2, 4])) {
                ClassSession::updateOrCreate(
                    ['host_id' => $host->id, 'class_plan_id' => $createdClassPlans[1]->id, 'start_time' => $date->copy()->setTime(18, 0)],
                    [
                        'host_id' => $host->id,
                        'class_plan_id' => $createdClassPlans[1]->id,
                        'primary_instructor_id' => $classInstructors[1],
                        'location_id' => $location->id,
                        'room_id' => $roomIds['Main Floor'] ?? null,
                        'start_time' => $date->copy()->setTime(18, 0),
                        'end_time' => $date->copy()->setTime(18, 45),
                        'duration_minutes' => 45,
                        'capacity' => 25,
                        'status' => 'scheduled',
                    ]
                );
                $sessionCount++;
            }

            // Zumba - Tue, Thu, Sat at 10:00 AM
            if (in_array($date->dayOfWeek, [2, 4, 6])) {
                ClassSession::updateOrCreate(
                    ['host_id' => $host->id, 'class_plan_id' => $createdClassPlans[2]->id, 'start_time' => $date->copy()->setTime(10, 0)],
                    [
                        'host_id' => $host->id,
                        'class_plan_id' => $createdClassPlans[2]->id,
                        'primary_instructor_id' => $classInstructors[2],
                        'location_id' => $location->id,
                        'room_id' => $roomIds['Main Floor'] ?? null,
                        'start_time' => $date->copy()->setTime(10, 0),
                        'end_time' => $date->copy()->setTime(11, 0),
                        'duration_minutes' => 60,
                        'capacity' => 30,
                        'status' => 'scheduled',
                    ]
                );
                $sessionCount++;
            }

            // Spin - Mon, Wed, Fri at 5:30 PM
            if (in_array($date->dayOfWeek, [1, 3, 5])) {
                ClassSession::updateOrCreate(
                    ['host_id' => $host->id, 'class_plan_id' => $createdClassPlans[3]->id, 'start_time' => $date->copy()->setTime(17, 30)],
                    [
                        'host_id' => $host->id,
                        'class_plan_id' => $createdClassPlans[3]->id,
                        'primary_instructor_id' => $classInstructors[3],
                        'location_id' => $location->id,
                        'room_id' => $roomIds['Spin Room'] ?? null,
                        'start_time' => $date->copy()->setTime(17, 30),
                        'end_time' => $date->copy()->setTime(18, 15),
                        'duration_minutes' => 45,
                        'capacity' => 25,
                        'status' => 'scheduled',
                    ]
                );
                $sessionCount++;
            }

            // CrossFit - Mon, Wed, Fri, Sat at 8:00 AM
            if (in_array($date->dayOfWeek, [1, 3, 5, 6])) {
                ClassSession::updateOrCreate(
                    ['host_id' => $host->id, 'class_plan_id' => $createdClassPlans[4]->id, 'start_time' => $date->copy()->setTime(8, 0)],
                    [
                        'host_id' => $host->id,
                        'class_plan_id' => $createdClassPlans[4]->id,
                        'primary_instructor_id' => $classInstructors[4],
                        'location_id' => $location->id,
                        'room_id' => $roomIds['Main Floor'] ?? null,
                        'start_time' => $date->copy()->setTime(8, 0),
                        'end_time' => $date->copy()->setTime(9, 0),
                        'duration_minutes' => 60,
                        'capacity' => 15,
                        'status' => 'scheduled',
                    ]
                );
                $sessionCount++;
            }

            // Restorative Yoga - Sun at 9:00 AM
            if ($date->dayOfWeek === 0) {
                ClassSession::updateOrCreate(
                    ['host_id' => $host->id, 'class_plan_id' => $createdClassPlans[5]->id, 'start_time' => $date->copy()->setTime(9, 0)],
                    [
                        'host_id' => $host->id,
                        'class_plan_id' => $createdClassPlans[5]->id,
                        'primary_instructor_id' => $classInstructors[5],
                        'location_id' => $location->id,
                        'room_id' => $roomIds['Yoga Studio'] ?? null,
                        'start_time' => $date->copy()->setTime(9, 0),
                        'end_time' => $date->copy()->setTime(10, 15),
                        'duration_minutes' => 75,
                        'capacity' => 15,
                        'status' => 'scheduled',
                    ]
                );
                $sessionCount++;
            }
        }

        $this->command->info("Class sessions created: {$sessionCount}");

        // 8. Create Membership Plans
        $membershipPlans = [
            [
                'name' => 'Basic Monthly',
                'description' => 'Access to all group classes',
                'price' => 49.00,
                'interval' => 'monthly',
                'type' => 'recurring',
                'status' => 'active',
            ],
            [
                'name' => 'Premium Monthly',
                'description' => 'Full access including personal training credits',
                'price' => 99.00,
                'interval' => 'monthly',
                'type' => 'recurring',
                'status' => 'active',
            ],
            [
                'name' => 'Annual All-Access',
                'description' => 'Best value - full year membership',
                'price' => 899.00,
                'interval' => 'yearly',
                'type' => 'recurring',
                'status' => 'active',
            ],
            [
                'name' => '10-Class Pack',
                'description' => 'Flexible class pack - use anytime',
                'price' => 120.00,
                'interval' => 'one_time',
                'type' => 'credit',
                'credits_per_cycle' => 10,
                'status' => 'active',
            ],
        ];

        foreach ($membershipPlans as $planData) {
            MembershipPlan::updateOrCreate(
                ['host_id' => $host->id, 'name' => $planData['name']],
                array_merge($planData, ['host_id' => $host->id])
            );
        }

        $this->command->info("Membership plans created: " . count($membershipPlans));

        // 9. Create Clients
        $clients = [
            ['first_name' => 'John', 'last_name' => 'Smith', 'email' => 'john.smith@email.com', 'phone' => '+1 (555) 100-0001'],
            ['first_name' => 'Emma', 'last_name' => 'Wilson', 'email' => 'emma.wilson@email.com', 'phone' => '+1 (555) 100-0002'],
            ['first_name' => 'Michael', 'last_name' => 'Brown', 'email' => 'michael.brown@email.com', 'phone' => '+1 (555) 100-0003'],
            ['first_name' => 'Sophia', 'last_name' => 'Davis', 'email' => 'sophia.davis@email.com', 'phone' => '+1 (555) 100-0004'],
            ['first_name' => 'William', 'last_name' => 'Garcia', 'email' => 'william.garcia@email.com', 'phone' => '+1 (555) 100-0005'],
            ['first_name' => 'Olivia', 'last_name' => 'Martinez', 'email' => 'olivia.martinez@email.com', 'phone' => '+1 (555) 100-0006'],
            ['first_name' => 'James', 'last_name' => 'Anderson', 'email' => 'james.anderson@email.com', 'phone' => '+1 (555) 100-0007'],
            ['first_name' => 'Ava', 'last_name' => 'Taylor', 'email' => 'ava.taylor@email.com', 'phone' => '+1 (555) 100-0008'],
            ['first_name' => 'Robert', 'last_name' => 'Thomas', 'email' => 'robert.thomas@email.com', 'phone' => '+1 (555) 100-0009'],
            ['first_name' => 'Isabella', 'last_name' => 'Moore', 'email' => 'isabella.moore@email.com', 'phone' => '+1 (555) 100-0010'],
        ];

        foreach ($clients as $clientData) {
            Client::updateOrCreate(
                ['host_id' => $host->id, 'email' => $clientData['email']],
                array_merge($clientData, ['host_id' => $host->id, 'status' => 'client'])
            );
        }

        $this->command->info("Clients created: " . count($clients));

        // 10. Create Team Members (Users linked to instructors)
        $teamMembers = [
            ['email' => 'sarah@faizanfitness.com', 'first_name' => 'Sarah', 'last_name' => 'Johnson', 'role' => User::ROLE_INSTRUCTOR],
            ['email' => 'mike@faizanfitness.com', 'first_name' => 'Mike', 'last_name' => 'Thompson', 'role' => User::ROLE_INSTRUCTOR],
        ];

        foreach ($teamMembers as $memberData) {
            $user = User::updateOrCreate(
                ['email' => $memberData['email']],
                [
                    'first_name' => $memberData['first_name'],
                    'last_name' => $memberData['last_name'],
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                    'host_id' => $host->id,
                ]
            );

            // Link to host via pivot
            $user->hosts()->syncWithoutDetaching([
                $host->id => [
                    'role' => $memberData['role'],
                    'is_primary' => true,
                    'joined_at' => now(),
                ]
            ]);

            // Link to instructor
            Instructor::where('host_id', $host->id)
                ->where('email', $memberData['email'])
                ->update(['user_id' => $user->id]);
        }

        $this->command->info("Team members created: " . count($teamMembers));

        // 11. Create Service Plans
        $servicePlans = [
            [
                'name' => 'Personal Training Session',
                'description' => 'One-on-one training session with a certified personal trainer. Customized workout based on your goals.',
                'category' => 'training',
                'duration_minutes' => 60,
                'buffer_minutes' => 15,
                'price' => 75.00,
                'max_participants' => 1,
                'location_type' => 'in_person',
                'is_active' => true,
            ],
            [
                'name' => 'Nutrition Consultation',
                'description' => 'Personalized nutrition planning and dietary advice from our certified nutritionist.',
                'category' => 'wellness',
                'duration_minutes' => 45,
                'buffer_minutes' => 10,
                'price' => 50.00,
                'max_participants' => 1,
                'location_type' => 'in_person',
                'is_active' => true,
            ],
            [
                'name' => 'Fitness Assessment',
                'description' => 'Comprehensive fitness evaluation including body composition, strength tests, and flexibility assessment.',
                'category' => 'assessment',
                'duration_minutes' => 60,
                'buffer_minutes' => 15,
                'price' => 40.00,
                'max_participants' => 1,
                'location_type' => 'in_person',
                'is_active' => true,
            ],
            [
                'name' => 'Sports Massage',
                'description' => 'Deep tissue massage for muscle recovery, relaxation, and injury prevention.',
                'category' => 'recovery',
                'duration_minutes' => 60,
                'buffer_minutes' => 15,
                'price' => 80.00,
                'max_participants' => 1,
                'location_type' => 'in_person',
                'is_active' => true,
            ],
            [
                'name' => 'Small Group Training',
                'description' => 'Semi-private training session for 2-4 people. Great for friends or couples.',
                'category' => 'training',
                'duration_minutes' => 60,
                'buffer_minutes' => 15,
                'price' => 40.00,
                'max_participants' => 4,
                'location_type' => 'in_person',
                'is_active' => true,
            ],
            [
                'name' => 'Online Coaching Session',
                'description' => 'Virtual training session via video call. Train from anywhere.',
                'category' => 'training',
                'duration_minutes' => 45,
                'buffer_minutes' => 10,
                'price' => 55.00,
                'max_participants' => 1,
                'location_type' => 'virtual',
                'is_active' => true,
            ],
        ];

        foreach ($servicePlans as $serviceData) {
            ServicePlan::updateOrCreate(
                ['host_id' => $host->id, 'name' => $serviceData['name']],
                array_merge($serviceData, ['host_id' => $host->id])
            );
        }

        $this->command->info("Service plans created: " . count($servicePlans));

        // 12. Create Class Passes
        $classPasses = [
            [
                'name' => '5-Class Pack',
                'description' => 'Perfect for trying out different classes. Valid for 30 days.',
                'class_count' => 5,
                'price' => 70.00,
                'expires_after_days' => 30,
                'activation_type' => 'on_purchase',
                'eligibility_type' => 'all',
                'status' => 'active',
            ],
            [
                'name' => '10-Class Pack',
                'description' => 'Our most popular option. Valid for 60 days.',
                'class_count' => 10,
                'price' => 120.00,
                'expires_after_days' => 60,
                'activation_type' => 'on_purchase',
                'eligibility_type' => 'all',
                'status' => 'active',
            ],
            [
                'name' => '20-Class Pack',
                'description' => 'Best value for regular attendees. Valid for 90 days.',
                'class_count' => 20,
                'price' => 200.00,
                'expires_after_days' => 90,
                'activation_type' => 'on_purchase',
                'eligibility_type' => 'all',
                'status' => 'active',
            ],
            [
                'name' => 'Unlimited Monthly',
                'description' => 'Unlimited classes for 30 days. Auto-renews monthly.',
                'class_count' => 999,
                'price' => 149.00,
                'expires_after_days' => 30,
                'activation_type' => 'on_purchase',
                'eligibility_type' => 'all',
                'is_recurring' => true,
                'renewal_interval' => 'monthly',
                'status' => 'active',
            ],
            [
                'name' => 'Yoga Only Pack',
                'description' => '10 classes valid only for yoga sessions.',
                'class_count' => 10,
                'price' => 100.00,
                'expires_after_days' => 60,
                'activation_type' => 'on_purchase',
                'eligibility_type' => 'categories',
                'eligible_categories' => ['yoga'],
                'status' => 'active',
            ],
            [
                'name' => 'Intro Offer - 3 Classes',
                'description' => 'New member special! Try 3 classes at a discounted rate.',
                'class_count' => 3,
                'price' => 30.00,
                'expires_after_days' => 14,
                'activation_type' => 'on_first_booking',
                'eligibility_type' => 'all',
                'status' => 'active',
            ],
        ];

        foreach ($classPasses as $passData) {
            ClassPass::updateOrCreate(
                ['host_id' => $host->id, 'name' => $passData['name']],
                array_merge($passData, ['host_id' => $host->id])
            );
        }

        $this->command->info("Class passes created: " . count($classPasses));

        // 13. Create Rental Items
        $rentalItems = [
            [
                'name' => 'Yoga Mat',
                'description' => 'Premium non-slip yoga mat. Perfect for yoga and floor exercises.',
                'sku' => 'RENT-MAT-001',
                'category' => 'mats',
                'prices' => ['USD' => 3.00],
                'deposit_amount' => 10.00,
                'total_inventory' => 20,
                'available_inventory' => 20,
                'requires_return' => true,
                'max_rental_days' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Resistance Bands Set',
                'description' => 'Set of 5 resistance bands with different resistance levels.',
                'sku' => 'RENT-BAND-001',
                'category' => 'equipment',
                'prices' => ['USD' => 5.00],
                'deposit_amount' => 15.00,
                'total_inventory' => 15,
                'available_inventory' => 15,
                'requires_return' => true,
                'max_rental_days' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Foam Roller',
                'description' => 'High-density foam roller for muscle recovery and self-massage.',
                'sku' => 'RENT-ROLL-001',
                'category' => 'recovery',
                'prices' => ['USD' => 4.00],
                'deposit_amount' => 20.00,
                'total_inventory' => 10,
                'available_inventory' => 10,
                'requires_return' => true,
                'max_rental_days' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Kettlebell (15 lb)',
                'description' => 'Cast iron kettlebell for strength and cardio workouts.',
                'sku' => 'RENT-KB-015',
                'category' => 'weights',
                'prices' => ['USD' => 8.00],
                'deposit_amount' => 30.00,
                'total_inventory' => 8,
                'available_inventory' => 8,
                'requires_return' => true,
                'max_rental_days' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Kettlebell (25 lb)',
                'description' => 'Cast iron kettlebell for intermediate strength training.',
                'sku' => 'RENT-KB-025',
                'category' => 'weights',
                'prices' => ['USD' => 10.00],
                'deposit_amount' => 40.00,
                'total_inventory' => 6,
                'available_inventory' => 6,
                'requires_return' => true,
                'max_rental_days' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Jump Rope',
                'description' => 'Adjustable speed jump rope for cardio workouts.',
                'sku' => 'RENT-ROPE-001',
                'category' => 'cardio',
                'prices' => ['USD' => 2.00],
                'deposit_amount' => 5.00,
                'total_inventory' => 25,
                'available_inventory' => 25,
                'requires_return' => true,
                'max_rental_days' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Heart Rate Monitor',
                'description' => 'Chest strap heart rate monitor compatible with our systems.',
                'sku' => 'RENT-HRM-001',
                'category' => 'tech',
                'prices' => ['USD' => 5.00],
                'deposit_amount' => 50.00,
                'total_inventory' => 12,
                'available_inventory' => 12,
                'requires_return' => true,
                'max_rental_days' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Yoga Block (Pair)',
                'description' => 'Set of 2 foam yoga blocks for support and alignment.',
                'sku' => 'RENT-BLOCK-001',
                'category' => 'props',
                'prices' => ['USD' => 2.00],
                'deposit_amount' => 8.00,
                'total_inventory' => 30,
                'available_inventory' => 30,
                'requires_return' => true,
                'max_rental_days' => 1,
                'is_active' => true,
            ],
        ];

        foreach ($rentalItems as $itemData) {
            RentalItem::updateOrCreate(
                ['host_id' => $host->id, 'sku' => $itemData['sku']],
                array_merge($itemData, ['host_id' => $host->id])
            );
        }

        $this->command->info("Rental items created: " . count($rentalItems));

        // 14. Create Space Rental Configs (for Rental Spaces catalog)
        $spaceRentalConfigs = [
            [
                'name' => 'Main Floor Studio',
                'description' => 'Large open floor space perfect for photo shoots, workshops, and events.',
                'rentable_type' => 'room',
                'room_id' => $roomIds['Main Floor'] ?? null,
                'location_id' => $location->id,
                'hourly_rates' => ['USD' => 75.00],
                'deposit_rates' => ['USD' => 150.00],
                'minimum_hours' => 2,
                'maximum_hours' => 8,
                'allowed_purposes' => ['photo_shoot', 'video_production', 'workshop', 'training'],
                'amenities_included' => ['WiFi', 'Sound System', 'Mirrors', 'Basic Lighting'],
                'setup_time_minutes' => 30,
                'cleanup_time_minutes' => 30,
                'requires_waiver' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Yoga Studio',
                'description' => 'Peaceful studio space with natural light, mirrors, and yoga props available.',
                'rentable_type' => 'room',
                'room_id' => $roomIds['Yoga Studio'] ?? null,
                'location_id' => $location->id,
                'hourly_rates' => ['USD' => 50.00],
                'deposit_rates' => ['USD' => 100.00],
                'minimum_hours' => 1,
                'maximum_hours' => 4,
                'allowed_purposes' => ['photo_shoot', 'workshop', 'training'],
                'amenities_included' => ['WiFi', 'Yoga Props', 'Mirrors', 'Sound System'],
                'setup_time_minutes' => 15,
                'cleanup_time_minutes' => 15,
                'requires_waiver' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Private Training Room',
                'description' => 'Intimate space for small group sessions or private filming.',
                'rentable_type' => 'room',
                'room_id' => $roomIds['Private Training'] ?? null,
                'location_id' => $location->id,
                'hourly_rates' => ['USD' => 40.00],
                'deposit_rates' => ['USD' => 75.00],
                'minimum_hours' => 1,
                'maximum_hours' => 6,
                'allowed_purposes' => ['photo_shoot', 'video_production', 'training', 'other'],
                'amenities_included' => ['WiFi', 'Basic Equipment'],
                'setup_time_minutes' => 15,
                'cleanup_time_minutes' => 15,
                'requires_waiver' => false,
                'is_active' => true,
            ],
        ];

        $createdSpaceConfigs = [];
        foreach ($spaceRentalConfigs as $configData) {
            $config = SpaceRentalConfig::updateOrCreate(
                ['host_id' => $host->id, 'name' => $configData['name']],
                array_merge($configData, ['host_id' => $host->id])
            );
            $createdSpaceConfigs[] = $config;
        }

        $this->command->info("Space rental configs created: " . count($createdSpaceConfigs));

        // 15. Get created clients for bookings
        $createdClients = Client::where('host_id', $host->id)->get();

        // 16. Create Class Bookings (schedule attendance)
        $classSessions = ClassSession::where('host_id', $host->id)
            ->where('start_time', '>=', Carbon::today())
            ->where('start_time', '<=', Carbon::today()->addDays(7))
            ->get();

        $bookingCount = 0;
        foreach ($classSessions as $session) {
            // Add 2-5 random clients to each upcoming session
            $clientsForSession = $createdClients->random(min(rand(2, 5), $createdClients->count()));
            foreach ($clientsForSession as $client) {
                Booking::updateOrCreate(
                    [
                        'host_id' => $host->id,
                        'client_id' => $client->id,
                        'bookable_type' => ClassSession::class,
                        'bookable_id' => $session->id,
                    ],
                    [
                        'host_id' => $host->id,
                        'client_id' => $client->id,
                        'bookable_type' => ClassSession::class,
                        'bookable_id' => $session->id,
                        'status' => Booking::STATUS_CONFIRMED,
                        'booking_source' => Booking::SOURCE_ONLINE,
                        'payment_method' => collect([Booking::PAYMENT_STRIPE, Booking::PAYMENT_CASH, Booking::PAYMENT_MEMBERSHIP])->random(),
                        'price_paid' => $session->classPlan->default_price ?? 0,
                        'booked_at' => now()->subDays(rand(1, 5)),
                    ]
                );
                $bookingCount++;
            }
        }

        $this->command->info("Class bookings created: {$bookingCount}");

        // 17. Create Service Slots and Bookings
        $servicePlansCreated = ServicePlan::where('host_id', $host->id)->get();
        $serviceSlotCount = 0;
        $serviceBookingCount = 0;

        foreach ($servicePlansCreated as $index => $servicePlan) {
            // Create slots for next 7 days
            for ($day = 0; $day < 7; $day++) {
                $date = Carbon::today()->addDays($day);

                // Skip weekends for some services
                if ($date->isWeekend() && $index % 2 === 0) {
                    continue;
                }

                // Create 2-3 slots per day
                $slotTimes = [
                    ['start' => '09:00', 'end' => '10:00'],
                    ['start' => '11:00', 'end' => '12:00'],
                    ['start' => '14:00', 'end' => '15:00'],
                    ['start' => '16:00', 'end' => '17:00'],
                ];

                $slotsToCreate = array_slice($slotTimes, 0, rand(2, 3));

                foreach ($slotsToCreate as $time) {
                    $startTime = $date->copy()->setTimeFromTimeString($time['start']);
                    $endTime = $date->copy()->setTimeFromTimeString($time['end']);

                    // Randomly book some slots
                    $isBooked = rand(0, 100) < 40; // 40% chance of being booked

                    $slot = ServiceSlot::updateOrCreate(
                        [
                            'host_id' => $host->id,
                            'service_plan_id' => $servicePlan->id,
                            'start_time' => $startTime,
                        ],
                        [
                            'host_id' => $host->id,
                            'service_plan_id' => $servicePlan->id,
                            'instructor_id' => $createdInstructors[array_rand($createdInstructors)]->id,
                            'location_id' => $location->id,
                            'room_id' => $roomIds['Private Training'] ?? null,
                            'start_time' => $startTime,
                            'end_time' => $endTime,
                            'status' => $isBooked ? ServiceSlot::STATUS_BOOKED : ServiceSlot::STATUS_AVAILABLE,
                            'price' => $servicePlan->price,
                        ]
                    );
                    $serviceSlotCount++;

                    // Create booking for booked slots
                    if ($isBooked) {
                        $client = $createdClients->random();
                        Booking::updateOrCreate(
                            [
                                'host_id' => $host->id,
                                'bookable_type' => ServiceSlot::class,
                                'bookable_id' => $slot->id,
                            ],
                            [
                                'host_id' => $host->id,
                                'client_id' => $client->id,
                                'bookable_type' => ServiceSlot::class,
                                'bookable_id' => $slot->id,
                                'status' => Booking::STATUS_CONFIRMED,
                                'booking_source' => Booking::SOURCE_ONLINE,
                                'payment_method' => collect([Booking::PAYMENT_STRIPE, Booking::PAYMENT_CASH])->random(),
                                'price_paid' => $servicePlan->price ?? 0,
                                'booked_at' => now()->subDays(rand(1, 3)),
                            ]
                        );
                        $serviceBookingCount++;
                    }
                }
            }
        }

        $this->command->info("Service slots created: {$serviceSlotCount}");
        $this->command->info("Service bookings created: {$serviceBookingCount}");

        // 18. Create Space Rentals
        $spaceRentalCount = 0;
        foreach ($createdSpaceConfigs as $config) {
            // Create 2-4 rentals per space config over next 2 weeks
            $numRentals = rand(2, 4);
            for ($i = 0; $i < $numRentals; $i++) {
                $date = Carbon::today()->addDays(rand(1, 14));
                $startHour = rand(9, 14);
                $duration = rand($config->minimum_hours, min($config->maximum_hours ?? 4, 4));

                $startTime = $date->copy()->setTime($startHour, 0);
                $endTime = $startTime->copy()->addHours($duration);

                $client = $createdClients->random();
                $hourlyRate = $config->hourly_rates['USD'] ?? 50;
                $depositAmount = $config->deposit_rates['USD'] ?? 0;
                $subtotal = $hourlyRate * $duration;

                SpaceRental::updateOrCreate(
                    [
                        'host_id' => $host->id,
                        'space_rental_config_id' => $config->id,
                        'start_time' => $startTime,
                    ],
                    [
                        'host_id' => $host->id,
                        'space_rental_config_id' => $config->id,
                        'client_id' => $client->id,
                        'purpose' => collect(['photo_shoot', 'video_production', 'workshop', 'training'])->random(),
                        'purpose_notes' => 'Seeded rental for testing',
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'hourly_rate' => $hourlyRate,
                        'hours_booked' => $duration,
                        'subtotal' => $subtotal,
                        'tax_amount' => 0,
                        'total_amount' => $subtotal,
                        'deposit_amount' => $depositAmount,
                        'currency' => 'USD',
                        'status' => collect([SpaceRental::STATUS_PENDING, SpaceRental::STATUS_CONFIRMED])->random(),
                        'deposit_status' => $depositAmount > 0 ? SpaceRental::DEPOSIT_PENDING : SpaceRental::DEPOSIT_NOT_REQUIRED,
                        'created_by_user_id' => $owner->id,
                    ]
                );
                $spaceRentalCount++;
            }
        }

        $this->command->info("Space rentals created: {$spaceRentalCount}");

        // 19. Create Item Rental Bookings
        $rentalItemsCreated = RentalItem::where('host_id', $host->id)->get();
        $itemRentalCount = 0;

        foreach ($rentalItemsCreated as $item) {
            // Create 1-3 rental bookings per item
            $numRentals = rand(1, 3);
            for ($i = 0; $i < $numRentals; $i++) {
                $rentalDate = Carbon::today()->addDays(rand(0, 7));
                $dueDate = $rentalDate->copy()->addDays($item->max_rental_days ?? 1);
                $quantity = rand(1, min(2, $item->available_inventory));
                $unitPrice = $item->prices['USD'] ?? 5;

                $client = $createdClients->random();

                RentalBooking::updateOrCreate(
                    [
                        'host_id' => $host->id,
                        'rental_item_id' => $item->id,
                        'client_id' => $client->id,
                        'rental_date' => $rentalDate,
                    ],
                    [
                        'host_id' => $host->id,
                        'rental_item_id' => $item->id,
                        'client_id' => $client->id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $unitPrice * $quantity,
                        'deposit_amount' => $item->deposit_amount ?? 0,
                        'currency' => 'USD',
                        'rental_date' => $rentalDate,
                        'due_date' => $dueDate,
                        'fulfillment_status' => collect([
                            RentalBooking::STATUS_PENDING,
                            RentalBooking::STATUS_PREPARED,
                            RentalBooking::STATUS_HANDED_OUT,
                        ])->random(),
                    ]
                );
                $itemRentalCount++;
            }
        }

        $this->command->info("Item rental bookings created: {$itemRentalCount}");

        // 21. Enable Features for Host
        $featureSlugs = [
            'online-1on1-meeting',
            'progress-templates',
        ];

        foreach ($featureSlugs as $slug) {
            $feature = Feature::where('slug', $slug)->first();
            if ($feature) {
                HostFeature::updateOrCreate(
                    ['host_id' => $host->id, 'feature_id' => $feature->id],
                    ['is_enabled' => true, 'config' => $feature->default_config]
                );
            }
        }

        $this->command->info("Features enabled");

        // 22. Create Booking Profiles for 1:1 Meetings
        $instructorsForBooking = [$ownerInstructor, $createdInstructors[0], $createdInstructors[1]];

        foreach ($instructorsForBooking as $instructor) {
            BookingProfile::updateOrCreate(
                ['host_id' => $host->id, 'instructor_id' => $instructor->id],
                [
                    'host_id' => $host->id,
                    'instructor_id' => $instructor->id,
                    'is_enabled' => true,
                    'is_setup_complete' => true,
                    'display_name' => $instructor->name,
                    'bio' => $instructor->bio,
                    'meeting_types' => ['in_person', 'video'],
                    'allowed_durations' => [30, 60],
                    'default_duration' => 30,
                    'buffer_before' => 10,
                    'buffer_after' => 10,
                    'min_notice_hours' => 24,
                    'max_advance_days' => 60,
                    'working_days' => [1, 2, 3, 4, 5],
                    'default_start_time' => '09:00:00',
                    'default_end_time' => '17:00:00',
                    'allow_reschedule' => true,
                    'reschedule_cutoff_hours' => 24,
                    'allow_cancel' => true,
                    'cancel_cutoff_hours' => 24,
                    'setup_completed_at' => now(),
                ]
            );
        }

        $this->command->info("Booking profiles created: " . count($instructorsForBooking));

        $this->command->newLine();
        $this->command->info("===========================================");
        $this->command->info("Studio setup complete!");
        $this->command->info("===========================================");
        $this->command->info("Login: faizanhumayun486@gmail.com");
        $this->command->info("Password: password123");
        $this->command->info("Studio URL: http://crossfit.projectfit.local:8888");
        $this->command->info("===========================================");
    }
}
