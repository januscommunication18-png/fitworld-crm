<?php

namespace Database\Seeders;

use App\Models\ClassPlan;
use App\Models\ClassRequest;
use App\Models\ClassSession;
use App\Models\Host;
use App\Models\Instructor;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ClassSessionSeeder extends Seeder
{
    public function run(): void
    {
        $hosts = Host::all();

        foreach ($hosts as $host) {
            $classPlans = $host->classPlans()->active()->get();
            $instructors = $host->instructors()->active()->get();
            $locations = $host->locations()->with('rooms')->get();

            if ($classPlans->isEmpty() || $instructors->isEmpty()) {
                continue;
            }

            // Create sessions for the next 2 weeks
            $startDate = now()->startOfWeek();
            $endDate = now()->addWeeks(2)->endOfWeek();

            foreach ($classPlans as $classPlan) {
                // Create 3-5 sessions per class plan
                $sessionCount = rand(3, 5);

                for ($i = 0; $i < $sessionCount; $i++) {
                    $primaryInstructor = $instructors->random();
                    $backupInstructor = $instructors->count() > 1
                        ? $instructors->where('id', '!=', $primaryInstructor->id)->random()
                        : null;

                    $location = $locations->isNotEmpty() ? $locations->random() : null;
                    $room = $location && $location->rooms->isNotEmpty()
                        ? $location->rooms->random()
                        : null;

                    // Random date within the next 2 weeks
                    $dayOffset = rand(0, 13);
                    $sessionDate = $startDate->copy()->addDays($dayOffset);

                    // Random hour between 6am and 8pm
                    $hour = rand(6, 20);
                    $startTime = $sessionDate->copy()->setHour($hour)->setMinute(0)->setSecond(0);
                    $endTime = $startTime->copy()->addMinutes($classPlan->default_duration_minutes);

                    // Random status
                    $statusRand = rand(1, 10);
                    $status = match (true) {
                        $statusRand <= 6 => ClassSession::STATUS_PUBLISHED,
                        $statusRand <= 8 => ClassSession::STATUS_DRAFT,
                        default => ClassSession::STATUS_CANCELLED,
                    };

                    $session = ClassSession::create([
                        'host_id' => $host->id,
                        'class_plan_id' => $classPlan->id,
                        'primary_instructor_id' => $primaryInstructor->id,
                        'backup_instructor_id' => rand(1, 3) === 1 ? $backupInstructor?->id : null,
                        'location_id' => $location?->id,
                        'room_id' => $room?->id,
                        'title' => null, // Use class plan name
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'duration_minutes' => $classPlan->default_duration_minutes,
                        'capacity' => $room?->capacity ?? $classPlan->default_capacity ?? rand(10, 25),
                        'price' => rand(1, 5) === 1 ? rand(15, 35) : null, // Occasional price override
                        'status' => $status,
                        'cancelled_at' => $status === ClassSession::STATUS_CANCELLED ? now() : null,
                        'cancellation_reason' => $status === ClassSession::STATUS_CANCELLED ? 'Instructor unavailable' : null,
                        'notes' => rand(1, 4) === 1 ? 'Sample internal note for this session.' : null,
                    ]);
                }
            }

            // Create some sample class requests
            $this->createSampleRequests($host, $classPlans);
        }
    }

    protected function createSampleRequests(Host $host, $classPlans): void
    {
        $names = ['Sarah Johnson', 'Mike Chen', 'Emma Wilson', 'David Park', 'Lisa Brown'];
        $domains = ['gmail.com', 'yahoo.com', 'outlook.com'];

        $days = ClassRequest::getDayOptions();
        $times = ClassRequest::getTimeOptions();

        // Create 3-5 requests per host
        $requestCount = rand(3, 5);

        for ($i = 0; $i < $requestCount; $i++) {
            $name = $names[array_rand($names)];
            $email = strtolower(str_replace(' ', '.', $name)) . rand(1, 99) . '@' . $domains[array_rand($domains)];
            $classPlan = $classPlans->random();

            // Random status
            $statusRand = rand(1, 10);
            $status = match (true) {
                $statusRand <= 6 => ClassRequest::STATUS_PENDING,
                $statusRand <= 8 => ClassRequest::STATUS_SCHEDULED,
                default => ClassRequest::STATUS_IGNORED,
            };

            // Random preferred days (1-3 days)
            $preferredDays = collect($days)->random(rand(1, 3))->values()->toArray();

            // Random preferred times (1-2 times)
            $preferredTimes = collect($times)->random(rand(1, 2))->values()->toArray();

            ClassRequest::create([
                'host_id' => $host->id,
                'class_plan_id' => $classPlan->id,
                'service_plan_id' => null,
                'requester_name' => $name,
                'requester_email' => $email,
                'preferred_days' => $preferredDays,
                'preferred_times' => $preferredTimes,
                'notes' => rand(1, 3) === 1 ? 'Looking forward to joining this class!' : null,
                'status' => $status,
                'scheduled_session_id' => null,
            ]);
        }
    }
}
