<?php

namespace Managers\Favicon;

use App\Managers\Favicon\UnavatarFaviconDriver;
use Astrotomic\PhpunitAssertions\UrlAssertions;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UnavatarFaviconDriverTest extends TestCase
{
    #[Test]
    public function it_builds_favicon_urls(): void
    {
        $driver = new UnavatarFaviconDriver('secret');
        $favicon = $driver->url('google.com');

        UrlAssertions::assertValidLoose($favicon);
        Assert::assertSame(
            'https://unavatar.io/google.com',
            $favicon
        );
    }
}
