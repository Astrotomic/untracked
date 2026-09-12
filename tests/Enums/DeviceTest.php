<?php

namespace Tests\Enums;

use App\Enums\Device;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeviceTest extends TestCase
{
    #[Test]
    #[DataProvider('deviceProvider')]
    public function it_normalizes_device_families(string $family, string $os, string $userAgent, Device $expected): void
    {
        Assert::assertSame($expected, Device::normalize($family, $os, $userAgent));
    }

    public static function deviceProvider(): array
    {
        return [
            'bot family' => ['Spider', 'Linux', '', Device::Bot],
            'bot user agent' => ['Other', 'Linux', 'Googlebot/2.1', Device::Bot],
            'tablet family' => ['iPad', 'iOS', '', Device::Tablet],
            'tablet user agent' => ['Other', 'Android', 'Tablet', Device::Tablet],
            'mobile family' => ['iPhone', 'iOS', '', Device::Mobile],
            'mobile user agent' => ['Other', 'Unknown', 'Mobile', Device::Mobile],
            'mobile os' => ['Other', 'Android', '', Device::Mobile],
            'desktop os' => ['Other', 'Linux', '', Device::Desktop],
            'unknown' => ['Other', 'Unknown', '', Device::Other],
        ];
    }
}
