<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenFoodFactsService
{
    public function fetchByBarcode(string $barcode): ?array
    {
        // Try UK database first, then fall back to world database
        return $this->lookup("https://uk.openfoodfacts.org/api/v0/product/{$barcode}.json")
            ?? $this->lookup("https://world.openfoodfacts.org/api/v0/product/{$barcode}.json");
    }

    private function lookup(string $url): ?array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders(['User-Agent' => 'AllerScan/1.0'])
                ->get($url);

            if ($response->failed()) {
                return null;
            }

            $data = $response->json();

            if (($data['status'] ?? 0) !== 1 || empty($data['product'])) {
                return null;
            }

            $product = $data['product'];

            $lang = $product['lang'] ?? 'en';

            // Use ?: (not ??) so empty strings "" are treated as missing
            $title = $product['product_name_en']
                ?: $product['product_name']
                ?: $product["product_name_{$lang}"]
                ?: null;

            $ingredients = $product['ingredients_text_en']
                ?: $product['ingredients_text_with_allergens']
                ?: $product['ingredients_text']
                ?: $product["ingredients_text_with_allergens_{$lang}"]
                ?: $product["ingredients_text_{$lang}"]
                ?: null;

            return [
                'title'       => $title,
                'brand'       => $product['brands'] ?: null,
                'image_url'   => $product['image_url'] ?: null,
                'ingredients' => $ingredients,
                'lang'        => $lang,
                'raw'         => $product,
            ];
        } catch (\Throwable $e) {
            Log::error('OpenFoodFacts lookup failed', ['url' => $url, 'error' => $e->getMessage()]);

            return null;
        }
    }
}