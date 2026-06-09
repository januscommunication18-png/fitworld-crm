<?php

/*
|--------------------------------------------------------------------------
| Mock schedule seeder — 100 bookings for ONE date (run via tinker)
|--------------------------------------------------------------------------
| Populates the Schedule feed for a single date with ~100 client bookings
| spread across every item type the calendar shows:
|   - regular class sessions (class plans)
|   - membership sessions (class_plan_id NULL + linked membership plan)
|   - 1-on-1 service slots
|   - space rentals (not Booking rows, shown on the calendar too)
|
| Run for the default host (1):
|   php artisan tinker --execute="require 'mock_schedule_100.php';"
|
| Run for another configured host (e.g. 26 = wloie@gmail.com):
|   php artisan tinker --execute="\$SEED_HOST=26; require 'mock_schedule_100.php';"
|
| The calendar renders events for the LOGGED-IN user's primary host
| (\$authUser->host), so seed the host whose account you actually view with.
|
| Idempotent: re-running first removes the previous mock rows for this date
| (matched by the "[MOCK]" marker), so it never duplicates.
*/

use App\Models\Booking;
use App\Models\ClassSession;
use App\Models\ServiceSlot;
use App\Models\SpaceRental;
use Illuminate\Support\Carbon;

$DATE = '2026-06-11'; // ← change this to seed any other date

// ── Per-host reference data (ids that exist for each studio) ──
$CONFIG = [
    1 => [
        'clients'       => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 15],
        'class_plans'   => [1, 2, 3, 4, 5, 6, 12],
        'memb_plans'    => [1, 2, 3, 4],
        'service_plans' => [1, 2, 3, 4, 5, 6],
        'instructors'   => [1, 2, 3, 4, 5, 18, 19, 20],
        'rooms'         => [[1, 1], [1, 2], [1, 3], [1, 4]], // [location_id, room_id]
        'rental_config' => 1,
    ],
    26 => [
        'clients'       => [40, 51, 56, 57, 58, 39, 41, 42, 43, 46, 47, 48, 49, 50],
        'class_plans'   => [11],
        'memb_plans'    => [6, 7, 8],
        'service_plans' => [9],
        'instructors'   => [21, 22, 23, 24],
        'rooms'         => [[17, 21], [17, 22], [19, 23]], // [location_id, room_id]
        'rental_config' => null, // skip rentals if not configured
    ],
];

$HOST = isset($SEED_HOST) ? (int) $SEED_HOST : 1;
if (! isset($CONFIG[$HOST])) {
    echo "No reference-data config for host {$HOST}. Add one to \$CONFIG.\n";
    return;
}
$cfg = $CONFIG[$HOST];

$CLIENTS       = $cfg['clients'];
$CLASS_PLANS   = $cfg['class_plans'];
$MEMB_PLANS    = $cfg['memb_plans'];
$SERVICE_PLANS = $cfg['service_plans'];
$INSTRUCTORS   = $cfg['instructors'];
$ROOMS         = $cfg['rooms'];

$at = fn (string $t) => Carbon::parse("$DATE $t");

// ── Clean up any previous mock rows for this date (idempotent re-runs) ──
$oldCs = ClassSession::where('host_id', $HOST)->where('title', 'like', '[MOCK]%')
    ->whereDate('start_time', $DATE)->pluck('id');
Booking::where('bookable_type', ClassSession::class)->whereIn('bookable_id', $oldCs)->delete();
foreach (ClassSession::whereIn('id', $oldCs)->get() as $c) {
    $c->membershipPlans()->detach();
}
ClassSession::whereIn('id', $oldCs)->delete();

$oldSs = ServiceSlot::where('host_id', $HOST)->where('title', 'like', '[MOCK]%')
    ->whereDate('start_time', $DATE)->pluck('id');
Booking::where('bookable_type', ServiceSlot::class)->whereIn('bookable_id', $oldSs)->delete();
ServiceSlot::whereIn('id', $oldSs)->delete();

SpaceRental::where('host_id', $HOST)->where('purpose_notes', 'like', '[MOCK]%')
    ->whereDate('start_time', $DATE)->forceDelete();

// ── Rotating variety for realistic bookings ──
$statusCycle = [
    ['confirmed', 'completed',    'stripe',     true],
    ['confirmed', 'pending',      'cash',       false],
    ['confirmed', 'completed',    'membership', true],
    ['confirmed', 'not_required', 'pack',       false],
    ['confirmed', 'completed',    'comp',       true],
    ['no_show',   'not_required', 'stripe',     false],
    ['confirmed', 'pending',      'cash',       false],
    ['cancelled', 'not_required', 'stripe',     false],
];

$bookingCount = 0;
$TARGET = 100;
$si = 0; // status index

// pick N distinct clients starting at an offset (cycles through the pool)
$pick = function (int $offset, int $n) use ($CLIENTS) {
    $out = [];
    $c = count($CLIENTS);
    for ($i = 0; $i < $n; $i++) {
        $out[] = $CLIENTS[($offset + $i) % $c];
    }
    return array_values(array_unique($out));
};

$book = function (int $clientId, $bookable, float $basePrice) use ($HOST, &$si, $statusCycle, &$bookingCount) {
    [$status, $intake, $method, $checkedIn] = $statusCycle[$si % count($statusCycle)];
    $si++;
    $price = in_array($method, ['membership', 'comp']) ? 0 : $basePrice;
    Booking::create([
        'host_id'        => $HOST,
        'client_id'      => $clientId,
        'bookable_type'  => get_class($bookable),
        'bookable_id'    => $bookable->id,
        'status'         => $status,
        'intake_status'  => $intake,
        'payment_method' => $method,
        'price_paid'     => $price,
        'booking_source' => 'internal_walkin',
        'booked_at'      => now(),
        'checked_in_at'  => $checkedIn ? now() : null,
    ]);
    $bookingCount++;
};

$summary = ['class' => 0, 'membership' => 0, 'service' => 0, 'rental' => 0];

// ── 1. Regular class sessions: 10 sessions × ~6 attendees = ~60 bookings ──
$classTimes = ['07:00', '08:30', '09:00', '10:30', '12:00', '13:30', '16:00', '17:30', '18:30', '19:30'];
foreach ($classTimes as $idx => $time) {
    if ($bookingCount >= $TARGET) break;
    [$locId, $roomId] = $ROOMS[$idx % count($ROOMS)];
    $session = ClassSession::create([
        'host_id'              => $HOST,
        'class_plan_id'        => $CLASS_PLANS[$idx % count($CLASS_PLANS)],
        'primary_instructor_id' => $INSTRUCTORS[$idx % count($INSTRUCTORS)],
        'location_id'          => $locId,
        'room_id'              => $roomId,
        'title'                => '[MOCK] Class ' . ($idx + 1),
        'start_time'           => $at($time),
        'end_time'             => $at($time)->copy()->addHour(),
        'duration_minutes'     => 60,
        'capacity'             => 20,
        'price'                => 20 + ($idx % 3) * 5,
        'status'               => ClassSession::STATUS_PUBLISHED,
    ]);
    foreach ($pick($idx * 4, 6) as $clientId) {
        if ($bookingCount >= $TARGET) break;
        $book($clientId, $session, (float) $session->price);
        $summary['class']++;
    }
}

// ── 2. Membership sessions: 4 sessions × ~5 attendees = ~20 bookings ──
$membTimes = ['08:00', '11:00', '15:00', '18:00'];
foreach ($membTimes as $idx => $time) {
    if ($bookingCount >= $TARGET) break;
    [$locId, $roomId] = $ROOMS[($idx + 1) % count($ROOMS)];
    $session = ClassSession::create([
        'host_id'              => $HOST,
        'class_plan_id'        => null,
        'primary_instructor_id' => $INSTRUCTORS[($idx + 2) % count($INSTRUCTORS)],
        'location_id'          => $locId,
        'room_id'              => $roomId,
        'title'                => '[MOCK] Members Session ' . ($idx + 1),
        'start_time'           => $at($time),
        'end_time'             => $at($time)->copy()->addHour(),
        'duration_minutes'     => 60,
        'capacity'             => 15,
        'status'               => ClassSession::STATUS_PUBLISHED,
    ]);
    $session->membershipPlans()->attach($MEMB_PLANS[$idx % count($MEMB_PLANS)]);
    foreach ($pick($idx * 3 + 1, 5) as $clientId) {
        if ($bookingCount >= $TARGET) break;
        $book($clientId, $session, 0.0);
        $summary['membership']++;
    }
}

// ── 3. Service slots: 1 booking each, until we reach 100 total ──
$serviceStart = $at('07:30');
$slotIdx = 0;
while ($bookingCount < $TARGET) {
    [$locId] = $ROOMS[$slotIdx % count($ROOMS)];
    $start = $serviceStart->copy()->addMinutes(30 * $slotIdx);
    $slot = ServiceSlot::create([
        'host_id'         => $HOST,
        'service_plan_id' => $SERVICE_PLANS[$slotIdx % count($SERVICE_PLANS)],
        'instructor_id'   => $INSTRUCTORS[$slotIdx % count($INSTRUCTORS)],
        'location_id'     => $locId,
        'title'           => '[MOCK] Service ' . ($slotIdx + 1),
        'start_time'      => $start,
        'end_time'        => $start->copy()->addMinutes(45),
        'status'          => ServiceSlot::STATUS_BOOKED,
        'price'           => 40 + ($slotIdx % 4) * 10,
    ]);
    $clientId = $CLIENTS[$slotIdx % count($CLIENTS)];
    $book($clientId, $slot, (float) $slot->price);
    $summary['service']++;
    $slotIdx++;
}

// ── 4. A couple of space rentals (shown on the calendar; not Booking rows) ──
if ($cfg['rental_config'] !== null) {
    foreach (['10:00', '20:00'] as $ri => $time) {
        $start = $at($time);
        SpaceRental::create([
            'host_id'               => $HOST,
            'space_rental_config_id' => $cfg['rental_config'],
            'client_id'             => $CLIENTS[$ri],
            'purpose'               => 'workshop',
            'purpose_notes'         => '[MOCK] Workshop ' . ($ri + 1),
            'start_time'            => $start,
            'end_time'              => $start->copy()->addHours(2),
            'hourly_rate'           => 50,
            'hours_booked'          => 2,
            'subtotal'              => 100,
            'tax_amount'            => 8,
            'total_amount'          => 108,
            'deposit_amount'        => 50,
            'deposit_status'        => SpaceRental::DEPOSIT_PENDING,
            'currency'              => 'USD',
            'status'                => SpaceRental::STATUS_CONFIRMED,
        ]);
        $summary['rental']++;
    }
}

echo "Seeded {$bookingCount} bookings for {$DATE} (host {$HOST}):\n";
echo " - Class bookings:      {$summary['class']}\n";
echo " - Membership bookings: {$summary['membership']}\n";
echo " - Service bookings:    {$summary['service']}\n";
echo " - Space rentals:       {$summary['rental']}\n";