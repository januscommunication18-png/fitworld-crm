<?php

/*
|--------------------------------------------------------------------------
| Mock schedule seeder (run via tinker)
|--------------------------------------------------------------------------
| Populates the Schedule feed for ONE date with mock entries across all four
| tabs — a couple of class sessions, a service slot, a membership session and
| a space rental — each with mock client bookings/attendees.
|
| Run:
|   php artisan tinker --execute="require 'mock_schedule.php';"
|
| Change ONE date below and re-run for any date. Re-running for the same date
| first removes the previous mock rows (matched by the "[MOCK]" marker), so it
| never duplicates. Everything is host_id = 1.
*/

use App\Models\Booking;
use App\Models\ClassSession;
use App\Models\ServiceSlot;
use App\Models\SpaceRental;
use Illuminate\Support\Carbon;

$HOST = 1;
$DATE = '2026-06-11'; // ← change this to seed any other date

$at = fn (string $t) => Carbon::parse("$DATE $t");

// ── Clean up any previous mock rows for this date (idempotent re-runs) ──
$oldCs = ClassSession::where('host_id', $HOST)->where('title', 'like', '[MOCK]%')
    ->whereDate('start_time', $DATE)->pluck('id');
Booking::where('bookable_type', ClassSession::class)->whereIn('bookable_id', $oldCs)->delete();
ClassSession::whereIn('id', $oldCs)->delete();

$oldSs = ServiceSlot::where('host_id', $HOST)->where('title', 'like', '[MOCK]%')
    ->whereDate('start_time', $DATE)->pluck('id');
Booking::where('bookable_type', ServiceSlot::class)->whereIn('bookable_id', $oldSs)->delete();
ServiceSlot::whereIn('id', $oldSs)->delete();

SpaceRental::where('host_id', $HOST)->where('purpose_notes', 'like', '[MOCK]%')
    ->whereDate('start_time', $DATE)->forceDelete();

// ── Helper: attach a client booking to a session/slot ──
$book = function (int $clientId, $bookable, string $status, string $intake, string $method, ?float $price, bool $checkedIn = false) use ($HOST) {
    return Booking::create([
        'host_id' => $HOST,
        'client_id' => $clientId,
        'bookable_type' => get_class($bookable),
        'bookable_id' => $bookable->id,
        'status' => $status,
        'intake_status' => $intake,
        'payment_method' => $method,
        'price_paid' => $price,
        'booking_source' => 'internal_walkin',
        'booked_at' => now(),
        'checked_in_at' => $checkedIn ? now() : null,
    ]);
};

$created = [];

// ── 1. Class session — Morning Yoga (9:00) with 3 attendees ──
$cs1 = ClassSession::create([
    'host_id' => $HOST, 'class_plan_id' => 1, 'primary_instructor_id' => 1,
    'location_id' => 1, 'room_id' => 2,
    'title' => '[MOCK] Morning Yoga Flow',
    'start_time' => $at('09:00'), 'end_time' => $at('10:00'),
    'duration_minutes' => 60, 'capacity' => 20, 'price' => 20,
    'status' => ClassSession::STATUS_PUBLISHED,
]);
$book(1, $cs1, 'confirmed', 'completed', 'stripe', 20, true);
$book(2, $cs1, 'confirmed', 'pending', 'cash', 20, false);
$book(3, $cs1, 'confirmed', 'completed', 'membership', 0, true);
$created[] = "Class #{$cs1->id} (3 attendees)";

// ── 2. Class session — CrossFit (17:30) with 2 attendees ──
$cs2 = ClassSession::create([
    'host_id' => $HOST, 'class_plan_id' => 5, 'primary_instructor_id' => 2,
    'location_id' => 1, 'room_id' => 1,
    'title' => '[MOCK] CrossFit WOD',
    'start_time' => $at('17:30'), 'end_time' => $at('18:30'),
    'duration_minutes' => 60, 'capacity' => 15, 'price' => 25,
    'status' => ClassSession::STATUS_PUBLISHED,
]);
$book(4, $cs2, 'confirmed', 'completed', 'pack', 25, false);
$book(5, $cs2, 'cancelled', 'not_required', 'stripe', 25, false);
$created[] = "Class #{$cs2->id} (2 attendees)";

// ── 3. Service slot — Fitness Assessment (11:00) with 1 booking ──
$ss = ServiceSlot::create([
    'host_id' => $HOST, 'service_plan_id' => 3, 'instructor_id' => 3,
    'location_id' => 1,
    'title' => '[MOCK] Fitness Assessment',
    'start_time' => $at('11:00'), 'end_time' => $at('11:45'),
    'status' => ServiceSlot::STATUS_BOOKED, 'price' => 40,
]);
$book(6, $ss, 'confirmed', 'completed', 'cash', 40, true);
$created[] = "Service #{$ss->id} (1 booking)";

// ── 4. Membership session — class plan NULL + linked plan (14:00) with 2 ──
$ms = ClassSession::create([
    'host_id' => $HOST, 'class_plan_id' => null, 'primary_instructor_id' => 1,
    'location_id' => 1, 'room_id' => 2,
    'title' => '[MOCK] Members Open Flow',
    'start_time' => $at('14:00'), 'end_time' => $at('15:00'),
    'duration_minutes' => 60, 'capacity' => 10,
    'status' => ClassSession::STATUS_PUBLISHED,
]);
$ms->membershipPlans()->attach(1);
$book(2, $ms, 'confirmed', 'not_required', 'membership', 0, true);
$book(4, $ms, 'confirmed', 'not_required', 'membership', 0, false);
$created[] = "Membership #{$ms->id} (2 attendees)";

// ── 5. Space rental — Workshop (19:00) for a client ──
$sr = SpaceRental::create([
    'host_id' => $HOST, 'space_rental_config_id' => 1, 'client_id' => 5,
    'purpose' => 'workshop', 'purpose_notes' => '[MOCK] Weekend workshop',
    'start_time' => $at('19:00'), 'end_time' => $at('21:00'),
    'hourly_rate' => 50, 'hours_booked' => 2,
    'subtotal' => 100, 'tax_amount' => 8, 'total_amount' => 108,
    'deposit_amount' => 50, 'deposit_status' => SpaceRental::DEPOSIT_PENDING,
    'currency' => 'USD', 'status' => SpaceRental::STATUS_CONFIRMED,
]);
$created[] = "Rental #{$sr->id} ({$sr->reference_number})";

echo "Seeded mock schedule for {$DATE}:\n - ".implode("\n - ", $created)."\n";