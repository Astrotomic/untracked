<?php

namespace Managers\Favicon;

use App\Managers\Favicon\LogoDevFaviconDriver;
use Astrotomic\PhpunitAssertions\UrlAssertions;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class LogoDevFaviconDriverTest extends TestCase
{
    #[Test]
    public function it_builds_favicon_urls(): void
    {
        $driver = new LogoDevFaviconDriver('secret');
        $favicon = $driver->url('google.com');

        UrlAssertions::assertValidLoose($favicon);
        Assert::assertSame(
            'https://img.logo.dev/google.com?token=secret&size=128&format=png&retina=true&fallback=monogram',
            $favicon,
        );
    }

    #[Test]
    public function it_requires_a_token(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The Logo.dev favicon driver requires LOGO_DEV_TOKEN.');

        new LogoDevFaviconDriver('');
    }
}
