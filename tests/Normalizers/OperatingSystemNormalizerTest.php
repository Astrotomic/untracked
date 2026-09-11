<?php

namespace Tests\Normalizers;

use App\Normalizers\OperatingSystemNormalizer;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class OperatingSystemNormalizerTest extends TestCase
{
    protected function setUp(): void
    {
        $this->normalizer = new OperatingSystemNormalizer;

        parent::setUp();
    }

    #[DataProvider('operatingSystemProvider')]
    public function test_it_collapses_versions_and_variants_to_os_families(string $family, string $expected): void
    {
        Assert::assertSame($expected, $this->normalizer->normalize($family));
    }

    public function test_it_preserves_unknown_uap_families_without_versions(): void
    {
        Assert::assertSame('PartyOS', $this->normalizer->normalize('PartyOS'));
    }

    public static function operatingSystemProvider()
    {
        return [
            ['Windows XP', 'Windows'],
            ['Windows Vista', 'Windows'],
            ['Windows 10', 'Windows'],
            ['Windows Server 2003', 'Windows'],
            ['Mac OS X', 'macOS'],
            ['Mac OS X 10.15.7', 'macOS'],
            ['Android OS', 'Android'],
            ['Chrome OS', 'ChromeOS'],
            ['Ubuntu', 'Linux'],
            ['Debian', 'Linux'],
            ['Fedora', 'Linux'],
            ['Arch Linux', 'Linux'],
            ['BlackBerry Tablet OS', 'BlackBerry OS'],
        ];
    }
}
