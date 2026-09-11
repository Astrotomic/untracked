<?php

namespace Tests\Drivers\Favicon;

use App\Drivers\Favicon\LogoDevFaviconDriver;
use PHPUnit\Framework\Assert;
use RuntimeException;
use Tests\TestCase;

class LogoDevFaviconDriverTest extends TestCase
{
    public function test_it_builds_favicon_urls(): void
    {
        Assert::assertSame(
            'https://img.logo.dev/google.com?token=secret&size=128&format=png&retina=true&fallback=monogram',
            (new LogoDevFaviconDriver('secret'))->url('google.com'),
        );
    }

    public function test_it_requires_a_token(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The Logo.dev favicon driver requires LOGO_DEV_TOKEN.');

        new LogoDevFaviconDriver('');
    }
}
