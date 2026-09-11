<?php

namespace App\Normalizers;

use App\Concerns\Resolvable;
use App\Contracts\Normalizer;
use Illuminate\Support\Str;

final readonly class ReferrerNormalizer implements Normalizer
{
    use Resolvable;

    public function normalize(?string $value, ?string $websiteDomain = null): ?string
    {
        $host = $this->normalizeHost($value);
        $websiteDomain = $this->normalizeHost($websiteDomain);

        if ($websiteDomain !== null && Str::equals($websiteDomain, $host)) {
            return null;
        }

        return ValueNormalizer::make()->normalize($host);
    }

    private function normalizeHost(?string $host): ?string
    {
        $host = trim((string) $host);

        if (empty($host)) {
            return null;
        }

        $url = str_contains($host, '://') ? $host : 'https://'.$host;
        $host = parse_url($url, PHP_URL_HOST);

        if (empty($host)) {
            return null;
        }

        $host = strtolower(rtrim(trim($host), '.'));

        return preg_replace('/^www\./', '', $host) ?? $host;
    }
}
