<?php

namespace Tests\Models;

use App\Enums\Device;
use App\Enums\Format;
use App\Enums\Metric;
use App\Models\Website;
use App\Values\Dimensions;
use App\Values\UserAgent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\Assertions\DailyMetricsAssertions;
use Tests\TestCase;

class WebsiteTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_records_each_available_dimension_as_an_independent_counter(): void
    {
        $website = $this->website();
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

        Assert::assertSame($dimensions, $website->record($dimensions));
        Assert::assertSame($dimensions, $website->record($dimensions));

        DailyMetricsAssertions::assertEquals([
            'path' => ['/blog/example' => 2],
            'country' => ['DE' => 2],
            'client' => ['Firefox' => 2],
            'os' => ['Linux' => 2],
            'device' => [Device::Desktop->value => 2],
            'format' => [Format::Markdown->value => 2],
            'referrer' => ['google.com' => 2],
            'utm_source' => ['newsletter' => 2],
            'utm_medium' => ['email' => 2],
            'utm_campaign' => ['launch' => 2],
            'utm_term' => ['privacy analytics' => 2],
            'utm_content' => ['hero-link' => 2],
        ], $website);
    }

    #[Test]
    public function it_does_not_persist_disabled_metrics(): void
    {
        $website = $this->website(metricPreferences: [
            Metric::Path->value => false,
            Metric::Country->value => false,
            Metric::Client->value => false,
            Metric::UtmCampaign->value => false,
        ]);

        Assert::assertTrue($website->isTracking(Metric::Path));

        $website->record(new Dimensions(
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
        ));

        DailyMetricsAssertions::assertEquals([
            'path' => ['/blog/example' => 1],
            'os' => ['Linux' => 1],
            'device' => [Device::Desktop->value => 1],
            'format' => [Format::Markdown->value => 1],
            'referrer' => ['google.com' => 1],
            'utm_source' => ['newsletter' => 1],
            'utm_medium' => ['email' => 1],
            'utm_term' => ['privacy analytics' => 1],
            'utm_content' => ['hero-link' => 1],
        ], $website);
    }

    #[Test]
    public function it_records_only_dimensions_with_values(): void
    {
        $website = $this->website();

        $website->record(new Dimensions(
            path: '/',
            country: null,
            userAgent: new UserAgent(null, null, Device::Desktop),
            format: Format::Html,
        ));

        DailyMetricsAssertions::assertEquals([
            'path' => ['/' => 1],
            'device' => [Device::Desktop->value => 1],
            'format' => [Format::Html->value => 1],
        ], $website);
    }

    #[Test]
    public function it_skips_bots_when_bot_tracking_is_disabled(): void
    {
        $website = $this->website(shouldTrackBots: false);
        $dimensions = new Dimensions(
            path: '/',
            country: null,
            userAgent: new UserAgent('Google', null, Device::Bot),
            format: Format::Html,
        );

        Assert::assertSame($dimensions, $website->record($dimensions));
        DailyMetricsAssertions::assertEquals([], $website);
    }

    /**
     * @param  array<string, bool>  $metricPreferences
     */
    private function website(bool $shouldTrackBots = true, array $metricPreferences = []): Website
    {
        $preferences = [];

        foreach (Metric::cases() as $metric) {
            if (! $metric->isConfigurable()) {
                continue;
            }

            $preferences[$metric->value] = true;
        }

        return Website::query()->create([
            'name' => 'Example',
            'domain' => 'example.com',
            'timezone' => 'UTC',
            'should_track_bots' => $shouldTrackBots,
            'metric_preferences' => array_replace($preferences, $metricPreferences),
        ]);
    }
}
