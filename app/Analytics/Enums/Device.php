<?php

namespace App\Analytics\Enums;

enum Device: string
{
    case Desktop = 'desktop';
    case Mobile = 'mobile';
    case Tablet = 'tablet';
    case Bot = 'bot';
    case Other = 'other';

    public static function normalize(string $family, OperatingSystem $os, string $userAgent = ''): self
    {
        $family = strtolower(trim($family));
        $userAgent = strtolower($userAgent);

        return match (true) {
            $family === 'spider' => self::Bot,
            str_contains($family, 'ipad'), str_contains($family, 'tablet'), str_contains($userAgent, 'tablet') => self::Tablet,
            str_contains($family, 'iphone'), str_contains($family, 'ipod'), str_contains($userAgent, 'mobile') => self::Mobile,
            $os === OperatingSystem::IOS => self::Mobile,
            $os === OperatingSystem::Android && $family !== 'other' && $family !== '' => self::Mobile,
            in_array($os, [OperatingSystem::Windows, OperatingSystem::MacOS, OperatingSystem::Linux, OperatingSystem::ChromeOS], true) => self::Desktop,
            default => self::Other,
        };
    }
}
