<?php

namespace Tests\Normalizers;

use App\Normalizers\ClientNormalizer;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ClientNormalizerTest extends TestCase
{
    protected function setUp(): void
    {
        $this->normalizer = new ClientNormalizer;

        parent::setUp();
    }

    #[DataProvider('clientProvider')]
    public function test_it_removes_platform_and_device_details_from_client_families(string $family, string $expected): void
    {
        Assert::assertSame($expected, $this->normalizer->normalize($family));
    }

    public function test_it_drops_versions_from_processed_client_values(): void
    {
        Assert::assertSame('Firefox', $this->normalizer->normalize('Firefox 142.0.1'));
    }

    public function test_it_preserves_unknown_uap_families(): void
    {
        Assert::assertSame('Ladybird', $this->normalizer->normalize('Ladybird'));
    }

    public static function clientProvider(): array
    {
        return [
            ['Firefox iOS', 'Firefox'],
            ['Chrome Mobile iOS', 'Chrome'],
            ['Chrome Mobile', 'Chrome'],
            ['Chrome Mobile WebView', 'Chrome WebView'],
            ['Google Chrome Embedded WebView', 'Chrome WebView'],
            ['Edge Mobile', 'Edge'],
            ['Mobile Safari', 'Safari'],
            ['Mobile Safari UI/WKWebView', 'Safari WebView'],
            ['Safari Embedded WebView', 'Safari WebView'],
            ['DuckDuckGo Mobile', 'DuckDuckGo'],
            ['Ecosia Android', 'Ecosia'],
            ['QQ Browser Mobile', 'QQ Browser'],
            ['Opera Mini', 'Opera'],
        ];
    }
}
