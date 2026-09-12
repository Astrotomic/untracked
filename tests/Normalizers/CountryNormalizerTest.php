<?php

namespace Tests\Normalizers;

use App\Normalizers\CountryNormalizer;
use Astrotomic\PhpunitAssertions\CountryAssertions;
use Astrotomic\PhpunitAssertions\NullableTypeAssertions;
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
    public function test_it_normalizes_lowercase_country_values(string $value, ?string $expected): void
    {
        $country = $this->normalizer->normalize(Str::lower($value));

        NullableTypeAssertions::assertIsNullableString($country);
        if (is_string($expected)) {
            CountryAssertions::assertAlpha2($country);
        }
        Assert::assertSame($expected, $country);
    }

    #[DataProvider('countryProvider')]
    public function test_it_normalizes_uppercase_country_values(string $value, ?string $expected): void
    {
        $country = $this->normalizer->normalize(Str::upper($value));

        NullableTypeAssertions::assertIsNullableString($country);
        if (is_string($expected)) {
            CountryAssertions::assertAlpha2($country);
        }
        Assert::assertSame($expected, $country);
    }

    public static function countryProvider(): array
    {
        return [
            ['de', 'DE'],
            ['deu', 'DE'],
            ['276', 'DE'],
            ['germany', 'DE'],
            ['xx', null],
            ['000', null],
            ['foobar', null],
        ];
    }
}
