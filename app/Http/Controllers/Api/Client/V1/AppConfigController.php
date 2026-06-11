<?php

namespace App\Http\Controllers\Api\Client\V1;

use App\Http\Controllers\Controller;
use App\Models\Host;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Branding + runtime configuration for the branded client app. Public under
 * the `client.app` middleware (the studio app token is the only credential)
 * — never expose secrets here. Branding falls back to the booking page
 * settings and studio logo so studios get sensible defaults for free.
 */
class AppConfigController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        /** @var Host $host */
        $host = $request->attributes->get('currentHost');

        $bookingSettings = $host->booking_settings ?? [];

        $slides = collect($host->getClientAppSetting('onboarding_slides', []))
            ->map(fn ($slide) => [
                'title' => $slide['title'] ?? '',
                'body' => $slide['body'] ?? '',
                'image_url' => ! empty($slide['image_path'])
                    ? Storage::disk(config('filesystems.uploads'))->url($slide['image_path'])
                    : null,
            ])
            ->values();

        return response()->json(['data' => [
            'studio' => [
                'name' => $host->studio_name,
                'display_name' => $host->getClientAppSetting('app_display_name')
                    ?: ($bookingSettings['display_name'] ?? null)
                    ?: $host->studio_name,
                'logo_url' => $host->logo_url,
                'cover_image_url' => $host->cover_image_url,
                'support_email' => $host->getClientAppSetting('support_email') ?: ($host->email ?? null),
                'support_phone' => $host->getClientAppSetting('support_phone') ?: ($host->phone ?? null),
                'currency' => $host->default_currency ?? 'USD',
                'timezone' => $host->timezone ?? null,
            ],
            'branding' => [
                'primary_color' => $host->getClientAppSetting('primary_color')
                    ?: ($bookingSettings['primary_color'] ?? '#023E8A'),
                'theme' => $host->getClientAppSetting('theme')
                    ?: ($bookingSettings['theme'] ?? 'light'),
                'font' => $bookingSettings['font'] ?? null,
            ],
            'onboarding' => $slides,
            'features' => array_values($host->getMemberPortalSetting(
                'allowed_features',
                ['schedule', 'bookings', 'payments', 'invoices', 'profile'],
            )),
            'auth' => [
                'login_method' => $host->getMemberPortalSetting('login_method', 'otp'),
                'allow_self_registration' => (bool) $host->getMemberPortalSetting('allow_self_registration', true),
            ],
        ]]);
    }
}
