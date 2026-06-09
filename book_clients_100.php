<?php

/*
|--------------------------------------------------------------------------
| Book 100 clients into the EXISTING schedule for one date (run via tinker)
|--------------------------------------------------------------------------
| Creates 100 fresh clients and books each into an existing class/membership
| session already on the schedule for $DATE. It does NOT create any sessions —
| it only books clients into what's already there, raising those sessions'
| capacity so all bookings fit.
|
| Run:
|   php artisan tinker --execute="require 'book_clients_100.php';"
|
| Idempotent: re-running first deletes the previously-seeded clients
| (email like %@mockseed.test) and their bookings, then recreates them.
*/

use App\Models\Booking;
use App\Models\Client;
use App\Models\ClassSession;

$HOST   = 26;
$DATE   = '2026-06-11';
$TARGET = 100;
$MARKER = '@mockseed.test'; // email suffix used to find/clean seeded clients

// ── Existing sessions on $DATE to book into ──
$sessions = ClassSession::where('host_id', $HOST)
    ->whereDate('start_time', $DATE)
    ->orderBy('start_time')
    ->get();

if ($sessions->isEmpty()) {
    echo "No existing sessions on {$DATE} for host {$HOST} — nothing to book into.\n";
    return;
}

// ── Clean up previously-seeded clients + their bookings (idempotent) ──
$oldClientIds = Client::where('host_id', $HOST)->where('email', 'like', '%' . $MARKER)->pluck('id');
Booking::where('host_id', $HOST)->whereIn('client_id', $oldClientIds)->delete();
Client::whereIn('id', $oldClientIds)->forceDelete();

// ── Distribute 100 bookings across the existing sessions ──
$n = $sessions->count();
$base = intdiv($TARGET, $n);
$rem  = $TARGET % $n;
$alloc = [];
foreach ($sessions as $i => $s) {
    $alloc[$s->id] = $base + ($i < $rem ? 1 : 0); // first $rem sessions get one extra
}

// Make sure each session's capacity can hold its new bookings (+ existing confirmed)
foreach ($sessions as $s) {
    $needed = $s->confirmedBookings()->count() + $alloc[$s->id];
    if ($s->capacity < $needed) {
        $s->capacity = $needed;
        $s->save();
    }
}

$firstNames = ['Ava', 'Liam', 'Mia', 'Noah', 'Emma', 'Ethan', 'Olivia', 'Lucas', 'Sophia', 'Mason',
    'Isla', 'Leo', 'Amara', 'Kai', 'Nora', 'Ravi', 'Zoe', 'Owen', 'Maya', 'Finn'];
$lastNames = ['Carter', 'Nguyen', 'Patel', 'Brooks', 'Rivera', 'Khan', 'Walsh', 'Diaz', 'Hayes', 'Ali'];

$statusCycle = [
    ['confirmed', 'completed',    'stripe',     true],
    ['confirmed', 'pending',      'cash',       false],
    ['confirmed', 'completed',    'membership', true],
    ['confirmed', 'not_required', 'pack',       false],
    ['confirmed', 'completed',    'comp',       true],
    ['no_show',   'not_required', 'stripe',     false],
];

$created = 0;
$si = 0;
$summary = [];

foreach ($sessions as $s) {
    $isMembership = $s->class_plan_id === null;
    $count = $alloc[$s->id];
    $summary[$s->display_title] = $count;

    for ($k = 0; $k < $count; $k++) {
        $idx = $created + 1;
        $client = Client::create([
            'host_id'           => $HOST,
            'first_name'        => $firstNames[$idx % count($firstNames)],
            'last_name'         => $lastNames[$idx % count($lastNames)] . ' ' . $idx,
            'email'             => 'mock' . $idx . $MARKER,
            'status'            => 'active',
            'membership_status' => $isMembership ? 'active' : 'none',
            'lead_source'       => 'manual',
        ]);

        [$status, $intake, $method, $checkedIn] = $statusCycle[$si % count($statusCycle)];
        $si++;
        if ($isMembership) {
            $method = 'membership';
        }
        $price = in_array($method, ['membership', 'comp']) ? 0 : (float) ($s->price ?? 20);

        Booking::create([
            'host_id'        => $HOST,
            'client_id'      => $client->id,
            'bookable_type'  => ClassSession::class,
            'bookable_id'    => $s->id,
            'status'         => $status,
            'intake_status'  => $intake,
            'payment_method' => $method,
            'price_paid'     => $price,
            'booking_source' => 'internal_walkin',
            'booked_at'      => now(),
            'checked_in_at'  => $checkedIn ? now() : null,
        ]);
        $created++;
    }
}

echo "Booked {$created} new clients into existing sessions on {$DATE} (host {$HOST}):\n";
foreach ($summary as $title => $c) {
    echo " - {$title}: {$c} clients\n";
}