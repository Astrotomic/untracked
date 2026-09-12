<?php

namespace Tests\Normalizers;

use App\Normalizers\ClientNormalizer;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ClientNormalizerTest extends TestCase
{
    private ClientNormalizer $normalizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->normalizer = new ClientNormalizer;
    }

    #[Test]
    #[DataProvider('clientProvider')]
    public function it_normalizes_client_families(string $family, string $expected): void
    {
        Assert::assertSame($expected, $this->normalizer->normalize($family));
    }

    public static function clientProvider(): array
    {
        return [
            'firefox ios' => ['Firefox iOS', 'Firefox'],
            'chrome mobile ios' => ['Chrome Mobile iOS', 'Chrome'],
            'chrome mobile' => ['Chrome Mobile', 'Chrome'],
            'chrome mobile webview' => ['Chrome Mobile WebView', 'Chrome WebView'],
            'google chrome embedded webview' => ['Google Chrome Embedded WebView', 'Chrome WebView'],
            'edge mobile' => ['Edge Mobile', 'Edge'],
            'mobile safari' => ['Mobile Safari', 'Safari'],
            'mobile safari webview' => ['Mobile Safari UI/WKWebView', 'Safari WebView'],
            'safari embedded webview' => ['Safari Embedded WebView', 'Safari WebView'],
            'duckduckgo mobile' => ['DuckDuckGo Mobile', 'DuckDuckGo'],
            'ecosia android' => ['Ecosia Android', 'Ecosia'],
            'qq browser mobile' => ['QQ Browser Mobile', 'QQ Browser'],
            'opera mini' => ['Opera Mini', 'Opera'],
            'version suffix' => ['Firefox 142.0.1', 'Firefox'],
            'unknown family' => ['Ladybird', 'Ladybird'],
        ];
    }
}
