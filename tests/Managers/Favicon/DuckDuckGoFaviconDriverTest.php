<?php

namespace Managers\Favicon;

use App\Managers\Favicon\DuckDuckGoFaviconDriver;
use Astrotomic\PhpunitAssertions\UrlAssertions;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DuckDuckGoFaviconDriverTest extends TestCase
{
    #[Test]
    public function it_builds_favicon_urls(): void
    {
        $driver = new DuckDuckGoFaviconDriver;
        $favicon = $driver->url('google.com');

        UrlAssertions::assertValidLoose($favicon);
        Assert::assertSame(
            'https://icons.duckduckgo.com/ip3/google.com.ico',
            $favicon
        );
    }
}
