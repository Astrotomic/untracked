<?php

namespace App\Analytics\Normalizers;

use Illuminate\Support\Str;

class ReferrerNormalizer
{
    public function normalize(?string $value, ?string $websiteDomain = null): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $url = str_contains($value, '://') ? $value : 'https://'.$value;
        $host = parse_url($url, PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            return null;
        }

        $host = $this->normalizeHost($host);
        $websiteDomain = $websiteDomain !== null ? $this->normalizeHost($websiteDomain) : null;

        if ($websiteDomain !== null && hash_equals($websiteDomain, $host)) {
            return null;
        }

        return Str::limit($host, 253, '');
    }

    private function normalizeHost(string $host): string
    {
        $host = strtolower(rtrim(trim($host), '.'));

        return preg_replace('/^www\./', '', $host) ?? $host;
    }
}
