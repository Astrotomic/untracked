<?php

namespace Tests\Managers;

use App\Managers\Ip\IpApiDriver;
use App\Managers\Ip\MaxMindIpDriver;
use App\Managers\IpManager;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IpManagerTest extends TestCase
{
    #[Test]
    public function it_uses_maxmind_by_default(): void
    {
        Assert::assertSame('maxmind', IpManager::make()->getDefaultDriver());
        Assert::assertInstanceOf(MaxMindIpDriver::class, IpManager::make()->driver());
    }

    #[Test]
    #[DataProvider('driverProvider')]
    public function it_resolves_configured_drivers(string $driver, string $expected): void
    {
        Assert::assertInstanceOf($expected, IpManager::make()->driver($driver));
    }

    public static function driverProvider(): array
    {
        return [
            'maxmind' => ['maxmind', MaxMindIpDriver::class],
            'ip api' => ['ip-api', IpApiDriver::class],
        ];
    }
}
