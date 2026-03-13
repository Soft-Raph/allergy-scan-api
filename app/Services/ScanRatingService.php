<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class ScanRatingService
{
    /**
     * Compute the safety rating for a product against a set of allergen IDs.
     *
     * @param  Collection<int, \App\Models\Allergen>  $productAllergens  (with pivot 'type')
     * @param  array<int>  $userAllergenIds
     * @return array{rating: string, triggered_allergens: array}
     */
    public function computeRating(Collection $productAllergens, array $userAllergenIds): array
    {
        $triggered = ['unsafe' => [], 'caution' => []];
//        dd($productAllergens, $userAllergenIds);
        foreach ($productAllergens as $allergen) {
            if (! in_array($allergen->id, $userAllergenIds)) {
                continue;
            }

            $type = $allergen->pivot->type;

            if ($type === 'contains') {
                $triggered['unsafe'][] = [
                    'id'   => $allergen->id,
                    'slug' => $allergen->slug,
                    'name' => $allergen->name,
                    'type' => 'contains',
                ];
            } elseif ($type === 'may_contain') {
                $triggered['caution'][] = [
                    'id'   => $allergen->id,
                    'slug' => $allergen->slug,
                    'name' => $allergen->name,
                    'type' => 'may_contain',
                ];
            }
        }

        if (! empty($triggered['unsafe'])) {
            return [
                'rating'               => 'unsafe',
                'triggered_allergens'  => array_merge($triggered['unsafe'], $triggered['caution']),
            ];
        }

        if (! empty($triggered['caution'])) {
            return [
                'rating'               => 'caution',
                'triggered_allergens'  => $triggered['caution'],
            ];
        }

        return [
            'rating'              => 'safe',
            'triggered_allergens' => [],
        ];
    }
}
