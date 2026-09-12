<?php

namespace Tests\Normalizers;

use App\Normalizers\PathNormalizer;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PathNormalizerTest extends TestCase
{
    #[Test]
    #[DataProvider('pathProvider')]
    public function it_normalizes_paths(?string $value, string $expected): void
    {
        Assert::assertSame($expected, (new PathNormalizer)->normalize($value));
    }

    public static function pathProvider(): array
    {
        return [
            'full url' => ['https://example.com/blog/post?utm_source=test#fragment', '/blog/post'],
            'relative path' => ['blog/post/', '/blog/post'],
            'extra slashes' => ['/blog/post///', '/blog/post'],
            'root' => ['/', '/'],
            'query only' => ['?utm_source=test', '/'],
            'empty' => ['', '/'],
            'missing' => [null, '/'],
        ];
    }
}
