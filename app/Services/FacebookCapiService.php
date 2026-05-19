<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FacebookCapiService
{
    public function sendEvent(array $eventData)
    {
        $pixelId = config('services.facebook.pixel_id');
        $accessToken = config('services.facebook.access_token');

        $url = "https://graph.facebook.com/v18.0/{$pixelId}/events";

        $response = Http::post($url, [
            'data' => [$eventData],
            'access_token' => $accessToken,
        ]);

        return $response->json();
    }
}
