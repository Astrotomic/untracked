<?php

namespace Managers\Favicon;

use App\Managers\Favicon\GoogleFaviconDriver;
use Astrotomic\PhpunitAssertions\UrlAssertions;
use PHPUnit\Framework\Assert;
use Tests\TestCase;

class GoogleFaviconDriverTest extends TestCase
{
    public function test_it_builds_favicon_urls(): void
    {
        $driver = new GoogleFaviconDriver;
        $favicon = $driver->url('google.com');

        UrlAssertions::assertValidLoose($favicon);
        Assert::assertSame(
            'https://t1.gstatic.com/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE%2CSIZE%2CURL&url=https%3A%2F%2Fgoogle.com&size=128',
            $favicon
        );
    }
}
