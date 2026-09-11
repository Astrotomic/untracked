<?php

namespace Providers;

use Illuminate\Support\Str;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StringMacroTest extends TestCase
{
    #[DataProvider('stringComparisonProvider')]
    public function test_compares_strings_for_equality(string $a, string $b, bool $expected): void
    {
        Assert::assertSame($expected, Str::equals($a, $b));
    }

    public static function stringComparisonProvider(): array
    {
        return [
            ['Bot', 'bot', true],
            ['É', 'é', true],
            ['É', "e\u{301}", true],
            ['é', "e\u{301}", true],
            ['foo', 'bar', false],
        ];
    }
}
