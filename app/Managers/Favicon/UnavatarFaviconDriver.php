<?php

namespace App\Managers\Favicon;

use App\Contracts\FaviconDriver;
use Astrotomic\Unavatar\Unavatar;

final class UnavatarFaviconDriver implements FaviconDriver
{
    public function url(string $domain, ?int $size = null): string
    {
        return Unavatar::domain($domain)->toUrl();
    }
}
