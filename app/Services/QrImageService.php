<?php

namespace App\Services;

use App\Models\Client;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

/**
 * Generates QR code images for client check-in tokens. Uses endroid/qr-code
 * (local, GD-backed) so nothing depends on an external CDN.
 */
class QrImageService
{
    /**
     * Raw PNG bytes encoding the given token.
     */
    public function pngForToken(string $token, int $size = 320, int $margin = 12): string
    {
        return (new Builder(
            writer: new PngWriter(),
            data: $token,
            size: $size,
            margin: $margin,
        ))->build()->getString();
    }

    /**
     * Raw PNG bytes for a client's active check-in token (creates one if needed).
     */
    public function pngForClient(Client $client, int $size = 320): string
    {
        return $this->pngForToken($client->getOrCreateQrCode()->qr_token, $size);
    }

    /**
     * Base64 data URI (for inline <img src> where embedding isn't available).
     */
    public function dataUriForToken(string $token, int $size = 320): string
    {
        return 'data:image/png;base64,' . base64_encode($this->pngForToken($token, $size));
    }
}
