<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductSyncService
{
    public function __construct(
        private readonly AllergenParserService $parser,
        private readonly TranslationService    $translator,
    ) {}

    /**
     * Persist a product from a normalised lookup result.
     *
     * Expected keys: title, brand, image_url, ingredients, raw
     */
    public function sync(string $barcode, array $item): Product
    {
        Log::info('ProductSync received', [
            'barcode'     => $barcode,
            'title'       => $item['title'] ?? 'NULL',
            'brand'       => $item['brand'] ?? 'NULL',
            'ingredients' => $item['ingredients'] ?? 'NULL',
            'lang'        => $item['lang'] ?? 'NULL',
            'raw_keys'    => array_keys($item['raw'] ?? []),
        ]);

        return DB::transaction(function () use ($barcode, $item) {
            $product = Product::updateOrCreate(
                ['barcode' => $barcode],
                [
                    'name'             => $item['title'] ?? 'Unknown Product',
                    'brand'            => $item['brand'] ?? null,
                    'image_url'        => $item['image_url'] ?? null,
                    'ingredients_text' => $this->resolveIngredients($item),
                    'upc_data'         => $item['raw'] ?? null,
                    'fetched_at'       => now(),
                ]
            );

            $allergenMap = [];

            if ($product->ingredients_text) {
                $parsed = $this->parser->parse($product->ingredients_text);

                foreach ($parsed['contains'] as $allergenId) {
                    $allergenMap[$allergenId] = ['type' => 'contains'];
                }

                foreach ($parsed['may_contain'] as $allergenId) {
                    $allergenMap[$allergenId] = ['type' => 'may_contain'];
                }
            }

            $product->allergens()->sync($allergenMap);

            return $product->load('allergens');
        });
    }

    private function resolveIngredients(array $item): ?string
    {
        $ingredients = $item['ingredients'] ?? null;

        if (! $ingredients) {
            return null;
        }

        // Translate if the source language is not English
        $lang = $item['lang'] ?? 'en';

        if ($lang !== 'en') {
            $ingredients = $this->translator->toEnglish($ingredients);
        }

        return $ingredients;
    }
}
