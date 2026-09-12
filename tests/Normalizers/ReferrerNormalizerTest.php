<?php

namespace Tests\Normalizers;

use App\Normalizers\ReferrerNormalizer;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReferrerNormalizerTest extends TestCase
{
    #[Test]
    #[DataProvider('referrerProvider')]
    public function it_normalizes_referrers(?string $value, ?string $websiteDomain, ?string $expected): void
    {
        Assert::assertSame($expected, (new ReferrerNormalizer)->normalize($value, $websiteDomain));
    }

    public static function referrerProvider(): array
    {
        return [
            'full url' => ['https://www.google.com/search?q=private', 'example.com', 'google.com'],
            'host only' => ['www.google.com', 'example.com', 'google.com'],
            'case and trailing dot' => ['HTTPS://WWW.GOOGLE.COM./search', 'example.com', 'google.com'],
            'same site' => ['https://www.example.com/from-here', 'example.com', null],
            'same site normalized' => ['EXAMPLE.COM.', 'https://www.example.com', null],
            'no website domain' => ['https://example.com/path', null, 'example.com'],
            'empty' => ['', 'example.com', null],
            'missing' => [null, 'example.com', null],
        ];
    }
}
