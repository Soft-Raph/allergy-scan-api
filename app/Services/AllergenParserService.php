<?php

namespace App\Services;

use App\Models\Allergen;
use Illuminate\Support\Collection;

class AllergenParserService
{
    private Collection $allergens;

    public function __construct()
    {
        $this->allergens = Allergen::all();
    }

    /**
     * Parse an ingredients string and return matched allergen IDs grouped by type.
     *
     * @return array{contains: array<int,int>, may_contain: array<int,int>}
     */
    public function parse(string $ingredientsText): array
    {
        $lower = strtolower($ingredientsText);

        // Split into "contains" and "may contain" sections
        $containsText   = $lower;
        $mayContainText = '';

        if (preg_match('/may contain[:\s]+(.*)/i', $lower, $matches)) {
            $mayContainText = $matches[1];
            $containsText   = str_replace($matches[0], '', $lower);
        }

        $contains   = [];
        $mayContain = [];

        foreach ($this->allergens as $allergen) {
            $keywords = $allergen->keywords ?? [];

            foreach ($keywords as $keyword) {
                if ($this->matchesWord($containsText, strtolower($keyword))) {
                    $contains[] = $allergen->id;
                    break;
                }
            }

            if (! in_array($allergen->id, $contains)) {
                foreach ($keywords as $keyword) {
                    if ($this->matchesWord($mayContainText, strtolower($keyword))) {
                        $mayContain[] = $allergen->id;
                        break;
                    }
                }
            }
        }

        return [
            'contains'    => array_unique($contains),
            'may_contain' => array_unique($mayContain),
        ];
    }

    /**
     * Match a keyword as a whole word (not a substring of another word).
     * e.g. "nut" matches "nuts" and "nut," but NOT "nutrition" or "peanut".
     */
    private function matchesWord(string $text, string $keyword): bool
    {
        if ($text === '') {
            return false;
        }

        $pattern = '/\b' . preg_quote($keyword, '/') . '\b/i';

        return (bool) preg_match($pattern, $text);
    }
}