<?php

namespace Tests\Managers;

use App\Managers\Favicon\DuckDuckGoFaviconDriver;
use App\Managers\Favicon\GoogleFaviconDriver;
use App\Managers\Favicon\LogoDevFaviconDriver;
use App\Managers\Favicon\UnavatarFaviconDriver;
use App\Managers\FaviconManager;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FaviconManagerTest extends TestCase
{
    #[Test]
    public function it_uses_duckduckgo_by_default(): void
    {
        Assert::assertSame('duckduckgo', FaviconManager::make()->getDefaultDriver());
        Assert::assertInstanceOf(DuckDuckGoFaviconDriver::class, FaviconManager::make()->driver());
    }

    #[Test]
    #[DataProvider('driverProvider')]
    public function it_resolves_configured_drivers(string $driver, string $expected): void
    {
        config()->set('favicons.drivers.logo_dev.token', 'token');

        Assert::assertInstanceOf($expected, FaviconManager::make()->driver($driver));
    }

    public static function driverProvider(): array
    {
        return [
            'duckduckgo' => ['duckduckgo', DuckDuckGoFaviconDriver::class],
            'google' => ['gstatic', GoogleFaviconDriver::class],
            'unavatar' => ['unavatar', UnavatarFaviconDriver::class],
            'logo.dev' => ['logo_dev', LogoDevFaviconDriver::class],
        ];
    }
}
