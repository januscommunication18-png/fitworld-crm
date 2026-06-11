<?php

namespace App\Http\Controllers\Api\Client\V1;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The client's personal check-in identity for the branded app. The app
 * renders `qr_token` as a QR code; the staff app / web Digital Check-In
 * scanner resolves that same token (DigitalCheckinService::resolveByToken),
 * so no scanner changes are needed.
 */
class CheckinController extends Controller
{
    public function myCode(Request $request): JsonResponse
    {
        $client = $request->user();

        return response()->json(['data' => [
            'client_code' => $client->getOrCreateClientCode(),
            'qr_token' => $client->getOrCreateQrCode()->qr_token,
        ]]);
    }

    /**
     * Self check-in for one of the client's own bookings, within the
     * studio's check-in window — mirrors the member portal flow.
     */
    public function selfCheckIn(Request $request, int $bookingId): JsonResponse
    {
        /** @var Client $client */
        $client = $request->user();

        $booking = Booking::query()
            ->where('id', $bookingId)
            ->where('client_id', $client->id)
            ->where('host_id', $client->host_id)
            ->first();

        if (! $booking) {
            return response()->json(['message' => 'Booking not found.'], 404);
        }

        $state = $booking->selfCheckInState();
        if (! $state['allowed']) {
            $messages = [
                'disabled' => 'Self check-in is not available at this studio.',
                'not_confirmed' => 'This booking cannot be checked in.',
                'already' => 'You are already checked in.',
                'no_session_time' => 'This booking has no scheduled time.',
                'too_early' => 'Check-in opens '
                    .($state['opens_at'] ?? now())->diffForHumans().'.',
                'too_late' => 'The check-in window for this session has closed.',
            ];

            return response()->json([
                'message' => $messages[$state['reason']] ?? 'Check-in is not available right now.',
            ], 422);
        }

        $booking->update([
            'checked_in_at' => now(),
            'checked_in_method' => Booking::CHECKIN_SELF,
        ]);

        return response()->json(['data' => [
            'booking_id' => $booking->id,
            'checked_in_at' => $booking->checked_in_at->toIso8601String(),
        ]]);
    }
}
