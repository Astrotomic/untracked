<?php

namespace Tests\Providers;

use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AppServiceProviderTest extends TestCase
{
    #[Test]
    public function it_registers_string_macros(): void
    {
        Assert::assertTrue(Str::hasMacro('equals'));
        Assert::assertTrue(Stringable::hasMacro('normalize'));
    }

    #[Test]
    #[DataProvider('stringComparisonProvider')]
    public function it_compares_strings_for_equality(string $a, string $b, bool $expected): void
    {
        Assert::assertSame($expected, Str::equals($a, $b));
    }

    #[Test]
    public function it_normalizes_unicode_strings(): void
    {
        Assert::assertSame('é', (string) Str::of("e\u{301}")->normalize());
    }

    public static function stringComparisonProvider(): array
    {
        return [
            'case insensitive' => ['Bot', 'bot', true],
            'precomposed unicode' => ['É', 'é', true],
            'decomposed unicode' => ['É', "e\u{301}", true],
            'normalized unicode' => ['é', "e\u{301}", true],
            'different strings' => ['foo', 'bar', false],
        ];
    }
}
