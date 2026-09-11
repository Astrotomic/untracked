<?php

namespace App\Normalizers;

use App\Concerns\Resolvable;
use App\Contracts\Normalizer;
use Illuminate\Support\Str;

final readonly class BrowserNormalizer implements Normalizer
{
    use Resolvable;

    public function normalize(?string $value): string
    {
        $value = trim((string) $value);

        if (empty($value) || Str::equals($value, 'other')) {
            return 'Other';
        }

        if (Str::contains($value, ['bot', 'crawler', 'spider', 'headless'], true)) {
            return 'Bot';
        }

        if (Str::containsAll($value, ['chrome', 'webview'], true)) {
            return 'Chrome WebView';
        }

        if (Str::containsAll($value, ['safari', 'webview'], true)) {
            return 'Safari WebView';
        }

        $value = preg_replace('/\s+(?:for\s+)?(?:ios|android)$/i', '', $value) ?? $value;
        $value = preg_replace('/^(?:mobile|tablet|desktop)\s+/i', '', $value) ?? $value;
        $value = preg_replace('/\s+(?:mobile|tablet|desktop)$/i', '', $value) ?? $value;
        $value = preg_replace('/(?:\s+|\/)v?\d+(?:[._-]\d+)*$/i', '', $value) ?? $value;
        $value = trim($value);

        return match (true) {
            Str::contains($value, 'firefox', true) => 'Firefox',
            Str::contains($value, 'chrome', true) => 'Chrome',
            Str::contains($value, 'chromium', true) => 'Chromium',
            Str::contains($value, ['edge', 'edg'], true) => 'Edge',
            Str::contains($value, ['ie'], true) => 'IE',
            Str::contains($value, ['opera'], true) => 'Opera',
            Str::contains($value, ['safari'], true) => 'Safari',
            Str::contains($value, ['duckduckgo'], true) => 'DuckDuckGo',
            Str::contains($value, ['ecosia'], true) => 'Ecosia',
            Str::contains($value, ['qq browser'], true) => 'QQ Browser',
            default => ValueNormalizer::make()->normalize($value),
        };
    }
}
