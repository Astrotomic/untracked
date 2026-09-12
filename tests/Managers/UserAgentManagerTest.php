<?php

namespace Tests\Managers;

use App\Managers\UserAgent\UapUserAgentDriver;
use App\Managers\UserAgentManager;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserAgentManagerTest extends TestCase
{
    #[Test]
    public function it_uses_uap_by_default(): void
    {
        Assert::assertSame('uap', UserAgentManager::make()->getDefaultDriver());
        Assert::assertInstanceOf(UapUserAgentDriver::class, UserAgentManager::make()->driver());
    }
}
