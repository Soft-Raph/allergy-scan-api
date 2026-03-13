<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NutritionixService
{
    private string $baseUrl = 'https://trackapi.nutritionix.com/v2/search/item';

    public function fetchByBarcode(string $barcode): ?array
    {
        $appId  = config('services.nutritionix.app_id');
        $appKey = config('services.nutritionix.app_key');

        if (! $appId || ! $appKey) {
            return null;
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'x-app-id'  => $appId,
                    'x-app-key' => $appKey,
                ])
                ->get($this->baseUrl, ['upc' => $barcode]);

            if ($response->failed()) {
                return null;
            }

            $data = $response->json();
            $food = $data['foods'][0] ?? null;

            if (! $food) {
                return null;
            }

            return [
                'title'       => $food['food_name'] ?? null,
                'brand'       => $food['brand_name'] ?? null,
                'image_url'   => $food['photo']['thumb'] ?? null,
                'ingredients' => $food['nf_ingredient_statement'] ?? null,
                'raw'         => $food,
            ];
        } catch (\Throwable $e) {
            Log::error('Nutritionix lookup failed', ['barcode' => $barcode, 'error' => $e->getMessage()]);

            return null;
        }
    }
}