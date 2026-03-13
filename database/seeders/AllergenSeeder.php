<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AllergenSeeder extends Seeder
{
    public function run(): void
    {
        $allergens = [
            [
                'slug'        => 'wheat',
                'name'        => 'Wheat',
                'description' => 'Includes wheat and wheat-derived ingredients.',
                'keywords'    => ['wheat', 'durum', 'semolina', 'farina', 'bulgur', 'couscous', 'graham flour'],
            ],
            [
                'slug'        => 'corn',
                'name'        => 'Corn (Maize)',
                'description' => 'Includes corn and corn-derived ingredients.',
                'keywords'    => ['corn', 'maize', 'cornstarch', 'corn syrup', 'corn flour', 'dextrose', 'maltodextrin'],
            ],
            [
                'slug'        => 'gelatin',
                'name'        => 'Gelatin',
                'description' => 'Derived from animal collagen and used in desserts and capsules.',
                'keywords'    => ['gelatin', 'gelatine', 'collagen'],
            ],
            [
                'slug'        => 'msg',
                'name'        => 'Monosodium Glutamate (MSG)',
                'description' => 'Flavor enhancer commonly used in processed foods.',
                'keywords'    => ['msg', 'monosodium glutamate', 'e621'],
            ],
            [
                'slug'        => 'coconut',
                'name'        => 'Coconut',
                'description' => 'Includes coconut and coconut-derived products.',
                'keywords'    => ['coconut', 'coconut milk', 'coconut oil', 'coconut cream'],
            ],
            [
                'slug'        => 'banana',
                'name'        => 'Banana',
                'description' => 'Fruit allergy sometimes linked to latex allergy.',
                'keywords'    => ['banana'],
            ],
            [
                'slug'        => 'avocado',
                'name'        => 'Avocado',
                'description' => 'Often associated with latex-fruit syndrome.',
                'keywords'    => ['avocado'],
            ],
            [
                'slug'        => 'kiwi',
                'name'        => 'Kiwi',
                'description' => 'Fruit allergy causing oral allergy syndrome.',
                'keywords'    => ['kiwi'],
            ],
            [
                'slug'        => 'strawberry',
                'name'        => 'Strawberry',
                'description' => 'Fruit allergy causing itching or rash.',
                'keywords'    => ['strawberry'],
            ],
            [
                'slug'        => 'garlic',
                'name'        => 'Garlic',
                'description' => 'Allergy or sensitivity to garlic.',
                'keywords'    => ['garlic'],
            ],
            [
                'slug'        => 'onion',
                'name'        => 'Onion',
                'description' => 'Allergy or intolerance to onions.',
                'keywords'    => ['onion'],
            ],
            [
                'slug'        => 'cocoa',
                'name'        => 'Cocoa / Chocolate',
                'description' => 'Includes cocoa and chocolate products.',
                'keywords'    => ['cocoa', 'chocolate', 'cacao'],
            ],
            [
                'slug'        => 'legumes',
                'name'        => 'Legumes',
                'description' => 'Includes beans, lentils, peas and chickpeas.',
                'keywords'    => ['bean', 'beans', 'lentil', 'lentils', 'pea', 'peas', 'chickpea', 'chickpeas', 'legume'],
            ],
        ];

        foreach ($allergens as $allergen) {
            DB::table('allergens')->updateOrInsert(
                ['slug' => $allergen['slug']],
                array_merge($allergen, [
                    'keywords'   => json_encode($allergen['keywords']),
                    'updated_at' => now(),
                    'created_at' => now(),
                ])
            );
        }
    }
}
