<?php

namespace App\Http\Controllers\Api\Client\V1;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * The signed-in client's editable profile. Email stays read-only here —
     * it is the login identifier and changing it requires staff.
     */
    public function show(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->payload($request->user())]);
    }

    /**
     * Upload/replace the client's profile photo (multipart `photo` field).
     */
    public function updatePhoto(Request $request): JsonResponse
    {
        /** @var Client $client */
        $client = $request->user();

        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $disk = config('filesystems.uploads');
        $this->deleteStoredPhoto($client, $disk);

        $path = $request->file('photo')
            ->storePublicly($client->host->getStoragePath('client-photos'), $disk);
        $client->update(['profile_photo' => $path]);

        return response()->json(['data' => $this->payload($client->fresh())]);
    }

    public function deletePhoto(Request $request): JsonResponse
    {
        /** @var Client $client */
        $client = $request->user();

        $this->deleteStoredPhoto($client, config('filesystems.uploads'));
        $client->update(['profile_photo' => null]);

        return response()->json(['data' => $this->payload($client->fresh())]);
    }

    private function deleteStoredPhoto(Client $client, string $disk): void
    {
        if (! $client->profile_photo || str_starts_with($client->profile_photo, 'http')) {
            return;
        }
        try {
            Storage::disk($disk)->delete($client->profile_photo);
        } catch (\Throwable $e) {
            // Ignore — file may live on a different disk or be missing.
        }
    }

    public function update(Request $request): JsonResponse
    {
        /** @var Client $client */
        $client = $request->user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'date_of_birth' => 'nullable|date|before:today',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:50',
        ]);

        $client->update($validated);

        return response()->json(['data' => $this->payload($client->fresh())]);
    }

    private function payload(Client $client): array
    {
        return [
            'id' => $client->id,
            'first_name' => $client->first_name,
            'last_name' => $client->last_name,
            'full_name' => $client->full_name,
            'email' => $client->email,
            'phone' => $client->phone,
            'initials' => $client->initials,
            'client_code' => $client->getOrCreateClientCode(),
            'photo_url' => self::photoUrl($client),
            'date_of_birth' => $client->date_of_birth?->toDateString(),
            'emergency_contact_name' => $client->emergency_contact_name,
            'emergency_contact_phone' => $client->emergency_contact_phone,
        ];
    }

    /**
     * Photo URL on the same origin the app is calling (APP_URL may point at
     * a different local host during development).
     */
    public static function photoUrl(Client $client): ?string
    {
        if (! $client->profile_photo) {
            return null;
        }
        if (str_starts_with($client->profile_photo, 'http')) {
            return $client->profile_photo;
        }

        return request()->getSchemeAndHttpHost()
            .'/api/client/v1/files/'.$client->profile_photo;
    }
}