<?php

namespace App\Analytics\Enums;

enum OperatingSystem: string
{
    case Windows = 'Windows';
    case MacOS = 'macOS';
    case Linux = 'Linux';
    case Android = 'Android';
    case IOS = 'iOS';
    case ChromeOS = 'ChromeOS';
    case Bot = 'Bot';
    case Other = 'Other';

    public static function normalize(string $family): self
    {
        $family = strtolower(trim($family));

        return match (true) {
            str_contains($family, 'windows') => self::Windows,
            str_contains($family, 'mac os'), str_contains($family, 'macos'), str_contains($family, 'os x') => self::MacOS,
            str_contains($family, 'android') => self::Android,
            str_contains($family, 'ios'), str_contains($family, 'iphone os'), str_contains($family, 'ipad os') => self::IOS,
            str_contains($family, 'chrome os'), str_contains($family, 'cros') => self::ChromeOS,
            str_contains($family, 'linux') => self::Linux,
            default => self::Other,
        };
    }
}
