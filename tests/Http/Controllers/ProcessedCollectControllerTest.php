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

class ProcessedCollectControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_records_normalized_processed_dimensions(): void
    {
        $website = $this->website();

        $this->postJson(route('collect.processed', $website), [
            'path' => '/landing?secret=discarded',
            'country' => 'de',
            'client' => 'Firefox iOS',
            'os' => 'Windows XP',
            'device' => Device::Mobile->value,
            'format' => Format::Markdown->value,
            'referrer' => 'https://www.google.com/search?q=private',
            'utm_source' => 'newsletter',
            'utm_medium' => 'email',
            'utm_campaign' => 'launch',
            'utm_term' => 'privacy analytics',
            'utm_content' => 'hero-link',
        ])->assertNoContent();

        DailyMetricsAssertions::assertEquals([
            'path' => ['/landing' => 1],
            'country' => ['DE' => 1],
            'client' => ['Firefox' => 1],
            'os' => ['Windows' => 1],
            'device' => [Device::Mobile->value => 1],
            'format' => [Format::Markdown->value => 1],
            'referrer' => ['google.com' => 1],
            'utm_source' => ['newsletter' => 1],
            'utm_medium' => ['email' => 1],
            'utm_campaign' => ['launch' => 1],
            'utm_term' => ['privacy analytics' => 1],
            'utm_content' => ['hero-link' => 1],
        ], $website);
    }

    #[Test]
    public function it_does_not_persist_disabled_processed_metrics(): void
    {
        $website = $this->website(metricPreferences: [
            Metric::Country->value => false,
            Metric::OperatingSystem->value => false,
            Metric::UtmCampaign->value => false,
        ]);

        $this->postJson(route('collect.processed', $website), [
            'path' => '/landing',
            'country' => 'de',
            'client' => 'Firefox',
            'os' => 'Windows',
            'device' => Device::Desktop->value,
            'format' => Format::Html->value,
            'utm_campaign' => 'launch',
        ])->assertNoContent();

        DailyMetricsAssertions::assertEquals([
            'path' => ['/landing' => 1],
            'client' => ['Firefox' => 1],
            'device' => [Device::Desktop->value => 1],
            'format' => [Format::Html->value => 1],
        ], $website);
    }

    #[Test]
    public function it_accepts_missing_optional_dimensions(): void
    {
        $website = $this->website();

        $this->postJson(route('collect.processed', $website), [
            'path' => '/',
            'device' => Device::Desktop->value,
            'format' => Format::Html->value,
        ])->assertNoContent();

        DailyMetricsAssertions::assertEquals([
            'path' => ['/' => 1],
            'device' => [Device::Desktop->value => 1],
            'format' => [Format::Html->value => 1],
        ], $website);
    }

    #[Test]
    public function it_keeps_only_bot_identity_dimensions(): void
    {
        $website = $this->website();

        $this->postJson(route('collect.processed', $website), [
            'path' => '/blog/example?utm_source=newsletter',
            'country' => 'US',
            'client' => 'OpenAI',
            'os' => 'Linux',
            'device' => Device::Bot->value,
            'format' => Format::Markdown->value,
            'referrer' => 'https://example.org/recommended',
            'utm_source' => 'newsletter',
            'utm_medium' => 'email',
            'utm_campaign' => 'launch',
            'utm_term' => 'privacy analytics',
            'utm_content' => 'hero-link',
        ])->assertNoContent();

        DailyMetricsAssertions::assertEquals([
            'path' => ['/blog/example' => 1],
            'client' => ['OpenAI' => 1],
            'device' => [Device::Bot->value => 1],
            'format' => [Format::Markdown->value => 1],
        ], $website);
    }

    #[Test]
    #[DataProvider('invalidPayloadProvider')]
    public function it_rejects_invalid_payloads(string $field, mixed $value): void
    {
        $website = $this->website();
        $payload = [
            'path' => '/',
            'device' => Device::Desktop->value,
            'format' => Format::Html->value,
        ];
        $payload[$field] = $value;

        $this->postJson(route('collect.processed', $website), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors($field);

        DailyMetricsAssertions::assertEquals([], $website);
    }

    public static function invalidPayloadProvider(): array
    {
        return [
            'path required' => ['path', null],
            'path string' => ['path', []],
            'path max' => ['path', str_repeat('x', 2049)],
            'country string' => ['country', []],
            'country alpha-2' => ['country', 'DEU'],
            'client string' => ['client', []],
            'client max' => ['client', str_repeat('x', 101)],
            'operating system string' => ['os', []],
            'operating system max' => ['os', str_repeat('x', 101)],
            'device required' => ['device', null],
            'device enum' => ['device', 'laptop'],
            'format required' => ['format', null],
            'format enum' => ['format', 'pdf'],
            'referrer string' => ['referrer', []],
            'referrer max' => ['referrer', str_repeat('x', 2049)],
            'utm source string' => ['utm_source', []],
            'utm source max' => ['utm_source', str_repeat('x', 501)],
            'utm medium string' => ['utm_medium', []],
            'utm medium max' => ['utm_medium', str_repeat('x', 501)],
            'utm campaign string' => ['utm_campaign', []],
            'utm campaign max' => ['utm_campaign', str_repeat('x', 501)],
            'utm term string' => ['utm_term', []],
            'utm term max' => ['utm_term', str_repeat('x', 501)],
            'utm content string' => ['utm_content', []],
            'utm content max' => ['utm_content', str_repeat('x', 501)],
        ];
    }

    /**
     * @param  array<string, bool>  $metricPreferences
     */
    private function website(array $metricPreferences = []): Website
    {
        $preferences = [];

        foreach (Metric::cases() as $metric) {
            $preferences[$metric->value] = true;
        }

        return Website::query()->create([
            'name' => 'Example',
            'domain' => 'example.com',
            'timezone' => 'UTC',
            'should_track_bots' => true,
            'metric_preferences' => array_replace($preferences, $metricPreferences),
        ]);
    }
}
