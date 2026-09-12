<?php

namespace Tests\Normalizers;

use App\Normalizers\ValueNormalizer;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ValueNormalizerTest extends TestCase
{
    #[Test]
    #[DataProvider('valueProvider')]
    public function it_normalizes_metric_values(?string $value, ?string $expected): void
    {
        Assert::assertSame($expected, (new ValueNormalizer)->normalize($value));
    }

    public static function valueProvider(): array
    {
        return [
            'plain value' => ['newsletter', 'newsletter'],
            'whitespace' => ['  newsletter  ', 'newsletter'],
            'control characters' => [" \nnews\tletter\x7F ", 'newsletter'],
            'empty' => ['', null],
            'whitespace only' => [" \n\t ", null],
            'missing' => [null, null],
        ];
    }
}
