<?php

namespace App\Normalizers;

use App\Concerns\Resolvable;
use App\Contracts\Normalizer;
use App\Values\Client;
use Illuminate\Support\Str;

final readonly class ClientNormalizer implements Normalizer
{
    use Resolvable;

    public function normalize(?string $value): string
    {
        $value = trim((string) $value);

        if (empty($value) || Str::equals($value, Client::OTHER)) {
            return Client::OTHER;
        }

        if (Str::contains($value, ['bot', 'crawler', 'spider', 'headless'], true)) {
            return Client::BOT;
        }

        if (Str::containsAll($value, ['chrome', 'webview'], true)) {
            return Client::CHROME_WEBVIEW;
        }

        if (Str::containsAll($value, ['safari', 'webview'], true)) {
            return Client::SAFARI_WEBVIEW;
        }

        $value = preg_replace('/\s+(?:for\s+)?(?:ios|android)$/i', '', $value) ?? $value;
        $value = preg_replace('/^(?:mobile|tablet|desktop)\s+/i', '', $value) ?? $value;
        $value = preg_replace('/\s+(?:mobile|tablet|desktop)$/i', '', $value) ?? $value;
        $value = preg_replace('/(?:\s+|\/)v?\d+(?:[._-]\d+)*$/i', '', $value) ?? $value;
        $value = trim($value);

        return match (true) {
            Str::contains($value, 'firefox', true) => Client::FIREFOX,
            Str::contains($value, 'chrome', true) => Client::CHROME,
            Str::contains($value, 'chromium', true) => Client::CHROMIUM,
            Str::contains($value, ['edge', 'edg'], true) => Client::EDGE,
            Str::contains($value, ['ie'], true) => Client::IE,
            Str::contains($value, ['opera'], true) => Client::OPERA,
            Str::contains($value, ['safari'], true) => Client::SAFARI,
            Str::contains($value, ['duckduckgo'], true) => Client::DUCKDUCKGO,
            Str::contains($value, ['ecosia'], true) => Client::ECOSIA,
            Str::contains($value, ['qq browser'], true) => Client::QQ_BROWSER,
            default => ValueNormalizer::make()->normalize($value),
        };
    }
}
