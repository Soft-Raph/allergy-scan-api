<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TranslationService
{
    private string $baseUrl = 'https://api.mymemory.translated.net/get';

    /**
     * Translate text to English using MyMemory free API.
     * Handles long texts by chunking (MyMemory limit: 500 chars per request).
     */
    public function toEnglish(string $text): string
    {
        $text = trim($text);

        if ($text === '' || $this->isEnglish($text)) {
            return $text;
        }

        $chunks = $this->chunk($text, 480);
        $translated = [];

        foreach ($chunks as $chunk) {
            $result = $this->translate($chunk);
            $translated[] = $result ?? $chunk;
        }

        return implode(' ', $translated);
    }

    private function translate(string $text): ?string
    {
        try {
            $response = Http::timeout(8)->get($this->baseUrl, [
                'q'        => $text,
                'langpair' => 'autodetect|en',
            ]);

            if ($response->failed()) {
                return null;
            }

            $translated = $response->json('responseData.translatedText');

            // MyMemory returns the original text when it can't translate
            return $translated ?: null;
        } catch (\Throwable $e) {
            Log::warning('Translation failed', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Simple heuristic: if text is mostly ASCII letters it's likely English already.
     * Accented characters (French, German, Spanish etc.) have high byte values.
     */
    private function isEnglish(string $text): bool
    {
        $nonAscii = preg_match_all('/[^\x00-\x7F]/', $text);
        $total    = mb_strlen($text);

        return ($nonAscii / max($total, 1)) < 0.05;
    }

    private function chunk(string $text, int $size): array
    {
        $chunks = [];
        // Split on commas/periods to keep ingredient items together
        $parts = preg_split('/(?<=[,.])\s+/', $text);
        $current = '';

        foreach ($parts as $part) {
            if (strlen($current) + strlen($part) > $size) {
                if ($current !== '') {
                    $chunks[] = trim($current);
                }
                $current = $part;
            } else {
                $current .= ($current ? ' ' : '') . $part;
            }
        }

        if ($current !== '') {
            $chunks[] = trim($current);
        }

        return $chunks ?: [$text];
    }
}