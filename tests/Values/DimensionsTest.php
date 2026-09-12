<?php

namespace Tests\Values;

use App\Enums\Device;
use App\Enums\Format;
use App\Enums\Metric;
use App\Models\Website;
use App\Values\Client;
use App\Values\Dimensions;
use App\Values\UserAgent;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DimensionsTest extends TestCase
{
    #[Test]
    public function it_normalizes_human_dimensions(): void
    {
        $dimensions = Dimensions::from(
            path: 'https://example.com/blog/example?secret=discarded',
            country: 'de',
            userAgent: UserAgent::from('Firefox iOS', 'Windows XP', Device::Mobile),
            format: Format::Markdown,
            referrer: 'https://www.google.com/search?q=private',
            utmSource: ' newsletter ',
            utmMedium: ' email ',
            utmCampaign: ' launch ',
            utmTerm: ' privacy analytics ',
            utmContent: ' hero-link ',
            website: $this->website(),
        );

        Assert::assertSame('/blog/example', $dimensions->path);
        Assert::assertSame('DE', $dimensions->country);
        Assert::assertSame('Firefox', $dimensions->userAgent->client);
        Assert::assertSame('Windows', $dimensions->userAgent->os);
        Assert::assertSame(Device::Mobile, $dimensions->userAgent->device);
        Assert::assertSame(Format::Markdown, $dimensions->format);
        Assert::assertSame('google.com', $dimensions->referrer);
        Assert::assertSame('newsletter', $dimensions->utmSource);
        Assert::assertSame('email', $dimensions->utmMedium);
        Assert::assertSame('launch', $dimensions->utmCampaign);
        Assert::assertSame('privacy analytics', $dimensions->utmTerm);
        Assert::assertSame('hero-link', $dimensions->utmContent);
    }

    #[Test]
    #[DataProvider('botClientProvider')]
    public function it_keeps_only_bot_identity_dimensions(?string $client, ?string $expectedClient): void
    {
        $dimensions = Dimensions::from(
            path: '/blog/example?utm_source=newsletter',
            country: 'US',
            userAgent: new UserAgent($client, 'Linux', Device::Bot),
            format: Format::Html,
            referrer: 'https://example.org/recommended',
            utmSource: 'newsletter',
            utmMedium: 'email',
            utmCampaign: 'launch',
            utmTerm: 'privacy analytics',
            utmContent: 'hero-link',
            website: $this->website(),
        );

        Assert::assertSame('/blog/example', $dimensions->path);
        Assert::assertNull($dimensions->country);
        Assert::assertSame($expectedClient, $dimensions->userAgent->client);
        Assert::assertNull($dimensions->userAgent->os);
        Assert::assertSame(Device::Bot, $dimensions->userAgent->device);
        Assert::assertSame(Format::Html, $dimensions->format);
        Assert::assertNull($dimensions->referrer);
        Assert::assertNull($dimensions->utmSource);
        Assert::assertNull($dimensions->utmMedium);
        Assert::assertNull($dimensions->utmCampaign);
        Assert::assertNull($dimensions->utmTerm);
        Assert::assertNull($dimensions->utmContent);
    }

    #[Test]
    #[DataProvider('metricProvider')]
    public function it_resolves_metric_values(Metric $metric, ?string $expected): void
    {
        $dimensions = new Dimensions(
            path: '/blog/example',
            country: 'DE',
            userAgent: new UserAgent('Firefox', 'Linux', Device::Desktop),
            format: Format::Markdown,
            referrer: 'google.com',
            utmSource: 'newsletter',
            utmMedium: 'email',
            utmCampaign: 'launch',
            utmTerm: 'privacy analytics',
            utmContent: 'hero-link',
        );

        Assert::assertSame($expected, $dimensions->value($metric));
    }

    #[Test]
    public function it_serializes_all_dimensions(): void
    {
        $dimensions = new Dimensions(
            path: '/',
            country: 'DE',
            userAgent: new UserAgent('Firefox', 'Linux', Device::Desktop),
            format: Format::Html,
            referrer: 'google.com',
            utmSource: 'newsletter',
        );

        $expected = [
            'path' => '/',
            'country' => 'DE',
            'user_agent' => [
                'client' => 'Firefox',
                'os' => 'Linux',
                'device' => Device::Desktop,
                'is_bot' => false,
            ],
            'format' => Format::Html,
            'referrer' => 'google.com',
            'utm_source' => 'newsletter',
            'utm_medium' => null,
            'utm_campaign' => null,
            'utm_term' => null,
            'utm_content' => null,
        ];

        Assert::assertSame($expected, $dimensions->toArray());
        Assert::assertSame($expected, $dimensions->jsonSerialize());
        Assert::assertSame(json_encode($expected, JSON_THROW_ON_ERROR), $dimensions->toJson());
    }

    public static function botClientProvider(): array
    {
        return [
            'known company' => [Client::GOOGLE, Client::GOOGLE],
            'generic bot' => [Client::BOT, null],
            'generic other' => [Client::OTHER, null],
            'empty' => ['', null],
            'missing' => [null, null],
        ];
    }

    public static function metricProvider(): array
    {
        return [
            'path' => [Metric::Path, '/blog/example'],
            'country' => [Metric::Country, 'DE'],
            'client' => [Metric::Client, 'Firefox'],
            'operating system' => [Metric::OperatingSystem, 'Linux'],
            'device' => [Metric::Device, Device::Desktop->value],
            'format' => [Metric::Format, Format::Markdown->value],
            'referrer' => [Metric::Referrer, 'google.com'],
            'utm source' => [Metric::UtmSource, 'newsletter'],
            'utm medium' => [Metric::UtmMedium, 'email'],
            'utm campaign' => [Metric::UtmCampaign, 'launch'],
            'utm term' => [Metric::UtmTerm, 'privacy analytics'],
            'utm content' => [Metric::UtmContent, 'hero-link'],
        ];
    }

    private function website(): Website
    {
        $website = new Website;
        $website->domain = 'example.com';

        return $website;
    }
}
