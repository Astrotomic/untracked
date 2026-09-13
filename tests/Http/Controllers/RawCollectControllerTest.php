<?php

namespace Tests\Http\Controllers;

use App\Enums\Device;
use App\Enums\Format;
use App\Enums\Metric;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Assertions\DailyMetricsAssertions;
use Tests\TestCase;

class RawCollectControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_derives_dimensions_from_the_raw_request(): void
    {
        $website = $this->website();

        $this->withHeader('User-Agent', 'Mozilla/5.0 (X11; Linux x86_64; rv:142.0) Gecko/20100101 Firefox/142.0')
            ->postJson(route('collect.raw', $website), [
                'url' => 'https://example.com/landing?utm_source=newsletter&utm_medium=email&utm_campaign=launch&utm_term=privacy%20analytics&utm_content=hero-link&secret=discarded',
                'referrer' => 'https://www.google.com/search?q=private',
            ])
            ->assertNoContent();

        DailyMetricsAssertions::assertEquals([
            'path' => ['/landing' => 1],
            'client' => ['Firefox' => 1],
            'os' => ['Linux' => 1],
            'device' => [Device::Desktop->value => 1],
            'format' => [Format::Html->value => 1],
            'referrer' => ['google.com' => 1],
            'utm_source' => ['newsletter' => 1],
            'utm_medium' => ['email' => 1],
            'utm_campaign' => ['launch' => 1],
            'utm_term' => ['privacy analytics' => 1],
            'utm_content' => ['hero-link' => 1],
        ], $website);
    }

    #[Test]
    public function it_does_not_persist_disabled_raw_metrics(): void
    {
        $website = $this->website(metricPreferences: [
            Metric::Client->value => false,
            Metric::Referrer->value => false,
            Metric::UtmSource->value => false,
        ]);

        $this->withHeader('User-Agent', 'Mozilla/5.0 (X11; Linux x86_64; rv:142.0) Gecko/20100101 Firefox/142.0')
            ->postJson(route('collect.raw', $website), [
                'url' => 'https://example.com/landing?utm_source=newsletter&utm_medium=email',
                'referrer' => 'https://www.google.com/search?q=private',
            ])
            ->assertNoContent();

        DailyMetricsAssertions::assertEquals([
            'path' => ['/landing' => 1],
            'os' => ['Linux' => 1],
            'device' => [Device::Desktop->value => 1],
            'format' => [Format::Html->value => 1],
            'utm_medium' => ['email' => 1],
        ], $website);
    }

    #[Test]
    public function it_can_skip_bots_before_recording_metrics(): void
    {
        $website = $this->website(shouldTrackBots: false);

        $this->withHeader('User-Agent', 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)')
            ->postJson(route('collect.raw', $website), ['url' => 'https://example.com/'])
            ->assertNoContent();

        DailyMetricsAssertions::assertEquals([], $website);
    }

    #[Test]
    public function it_keeps_only_raw_bot_identity_dimensions(): void
    {
        $website = $this->website();

        $this->withHeader('User-Agent', 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)')
            ->postJson(route('collect.raw', $website), [
                'url' => 'https://example.com/blog/example?utm_source=newsletter&utm_campaign=launch',
                'referrer' => 'https://example.org/recommended',
            ])
            ->assertNoContent();

        DailyMetricsAssertions::assertEquals([
            'path' => ['/blog/example' => 1],
            'client' => ['Google' => 1],
            'device' => [Device::Bot->value => 1],
            'format' => [Format::Html->value => 1],
        ], $website);
    }

    #[Test]
    #[DataProvider('invalidPayloadProvider')]
    public function it_rejects_invalid_payloads(string $field, mixed $value): void
    {
        $website = $this->website();
        $payload = ['url' => 'https://example.com/'];
        $payload[$field] = $value;

        $this->postJson(route('collect.raw', $website), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors($field);

        DailyMetricsAssertions::assertEquals([], $website);
    }

    public static function invalidPayloadProvider(): array
    {
        return [
            'url required' => ['url', null],
            'url valid' => ['url', 'not a url'],
            'url max' => ['url', 'https://example.com/'.str_repeat('x', 2049)],
            'referrer string' => ['referrer', []],
            'referrer max' => ['referrer', str_repeat('x', 2049)],
        ];
    }

    /**
     * @param  array<string, bool>  $metricPreferences
     */
    private function website(bool $shouldTrackBots = true, array $metricPreferences = []): Website
    {
        $preferences = [];

        foreach (Metric::cases() as $metric) {
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
