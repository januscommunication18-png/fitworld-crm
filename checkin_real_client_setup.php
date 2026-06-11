<?php

/*
| Real-client check-in test on host 26:
|  - picks a real client (not the mock/seed ones)
|  - creates a class session starting in ~25 min (within the check-in window now)
|  - books the client (paid) so a scan checks them in
|  - prints the client + token; a QR PNG is generated separately
*/

use App\Models\Client;
use App\Models\ClassSession;
use App\Models\Booking;

$HOST = 26;

// A real client: skip the seeded/test accounts created earlier.
$client = Client::where('host_id', $HOST)
    ->where('email', 'not like', '%@mockseed.test')
    ->where('email', '!=', 'camera.test@mockseed.test')
    ->orderBy('id')
    ->first();

$token = $client->getOrCreateQrCode()->qr_token;

// Clean prior runs of this test session for a single clear eligible option.
$oldIds = ClassSession::where('host_id', $HOST)->where('title', 'Evening Flow (test)')->pluck('id');
Booking::where('bookable_type', ClassSession::class)->whereIn('bookable_id', $oldIds)->delete();
ClassSession::whereIn('id', $oldIds)->delete();

$cs = ClassSession::create([
    'host_id' => $HOST, 'class_plan_id' => 11, 'primary_instructor_id' => 21,
    'location_id' => 17, 'room_id' => 21, 'title' => 'Evening Flow (test)',
    'start_time' => now()->addMinutes(25), 'end_time' => now()->addMinutes(85),
    'duration_minutes' => 60, 'capacity' => 20, 'price' => 20, 'status' => 'published',
]);

// Avoid a duplicate booking if re-run within the same minute.
Booking::where('host_id', $HOST)->where('client_id', $client->id)
    ->where('bookable_type', ClassSession::class)->where('bookable_id', $cs->id)->delete();

Booking::create([
    'host_id' => $HOST, 'client_id' => $client->id,
    'bookable_type' => ClassSession::class, 'bookable_id' => $cs->id,
    'status' => 'confirmed', 'intake_status' => 'not_required',
    'payment_method' => 'comp', 'price_paid' => 0,
    'booking_source' => 'internal_walkin', 'booked_at' => now(),
]);

echo 'CLIENT_ID=' . $client->id . PHP_EOL;
echo 'CLIENT_NAME=' . $client->full_name . PHP_EOL;
echo 'SESSION_ID=' . $cs->id . PHP_EOL;
echo 'START=' . $cs->start_time->format('g:i A') . PHP_EOL;
echo 'TOKEN=' . $token . PHP_EOL;
