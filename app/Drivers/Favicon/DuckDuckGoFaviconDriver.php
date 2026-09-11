<?php

namespace App\Drivers\Favicon;

use App\Contracts\FaviconDriver;

final class DuckDuckGoFaviconDriver implements FaviconDriver
{
    public function url(string $domain, ?int $size = null): string
    {
        return 'https://icons.duckduckgo.com/ip3/'.rawurlencode($domain).'.ico';
    }
}
