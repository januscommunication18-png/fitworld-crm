<?php

/*
| Sets up a ready-to-scan camera check-in test on host 26:
|  - ensures a "Camera Test" client + its permanent QR token
|  - creates a class session starting in 10 min (inside the check-in window)
|  - books the client into it (comp = paid) so a scan auto-checks-in
| Prints the token so a QR PNG can be generated from it.
|
| Re-run any time to get a fresh eligible booking:
|   php artisan tinker --execute="require 'checkin_camera_setup.php';"
*/

use App\Models\Client;
use App\Models\ClassSession;
use App\Models\Booking;

$HOST = 26;

$client = Client::firstOrCreate(
    ['host_id' => $HOST, 'email' => 'camera.test@mockseed.test'],
    ['first_name' => 'Camera', 'last_name' => 'Test', 'status' => 'active', 'lead_source' => 'manual']
);

$token = $client->getOrCreateQrCode()->qr_token;

// Remove any prior test sessions/bookings for a clean, single eligible option.
$oldIds = ClassSession::where('host_id', $HOST)->where('title', '[CAMERA TEST]')->pluck('id');
Booking::where('bookable_type', ClassSession::class)->whereIn('bookable_id', $oldIds)->delete();
ClassSession::whereIn('id', $oldIds)->delete();

$cs = ClassSession::create([
    'host_id' => $HOST, 'class_plan_id' => 11, 'primary_instructor_id' => 21,
    'location_id' => 17, 'room_id' => 21, 'title' => '[CAMERA TEST]',
    'start_time' => now()->addMinutes(10), 'end_time' => now()->addMinutes(70),
    'duration_minutes' => 60, 'capacity' => 20, 'price' => 20, 'status' => 'published',
]);

Booking::create([
    'host_id' => $HOST, 'client_id' => $client->id,
    'bookable_type' => ClassSession::class, 'bookable_id' => $cs->id,
    'status' => 'confirmed', 'intake_status' => 'not_required',
    'payment_method' => 'comp', 'price_paid' => 0,
    'booking_source' => 'internal_walkin', 'booked_at' => now(),
]);

echo "Client:  {$client->full_name} (id {$client->id})\n";
echo "Session: #{$cs->id} at " . $cs->start_time->format('g:i A') . " (window opens ~30 min before)\n";
echo "QR TOKEN: {$token}\n";
