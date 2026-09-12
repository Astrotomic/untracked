<?php

namespace Tests\Drivers\Favicon;

use App\Drivers\Favicon\UnavatarFaviconDriver;
use PHPUnit\Framework\Assert;
use Tests\TestCase;

class UnavatarFaviconDriverTest extends TestCase
{
    public function test_it_builds_favicon_urls(): void
    {
        Assert::assertSame(
            'https://unavatar.io/google.com',
            (new UnavatarFaviconDriver)->url('google.com'),
        );
    }
}
