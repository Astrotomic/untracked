<?php

namespace App\Managers\Favicon;

use App\Contracts\FaviconDriver;

final class GoogleFaviconDriver implements FaviconDriver
{
    public function url(string $domain, ?int $size = null): string
    {
        return 'https://t1.gstatic.com/faviconV2?'.http_build_query([
            'client' => 'SOCIAL',
            'type' => 'FAVICON',
            'fallback_opts' => 'TYPE,SIZE,URL',
            'url' => 'https://'.$domain,
            'size' => $size ?? 128,
        ], '', '&', PHP_QUERY_RFC3986);
    }
}
