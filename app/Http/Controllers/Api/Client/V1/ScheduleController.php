<?php

namespace App\Http\Controllers\Api\Client\V1;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ClassSession;
use App\Models\Client;
use App\Models\OneOnOneBooking;
use App\Models\ServiceSlot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * The signed-in client's own schedule: their class/service bookings and
     * 1:1 appointments, split into upcoming and past. Everything is queried
     * through the authenticated client, so no other client's bookings can
     * ever appear here.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var Client $client */
        $client = $request->user();

        $bookings = $client->bookings()
            ->whereNotIn('status', [Booking::STATUS_CANCELLED])
            ->with(['bookable' => function ($morphTo) {
                $morphTo->morphWith([
                    ClassSession::class => ['classPlan', 'primaryInstructor', 'location'],
                    ServiceSlot::class => ['servicePlan', 'instructor', 'location'],
                ]);
            }])
            ->get()
            ->filter(fn (Booking $b) => $b->bookable?->start_time !== null)
            ->map(fn (Booking $b) => $this->bookingItem($b));

        $oneOnOnes = OneOnOneBooking::query()
            ->where('client_id', $client->id)
            ->where('host_id', $client->host_id)
            ->whereNotIn('status', [
                OneOnOneBooking::STATUS_CANCELLED,
                OneOnOneBooking::STATUS_DECLINED,
            ])
            ->with('bookingProfile')
            ->get()
            ->map(fn (OneOnOneBooking $b) => $this->oneOnOneItem($b));

        $all = $bookings->concat($oneOnOnes);
        $now = now();

        $upcoming = $all->filter(fn ($i) => $i['_start']->gte($now))
            ->sortBy('_start')->values()->map(fn ($i) => collect($i)->except('_start'));
        $past = $all->filter(fn ($i) => $i['_start']->lt($now))
            ->sortByDesc('_start')->values()->map(fn ($i) => collect($i)->except('_start'));

        return response()->json(['data' => [
            'upcoming' => $upcoming,
            'past' => $past,
        ]]);
    }

    private function bookingItem(Booking $booking): array
    {
        $bookable = $booking->bookable;
        $isClass = $bookable instanceof ClassSession;

        if ($isClass) {
            $name = $bookable->title ?? $bookable->classPlan?->name ?? 'Class';
            $instructor = $bookable->primaryInstructor?->name;
        } else {
            $name = $bookable->servicePlan?->name ?? 'Session';
            $instructor = $bookable->instructor?->name;
        }

        $upcoming = $bookable->start_time->isFuture();
        $checkIn = $booking->selfCheckInState();

        return [
            'id' => 'booking-'.$booking->id,
            'booking_id' => $booking->id,
            'type' => $isClass ? 'class' : 'service',
            'name' => $name,
            'status' => $booking->status,
            'start_time' => $bookable->start_time->toIso8601String(),
            'end_time' => $bookable->end_time?->toIso8601String(),
            'instructor' => $instructor,
            'location' => $bookable->location?->name,
            'booked_at' => $booking->booked_at?->toIso8601String(),
            'can_cancel' => $upcoming && $booking->canBeCancelled(),
            'cancel_deadline' => $upcoming
                ? $booking->getCancellationDeadline()?->toIso8601String()
                : null,
            'checked_in' => $booking->checked_in_at !== null,
            'can_check_in' => $checkIn['allowed'],
            'check_in_opens_at' => $checkIn['reason'] === 'too_early'
                ? $checkIn['opens_at']->toIso8601String()
                : null,
            '_start' => $bookable->start_time,
        ];
    }

    private function oneOnOneItem(OneOnOneBooking $booking): array
    {
        return [
            'id' => 'appointment-'.$booking->id,
            'booking_id' => null, // 1:1s are managed by the studio, not the app
            'type' => 'appointment',
            'name' => '1:1 Session',
            'status' => $booking->status,
            'start_time' => $booking->start_time->toIso8601String(),
            'end_time' => $booking->end_time?->toIso8601String(),
            'instructor' => $booking->bookingProfile?->display_name,
            'location' => null,
            'booked_at' => $booking->booked_at?->toIso8601String(),
            'can_cancel' => false,
            'cancel_deadline' => null,
            'checked_in' => false,
            'can_check_in' => false,
            'check_in_opens_at' => null,
            '_start' => $booking->start_time,
        ];
    }
}