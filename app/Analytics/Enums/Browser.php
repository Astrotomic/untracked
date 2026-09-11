<?php

namespace App\Analytics\Enums;

enum Browser: string
{
    case Chrome = 'Chrome';
    case Firefox = 'Firefox';
    case Safari = 'Safari';
    case Edge = 'Edge';
    case Opera = 'Opera';
    case SamsungInternet = 'Samsung Internet';
    case Bot = 'Bot';
    case Other = 'Other';

    public static function normalize(string $family): self
    {
        $family = strtolower(trim($family));

        return match (true) {
            str_contains($family, 'chrome'), $family === 'chromium' => self::Chrome,
            str_contains($family, 'firefox') => self::Firefox,
            str_contains($family, 'safari') => self::Safari,
            str_contains($family, 'edge'), str_starts_with($family, 'edg') => self::Edge,
            str_contains($family, 'opera'), str_contains($family, 'opera mini'), str_contains($family, 'opera mobile') => self::Opera,
            str_contains($family, 'samsung') => self::SamsungInternet,
            default => self::Other,
        };
    }
}
