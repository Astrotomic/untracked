<?php

namespace Tests\Normalizers;

use App\Normalizers\OperatingSystemNormalizer;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OperatingSystemNormalizerTest extends TestCase
{
    private OperatingSystemNormalizer $normalizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->normalizer = new OperatingSystemNormalizer;
    }

    #[Test]
    #[DataProvider('operatingSystemProvider')]
    public function it_normalizes_operating_system_families(string $family, string $expected): void
    {
        Assert::assertSame($expected, $this->normalizer->normalize($family));
    }

    public static function operatingSystemProvider(): array
    {
        return [
            'windows xp' => ['Windows XP', 'Windows'],
            'windows vista' => ['Windows Vista', 'Windows'],
            'windows 10' => ['Windows 10', 'Windows'],
            'windows server' => ['Windows Server 2003', 'Windows'],
            'mac os' => ['Mac OS X', 'macOS'],
            'mac os version' => ['Mac OS X 10.15.7', 'macOS'],
            'android' => ['Android OS', 'Android'],
            'chrome os' => ['Chrome OS', 'ChromeOS'],
            'ubuntu' => ['Ubuntu', 'Linux'],
            'debian' => ['Debian', 'Linux'],
            'fedora' => ['Fedora', 'Linux'],
            'arch linux' => ['Arch Linux', 'Linux'],
            'blackberry' => ['BlackBerry Tablet OS', 'BlackBerry OS'],
            'unknown family' => ['PartyOS', 'PartyOS'],
        ];
    }
}
