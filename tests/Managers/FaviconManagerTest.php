<?php

namespace Tests\Managers;

use App\Drivers\Favicon\DuckDuckGoFaviconDriver;
use App\Managers\FaviconManager;
use PHPUnit\Framework\Assert;
use Tests\TestCase;

class FaviconManagerTest extends TestCase
{
    public function test_it_uses_duckduckgo_by_default(): void
    {
        Assert::assertInstanceOf(
            DuckDuckGoFaviconDriver::class,
            FaviconManager::make()->driver(),
        );
    }
}
