<?php

namespace Tests\Drivers\Favicon;

use App\Drivers\Favicon\GoogleFaviconDriver;
use PHPUnit\Framework\Assert;
use Tests\TestCase;

class GoogleFaviconDriverTest extends TestCase
{
    public function test_it_builds_favicon_urls(): void
    {
        Assert::assertSame(
            'https://t1.gstatic.com/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE%2CSIZE%2CURL&url=https%3A%2F%2Fgoogle.com&size=128',
            (new GoogleFaviconDriver)->url('google.com'),
        );
    }
}
