<?php

namespace App\Drivers\Favicon;

use App\Contracts\FaviconDriver;
use RuntimeException;

final readonly class LogoDevFaviconDriver implements FaviconDriver
{
    public function __construct(private string $token)
    {
        if (blank($this->token)) {
            throw new RuntimeException('The Logo.dev favicon driver requires LOGO_DEV_TOKEN.');
        }
    }

    public function url(string $domain, ?int $size = null): string
    {
        return 'https://img.logo.dev/'.rawurlencode($domain).'?'.http_build_query([
            'token' => $this->token,
            'size' => $size ?? 128,
            'format' => 'png',
            'retina' => 'true',
            'fallback' => 'monogram',
        ], '', '&', PHP_QUERY_RFC3986);
    }
}
