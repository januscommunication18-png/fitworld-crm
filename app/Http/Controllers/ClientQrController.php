<?php

namespace App\Http\Controllers;

use App\Models\ClientQrCode;
use App\Services\QrImageService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClientQrController extends Controller
{
    public function __construct(
        protected QrImageService $qrImageService,
    ) {}

    /**
     * Serve a client's check-in QR PNG by its (secret) token.
     * Public — the opaque token is the credential. `?dl=1` forces a download.
     */
    public function show(Request $request, string $token): Response
    {
        $qr = ClientQrCode::active()->where('qr_token', $token)->with('client')->first();

        abort_if(! $qr || ! $qr->client, 404);

        $png = $this->qrImageService->pngForToken($token, 480);

        $disposition = $request->boolean('dl') ? 'attachment' : 'inline';
        $filename = 'checkin-qr-' . $qr->client_id . '.png';

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => $disposition . '; filename="' . $filename . '"',
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }
}
