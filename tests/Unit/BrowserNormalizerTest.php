<?php

namespace Tests\Unit;

use App\Normalizers\BrowserNormalizer;
use PHPUnit\Framework\TestCase;

class BrowserNormalizerTest extends TestCase
{
    public function test_it_removes_platform_and_device_details_from_browser_families(): void
    {
        $normalizer = new BrowserNormalizer;

        $cases = [
            'Firefox iOS' => 'Firefox',
            'Chrome Mobile iOS' => 'Chrome',
            'Chrome Mobile' => 'Chrome',
            'Chrome Mobile WebView' => 'Chrome WebView',
            'Google Chrome Embedded WebView' => 'Chrome WebView',
            'Edge Mobile' => 'Edge',
            'Mobile Safari' => 'Safari',
            'Mobile Safari UI/WKWebView' => 'Safari WebView',
            'Safari Embedded WebView' => 'Safari WebView',
            'DuckDuckGo Mobile' => 'DuckDuckGo',
            'Ecosia Android' => 'Ecosia',
            'QQ Browser Mobile' => 'QQ Browser',
            'Opera Mini' => 'Opera',
        ];

        foreach ($cases as $family => $expected) {
            self::assertSame($expected, $normalizer->normalize($family), $family);
        }
    }

    public function test_it_drops_versions_from_processed_browser_values(): void
    {
        self::assertSame('Firefox', (new BrowserNormalizer)->normalize('Firefox 142.0.1'));
    }

    public function test_it_preserves_unknown_uap_families(): void
    {
        self::assertSame('Ladybird', (new BrowserNormalizer)->normalize('Ladybird'));
    }
}
