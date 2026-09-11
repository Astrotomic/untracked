<?php

namespace Tests\Unit;

use App\Analytics\Normalizers\OperatingSystemNormalizer;
use PHPUnit\Framework\TestCase;

class OperatingSystemNormalizerTest extends TestCase
{
    public function test_it_collapses_versions_and_variants_to_os_families(): void
    {
        $normalizer = new OperatingSystemNormalizer;

        $cases = [
            'Windows XP' => 'Windows',
            'Windows Vista' => 'Windows',
            'Windows 10' => 'Windows',
            'Windows Server 2003' => 'Windows',
            'Mac OS X' => 'macOS',
            'Mac OS X 10.15.7' => 'macOS',
            'Android OS' => 'Android',
            'Chrome OS' => 'ChromeOS',
            'Ubuntu' => 'Linux',
            'Debian' => 'Linux',
            'Fedora' => 'Linux',
            'Arch Linux' => 'Linux',
            'BlackBerry Tablet OS' => 'BlackBerry OS',
        ];

        foreach ($cases as $family => $expected) {
            self::assertSame($expected, $normalizer->normalize($family), $family);
        }
    }

    public function test_it_preserves_unknown_uap_families_without_versions(): void
    {
        self::assertSame('KaiOS', (new OperatingSystemNormalizer)->normalize('KaiOS'));
    }
}
