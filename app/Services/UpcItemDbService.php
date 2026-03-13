<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpcItemDbService
{
    private string $baseUrl = 'https://api.upcitemdb.com/prod/trial/lookup';

    public function fetchByBarcode(string $barcode): ?array
    {
        try {
            $response = Http::timeout(10)
                ->get($this->baseUrl, ['upc' => $barcode]);

            if ($response->failed()) {
                return null;
            }

            $data = $response->json();

            if (($data['code'] ?? '') !== 'OK' || empty($data['items'])) {
                return null;
            }

            $item = $data['items'][0];

            return [
                'title'       => $item['title'] ?? null,
                'brand'       => $item['brand'] ?? null,
                'image_url'   => $item['images'][0] ?? null,
                'ingredients' => $item['ingredients'] ?? null,
                'raw'         => $item,
            ];
        } catch (\Throwable $e) {
            Log::error('UpcItemDb lookup failed', ['barcode' => $barcode, 'error' => $e->getMessage()]);

            return null;
        }
    }
}