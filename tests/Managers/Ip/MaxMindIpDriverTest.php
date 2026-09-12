<?php

namespace Managers\Ip;

use App\Managers\Ip\MaxMindIpDriver;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MaxMindIpDriverTest extends TestCase
{
    #[Test]
    public function it_returns_null_when_the_database_is_unavailable(): void
    {
        Assert::assertNull((new MaxMindIpDriver('/missing/GeoLite2-Country.mmdb'))->country('203.0.113.10'));
    }
}
