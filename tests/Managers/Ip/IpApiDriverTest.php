<?php

namespace Managers\Ip;

use App\Managers\Ip\IpApiDriver;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IpApiDriverTest extends TestCase
{
    #[Test]
    public function it_resolves_and_normalizes_country_codes(): void
    {
        Http::fake([
            'https://ip-api.test/json/203.0.113.10*' => Http::response([
                'status' => 'success',
                'countryCode' => 'de',
            ]),
        ]);

        Assert::assertSame('DE', (new IpApiDriver('https://ip-api.test/json/', 2))->country('203.0.113.10'));

        Http::assertSent(static function (Request $request): bool {
            $url = parse_url($request->url());

            Assert::assertIsArray($url);
            Assert::assertArrayHasKey('scheme', $url);
            Assert::assertArrayHasKey('host', $url);
            Assert::assertArrayHasKey('path', $url);
            Assert::assertArrayHasKey('query', $url);
            Assert::assertSame('https', $url['scheme']);
            Assert::assertSame('ip-api.test', $url['host']);
            Assert::assertSame('/json/203.0.113.10', $url['path']);
            Assert::assertSame('GET', $request->method());

            parse_str($url['query'], $query);

            Assert::assertSame(['fields' => 'status,countryCode'], $query);
            Assert::assertTrue($request->hasHeader('Accept', 'application/json'));

            return true;
        });
        Http::assertSentCount(1);
    }

    #[Test]
    public function it_returns_null_for_failed_api_responses(): void
    {
        Http::fake([
            '*' => Http::response([
                'status' => 'fail',
                'countryCode' => null,
            ]),
        ]);

        Assert::assertNull((new IpApiDriver('https://ip-api.test/json', 2))->country('203.0.113.10'));
        Http::assertSentCount(1);
    }

    #[Test]
    public function it_returns_null_for_http_failures(): void
    {
        Http::fake([
            '*' => Http::response(status: 500),
        ]);

        Assert::assertNull((new IpApiDriver('https://ip-api.test/json', 2))->country('203.0.113.10'));
        Http::assertSentCount(1);
    }
}
