<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\ClassPlan;
use App\Models\ClassSession;
use App\Models\Client;
use App\Models\CustomerMembership;
use App\Models\Host;
use App\Models\MembershipPlan;
use App\Services\ScheduledMembershipService;
use Illuminate\Console\Command;

class ScheduledMembershipCommand extends Command
{
    protected $signature = 'membership:scheduled
                            {action : Action to perform (enroll-session, enroll-member, seed-test-data, status)}
                            {--session= : Class session ID for enroll-session action}
                            {--membership= : Customer membership ID for enroll-member action}
                            {--host= : Host ID for seed-test-data action}';

    protected $description = 'Manage scheduled membership auto-enrollments';

    protected ScheduledMembershipService $scheduledMembershipService;

    public function __construct(ScheduledMembershipService $scheduledMembershipService)
    {
        parent::__construct();
        $this->scheduledMembershipService = $scheduledMembershipService;
    }

    public function handle(): int
    {
        $action = $this->argument('action');

        return match ($action) {
            'enroll-session' => $this->enrollSession(),
            'enroll-member' => $this->enrollMember(),
            'seed-test-data' => $this->seedTestData(),
            'status' => $this->showStatus(),
            default => $this->error("Unknown action: {$action}") ?? 1,
        };
    }

    /**
     * Enroll all scheduled membership holders into a specific session
     */
    protected function enrollSession(): int
    {
        $sessionId = $this->option('session');

        if (!$sessionId) {
            // Show available sessions to choose from
            $sessions = ClassSession::published()
                ->upcoming()
                ->with('classPlan')
                ->orderBy('start_time')
                ->limit(20)
                ->get();

            if ($sessions->isEmpty()) {
                $this->error('No upcoming published sessions found.');
                return 1;
            }

            $this->info('Available upcoming sessions:');
            $this->table(
                ['ID', 'Class', 'Date/Time', 'Bookings'],
                $sessions->map(fn($s) => [
                    $s->id,
                    $s->classPlan?->name ?? 'N/A',
                    $s->start_time->format('M j, Y g:i A'),
                    $s->bookings()->confirmed()->count() . '/' . $s->getEffectiveCapacity(),
                ])
            );

            $sessionId = $this->ask('Enter session ID to enroll members');
        }

        $session = ClassSession::find($sessionId);
        if (!$session) {
            $this->error("Session not found: {$sessionId}");
            return 1;
        }

        $this->info("Enrolling scheduled membership holders into: {$session->display_title}");
        $this->info("Session time: {$session->start_time->format('M j, Y g:i A')}");

        $results = $this->scheduledMembershipService->enrollScheduledMembersIntoSession($session);

        $this->displayResults($results);

        return 0;
    }

    /**
     * Enroll a specific member into all upcoming scheduled sessions
     */
    protected function enrollMember(): int
    {
        $membershipId = $this->option('membership');

        if (!$membershipId) {
            // Show available memberships with scheduled classes
            $memberships = CustomerMembership::active()
                ->notExpired()
                ->whereHas('membershipPlan', fn($q) => $q->where('has_scheduled_class', true))
                ->with(['client', 'membershipPlan'])
                ->limit(20)
                ->get();

            if ($memberships->isEmpty()) {
                $this->error('No active scheduled memberships found.');
                return 1;
            }

            $this->info('Active scheduled memberships:');
            $this->table(
                ['ID', 'Client', 'Plan', 'Status'],
                $memberships->map(fn($m) => [
                    $m->id,
                    $m->client?->full_name ?? 'N/A',
                    $m->membershipPlan?->name ?? 'N/A',
                    $m->status,
                ])
            );

            $membershipId = $this->ask('Enter membership ID to enroll');
        }

        $membership = CustomerMembership::with(['client', 'membershipPlan'])->find($membershipId);
        if (!$membership) {
            $this->error("Membership not found: {$membershipId}");
            return 1;
        }

        $this->info("Enrolling {$membership->client->full_name} into scheduled classes");
        $this->info("Membership plan: {$membership->membershipPlan->name}");

        $results = $this->scheduledMembershipService->enrollMemberIntoScheduledClasses($membership);

        $this->displayResults($results);

        return 0;
    }

    /**
     * Seed test data for scheduled membership testing
     */
    protected function seedTestData(): int
    {
        $hostId = $this->option('host');

        if (!$hostId) {
            $hosts = Host::limit(10)->get();
            $this->table(['ID', 'Name', 'Subdomain'], $hosts->map(fn($h) => [$h->id, $h->name, $h->subdomain]));
            $hostId = $this->ask('Enter host ID to seed test data for');
        }

        $host = Host::find($hostId);
        if (!$host) {
            $this->error("Host not found: {$hostId}");
            return 1;
        }

        $this->info("Seeding test data for host: {$host->name}");

        // 1. Get or create a class plan
        $classPlan = $host->classPlans()->first();
        if (!$classPlan) {
            $this->error('No class plans found for this host. Please create a class plan first.');
            return 1;
        }
        $this->info("Using class plan: {$classPlan->name}");

        // 2. Get or create a membership plan with scheduled classes
        $membershipPlan = $host->membershipPlans()
            ->where('has_scheduled_class', true)
            ->first();

        if (!$membershipPlan) {
            $membershipPlan = $host->membershipPlans()->create([
                'name' => 'Test Scheduled Membership',
                'slug' => 'test-scheduled-membership',
                'description' => 'Test membership with scheduled classes for auto-enrollment testing',
                'type' => MembershipPlan::TYPE_UNLIMITED,
                'has_scheduled_class' => true,
                'price' => 99.00,
                'prices' => ['USD' => 99.00],
                'interval' => MembershipPlan::INTERVAL_MONTHLY,
                'eligibility_scope' => MembershipPlan::ELIGIBILITY_SELECTED,
                'location_scope_type' => MembershipPlan::LOCATION_ALL,
                'visibility_public' => true,
                'status' => MembershipPlan::STATUS_ACTIVE,
                'sort_order' => 999,
            ]);
            $this->info("Created membership plan: {$membershipPlan->name}");
        } else {
            $this->info("Using existing membership plan: {$membershipPlan->name}");
        }

        // 3. Link class plan to membership plan
        if (!$membershipPlan->classPlans()->where('class_plans.id', $classPlan->id)->exists()) {
            $membershipPlan->classPlans()->attach($classPlan->id);
            $this->info("Linked class plan '{$classPlan->name}' to membership plan");
        }

        // 4. Create test clients with memberships
        $testClients = [];
        for ($i = 1; $i <= 3; $i++) {
            $email = "scheduled-test-{$i}@example.com";
            $client = Client::where('host_id', $host->id)->where('email', $email)->first();

            if (!$client) {
                $client = Client::create([
                    'host_id' => $host->id,
                    'first_name' => "Scheduled",
                    'last_name' => "Tester {$i}",
                    'email' => $email,
                    'phone' => "555-000-000{$i}",
                    'status' => Client::STATUS_MEMBER,
                    'membership_status' => Client::MEMBERSHIP_ACTIVE,
                ]);
                $this->info("Created test client: {$client->full_name}");
            }

            // Create membership for client if not exists
            $existingMembership = CustomerMembership::where('client_id', $client->id)
                ->where('membership_plan_id', $membershipPlan->id)
                ->active()
                ->first();

            if (!$existingMembership) {
                $membership = CustomerMembership::create([
                    'host_id' => $host->id,
                    'client_id' => $client->id,
                    'membership_plan_id' => $membershipPlan->id,
                    'status' => CustomerMembership::STATUS_ACTIVE,
                    'payment_method' => CustomerMembership::PAYMENT_MANUAL,
                    'current_period_start' => now(),
                    'current_period_end' => now()->addMonth(),
                    'started_at' => now(),
                ]);
                $this->info("Created membership for: {$client->full_name}");
            } else {
                $this->info("Membership already exists for: {$client->full_name}");
            }

            $testClients[] = $client;
        }

        // 5. Create upcoming class sessions (draft)
        $instructor = $host->instructors()->first();
        $location = $host->locations()->first();

        $sessionsCreated = 0;
        for ($day = 1; $day <= 5; $day++) {
            $startTime = now()->addDays($day)->setTime(9, 0, 0);

            // Check if session already exists at this time
            $existingSession = ClassSession::where('host_id', $host->id)
                ->where('class_plan_id', $classPlan->id)
                ->where('start_time', $startTime)
                ->first();

            if (!$existingSession) {
                ClassSession::create([
                    'host_id' => $host->id,
                    'class_plan_id' => $classPlan->id,
                    'primary_instructor_id' => $instructor?->id,
                    'location_id' => $location?->id,
                    'title' => "{$classPlan->name} - Test Session",
                    'start_time' => $startTime,
                    'end_time' => $startTime->copy()->addHour(),
                    'duration_minutes' => 60,
                    'capacity' => 10,
                    'status' => ClassSession::STATUS_DRAFT, // Draft so you can test publishing
                ]);
                $sessionsCreated++;
            }
        }
        $this->info("Created {$sessionsCreated} draft class sessions for the next 5 days");

        // Summary
        $this->newLine();
        $this->info('=== Test Data Summary ===');
        $this->table(
            ['Item', 'Value'],
            [
                ['Host', $host->name],
                ['Class Plan', $classPlan->name],
                ['Membership Plan', $membershipPlan->name . ' (ID: ' . $membershipPlan->id . ')'],
                ['Test Clients', count($testClients)],
                ['Draft Sessions', $sessionsCreated],
            ]
        );

        $this->newLine();
        $this->info('=== Next Steps ===');
        $this->line('1. Go to /schedule and publish one of the draft sessions');
        $this->line('2. Check that test clients are auto-enrolled');
        $this->line('3. Or run: php artisan membership:scheduled enroll-session --session=<ID>');

        return 0;
    }

    /**
     * Show status of scheduled memberships
     */
    protected function showStatus(): int
    {
        $this->info('=== Scheduled Membership Status ===');
        $this->newLine();

        // Membership plans with scheduled classes
        $plans = MembershipPlan::where('has_scheduled_class', true)
            ->where('status', MembershipPlan::STATUS_ACTIVE)
            ->withCount('classPlans')
            ->get();

        $this->info('Membership Plans with Scheduled Classes:');
        if ($plans->isEmpty()) {
            $this->warn('  No membership plans configured with scheduled classes.');
        } else {
            $this->table(
                ['ID', 'Name', 'Host', 'Linked Classes'],
                $plans->map(fn($p) => [
                    $p->id,
                    $p->name,
                    $p->host?->name ?? 'N/A',
                    $p->class_plans_count,
                ])
            );
        }

        $this->newLine();

        // Active memberships with scheduled classes
        $memberships = CustomerMembership::active()
            ->notExpired()
            ->whereHas('membershipPlan', fn($q) => $q->where('has_scheduled_class', true))
            ->with(['client', 'membershipPlan'])
            ->get();

        $this->info('Active Scheduled Memberships:');
        if ($memberships->isEmpty()) {
            $this->warn('  No active scheduled memberships found.');
        } else {
            $this->table(
                ['ID', 'Client', 'Plan', 'Expires'],
                $memberships->map(fn($m) => [
                    $m->id,
                    $m->client?->full_name ?? 'N/A',
                    $m->membershipPlan?->name ?? 'N/A',
                    $m->current_period_end?->format('M j, Y') ?? 'N/A',
                ])
            );
        }

        $this->newLine();

        // Upcoming sessions for scheduled classes
        $classPlanIds = $plans->flatMap(fn($p) => $p->classPlans()->pluck('class_plans.id'))->unique();

        $sessions = ClassSession::whereIn('class_plan_id', $classPlanIds)
            ->published()
            ->upcoming()
            ->withCount(['bookings' => fn($q) => $q->confirmed()])
            ->orderBy('start_time')
            ->limit(10)
            ->get();

        $this->info('Upcoming Published Sessions (for scheduled classes):');
        if ($sessions->isEmpty()) {
            $this->warn('  No upcoming published sessions found.');
        } else {
            $this->table(
                ['ID', 'Class', 'Date/Time', 'Bookings/Capacity'],
                $sessions->map(fn($s) => [
                    $s->id,
                    $s->classPlan?->name ?? 'N/A',
                    $s->start_time->format('M j, Y g:i A'),
                    $s->bookings_count . '/' . $s->getEffectiveCapacity(),
                ])
            );
        }

        return 0;
    }

    /**
     * Display enrollment results
     */
    protected function displayResults(array $results): void
    {
        $this->newLine();

        if (!empty($results['enrolled'])) {
            $this->info('Enrolled (' . count($results['enrolled']) . '):');
            $this->table(
                ['Client', 'Session', 'Time'],
                collect($results['enrolled'])->map(fn($r) => [
                    $r['client_name'],
                    $r['session_title'],
                    $r['session_time'] ?? 'N/A',
                ])
            );
        }

        if (!empty($results['skipped'])) {
            $this->warn('Skipped (' . count($results['skipped']) . '):');
            $this->table(
                ['Client', 'Session', 'Reason'],
                collect($results['skipped'])->map(fn($r) => [
                    $r['client_name'],
                    $r['session_title'],
                    $r['reason'],
                ])
            );
        }

        if (!empty($results['errors'])) {
            $this->error('Errors (' . count($results['errors']) . '):');
            $this->table(
                ['Client', 'Session', 'Error'],
                collect($results['errors'])->map(fn($r) => [
                    $r['client_name'],
                    $r['session_title'],
                    $r['reason'],
                ])
            );
        }

        if (empty($results['enrolled']) && empty($results['skipped']) && empty($results['errors'])) {
            $this->info('No members to enroll.');
        }
    }
}
