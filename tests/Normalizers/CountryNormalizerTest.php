<?php

namespace Tests\Normalizers;

use App\Normalizers\CountryNormalizer;
use Illuminate\Support\Str;
use League\ISO3166\ISO3166;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CountryNormalizerTest extends TestCase
{
    protected function setUp(): void
    {
        $this->normalizer = new CountryNormalizer(new ISO3166);

        parent::setUp();
    }

    #[DataProvider('countryProvider')]
    public function test_it_normalizes_country_values(string $value, ?string $expected): void
    {
        Assert::assertSame($expected, $this->normalizer->normalize(Str::lower($value)));
        Assert::assertSame($expected, $this->normalizer->normalize(Str::upper($value)));
    }

    public static function countryProvider(): array
    {
        return [
            ['de', 'DE'],
            ['deu', 'DE'],
            ['276', 'DE'],
            ['germany', 'DE'],
            ['foobar', null],
        ];
    }
}
