<?php

namespace Tests\Drivers\Favicon;

use App\Drivers\Favicon\DuckDuckGoFaviconDriver;
use PHPUnit\Framework\Assert;
use Tests\TestCase;

class DuckDuckGoFaviconDriverTest extends TestCase
{
    public function test_it_builds_favicon_urls(): void
    {
        Assert::assertSame(
            'https://icons.duckduckgo.com/ip3/google.com.ico',
            (new DuckDuckGoFaviconDriver)->url('google.com'),
        );
    }
}
