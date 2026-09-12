<?php

namespace Tests\Values;

use App\Enums\Device;
use App\Values\UserAgent;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserAgentTest extends TestCase
{
    #[Test]
    public function it_normalizes_processed_user_agent_values(): void
    {
        $userAgent = UserAgent::from(
            client: 'Firefox iOS',
            os: 'Windows XP',
            device: Device::Mobile->value,
        );

        Assert::assertSame('Firefox', $userAgent->client);
        Assert::assertSame('Windows', $userAgent->os);
        Assert::assertSame(Device::Mobile, $userAgent->device);
        Assert::assertFalse($userAgent->isBot());
    }

    #[Test]
    #[DataProvider('deviceProvider')]
    public function it_identifies_bots_by_device(Device $device, bool $expected): void
    {
        $userAgent = new UserAgent(null, null, $device);

        Assert::assertSame($expected, $userAgent->isBot());
    }

    public static function deviceProvider(): array
    {
        return [
            'desktop' => [Device::Desktop, false],
            'mobile' => [Device::Mobile, false],
            'tablet' => [Device::Tablet, false],
            'bot' => [Device::Bot, true],
            'other' => [Device::Other, false],
        ];
    }
}
