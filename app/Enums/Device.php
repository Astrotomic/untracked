<?php

namespace App\Enums;

use Illuminate\Support\Str;

enum Device: string
{
    case Desktop = 'desktop';
    case Mobile = 'mobile';
    case Tablet = 'tablet';
    case Bot = 'bot';
    case Other = 'other';

    public static function normalize(string $family, string $os, string $userAgent = ''): self
    {
        return match (true) {
            Str::contains($family, 'spider', true), Str::contains($userAgent, ['bot', 'crawler', 'spider', 'headless'], true) => self::Bot,
            Str::contains($family, ['ipad', 'tablet'], true), Str::contains($userAgent, 'tablet', true) => self::Tablet,
            Str::contains($family, ['iphone', 'ipod'], true), Str::contains($userAgent, 'mobile', true), Str::contains($os, ['ios', 'android'], true) => self::Mobile,
            Str::contains($os, ['windows', 'macos', 'linux', 'chromeos'], true) => self::Desktop,
            default => self::Other,
        };
    }
}
