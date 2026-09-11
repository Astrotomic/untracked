<?php

namespace App\Analytics\Normalizers;

use Illuminate\Support\Str;

class BrowserNormalizer
{
    public function normalize(string $family): string
    {
        $family = trim($family);

        if ($family === '' || strcasecmp($family, 'Other') === 0) {
            return 'Other';
        }

        if (preg_match('/bot|crawler|spider|headless/i', $family) === 1) {
            return 'Bot';
        }

        $normalized = strtolower($family);

        if (str_contains($normalized, 'chrome') && str_contains($normalized, 'webview')) {
            return 'Chrome WebView';
        }

        if (str_contains($normalized, 'safari') && str_contains($normalized, 'webview')) {
            return 'Safari WebView';
        }

        $family = preg_replace('/\s+(?:for\s+)?(?:ios|android)$/i', '', $family) ?? $family;
        $family = preg_replace('/^(?:mobile|tablet|desktop)\s+/i', '', $family) ?? $family;
        $family = preg_replace('/\s+(?:mobile|tablet|desktop)$/i', '', $family) ?? $family;
        $family = preg_replace('/(?:\s+|\/)v?\d+(?:[._-]\d+)*$/i', '', $family) ?? $family;
        $family = trim($family);
        $normalized = strtolower($family);

        return match (true) {
            str_starts_with($normalized, 'firefox') => 'Firefox',
            str_starts_with($normalized, 'chrome') => 'Chrome',
            $normalized === 'chromium' => 'Chromium',
            str_starts_with($normalized, 'edge'), str_starts_with($normalized, 'edg') => 'Edge',
            $normalized === 'ie', str_starts_with($normalized, 'ie ') => 'IE',
            str_starts_with($normalized, 'opera') => 'Opera',
            $normalized === 'safari' => 'Safari',
            str_starts_with($normalized, 'duckduckgo') => 'DuckDuckGo',
            str_starts_with($normalized, 'ecosia') => 'Ecosia',
            str_starts_with($normalized, 'qq browser') => 'QQ Browser',
            $normalized === 'android' => 'Android Browser',
            default => Str::limit($family, 100, ''),
        };
    }
}
