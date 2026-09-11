<?php

namespace Tests;

use App\Enums\Device;
use App\Enums\Format;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

class CollectMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_processed_collection_stores_only_independent_daily_counters(): void
    {
        $website = $this->website();

        $payload = [
            'path' => '/blog/example?utm_source=test',
            'country' => 'de',
            'browser' => 'Firefox',
            'os' => 'Linux',
            'device' => Device::Desktop->value,
            'format' => Format::Markdown->value,
        ];

        $this->postJson(route('collect.processed', $website), $payload)->assertNoContent();
        $this->postJson(route('collect.processed', $website), $payload)->assertNoContent();

        $this->assertDatabaseCount('daily_metrics', 6);
        $this->assertDatabaseHas('daily_metrics', [
            'website_uuid' => $website->getKey(),
            'metric' => 'path',
            'value' => '/blog/example',
            'count' => 2,
        ]);
        $this->assertDatabaseHas('daily_metrics', [
            'website_uuid' => $website->getKey(),
            'metric' => 'country',
            'value' => 'DE',
            'count' => 2,
        ]);
        $this->assertDatabaseMissing('daily_metrics', ['metric' => 'utm_source']);

        $this->assertFalse(Schema::hasColumn('daily_metrics', 'created_at'));
        $this->assertFalse(Schema::hasColumn('daily_metrics', 'updated_at'));
    }

    public function test_processed_collection_stores_attribution_as_independent_optional_counters(): void
    {
        $website = $this->website();

        $this->postJson(route('collect.processed', $website), [
            'path' => '/landing?secret=discarded',
            'country' => 'DE',
            'browser' => 'Firefox iOS',
            'os' => 'iOS',
            'device' => Device::Mobile->value,
            'format' => Format::Html->value,
            'referrer' => 'https://www.google.com/search?q=private',
            'utm_source' => 'newsletter',
            'utm_medium' => 'email',
            'utm_campaign' => 'launch',
            'utm_term' => 'privacy analytics',
            'utm_content' => 'hero-link',
        ])->assertNoContent();

        $this->assertDatabaseCount('daily_metrics', 12);
        $this->assertDatabaseHas('daily_metrics', ['metric' => 'path', 'value' => '/landing']);
        $this->assertDatabaseHas('daily_metrics', ['metric' => 'referrer', 'value' => 'google.com']);
        $this->assertDatabaseHas('daily_metrics', ['metric' => 'utm_source', 'value' => 'newsletter']);
        $this->assertDatabaseHas('daily_metrics', ['metric' => 'utm_medium', 'value' => 'email']);
        $this->assertDatabaseHas('daily_metrics', ['metric' => 'utm_campaign', 'value' => 'launch']);
        $this->assertDatabaseHas('daily_metrics', ['metric' => 'utm_term', 'value' => 'privacy analytics']);
        $this->assertDatabaseHas('daily_metrics', ['metric' => 'utm_content', 'value' => 'hero-link']);
        $this->assertDatabaseMissing('daily_metrics', ['value' => 'https://www.google.com/search?q=private']);
    }

    public function test_same_site_referrer_is_not_recorded(): void
    {
        $website = $this->website();

        $this->postJson(route('collect.processed', $website), [
            'path' => '/',
            'country' => 'DE',
            'browser' => 'Firefox',
            'os' => 'Linux',
            'device' => Device::Desktop->value,
            'format' => Format::Html->value,
            'referrer' => 'https://www.example.com/from-here',
        ])->assertNoContent();

        $this->assertDatabaseCount('daily_metrics', 6);
        $this->assertDatabaseMissing('daily_metrics', ['metric' => 'referrer']);
    }

    public function test_processed_collection_normalizes_browser_and_os_families(): void
    {
        $website = $this->website();

        $this->postJson(route('collect.processed', $website), [
            'path' => '/',
            'country' => 'DE',
            'browser' => 'Firefox iOS',
            'os' => 'Windows XP',
            'device' => Device::Desktop->value,
            'format' => Format::Html->value,
        ])->assertNoContent();

        $this->assertDatabaseHas('daily_metrics', ['metric' => 'browser', 'value' => 'Firefox']);
        $this->assertDatabaseHas('daily_metrics', ['metric' => 'os', 'value' => 'Windows']);
    }

    public function test_processed_collection_still_rejects_values_outside_closed_enums(): void
    {
        $website = $this->website();

        $this->postJson(route('collect.processed', $website), [
            'path' => '/',
            'country' => 'DE',
            'browser' => 'Firefox',
            'os' => 'Linux',
            'device' => 'laptop',
            'format' => Format::Html->value,
        ])->assertUnprocessable();

        $this->assertDatabaseCount('daily_metrics', 0);
    }

    public function test_raw_collection_parses_the_user_agent_internally(): void
    {
        $website = $this->website();

        $this->withHeader('User-Agent', 'Mozilla/5.0 (X11; Linux x86_64; rv:142.0) Gecko/20100101 Firefox/142.0')
            ->postJson(route('collect.raw', $website), [
                'path' => '/',
                'format' => 'html',
            ])
            ->assertNoContent();

        $this->assertDatabaseHas('daily_metrics', ['metric' => 'browser', 'value' => 'Firefox']);
        $this->assertDatabaseHas('daily_metrics', ['metric' => 'os', 'value' => 'Linux']);
        $this->assertDatabaseHas('daily_metrics', ['metric' => 'device', 'value' => Device::Desktop->value]);
        $this->assertFalse(Schema::hasColumn('daily_metrics', 'ip'));
        $this->assertFalse(Schema::hasColumn('daily_metrics', 'user_agent'));
    }

    public function test_raw_collection_can_reject_bots_before_recording_any_metric(): void
    {
        $website = $this->website(shouldTrackBots: false);

        $this->withHeader('User-Agent', 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)')
            ->postJson(route('collect.raw', $website), ['path' => '/'])
            ->assertNoContent();

        $this->assertDatabaseCount('daily_metrics', 0);
    }

    public function test_raw_collection_coarsens_bots_when_enabled(): void
    {
        $website = $this->website();

        $this->withHeader('User-Agent', 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)')
            ->postJson(route('collect.raw', $website), ['path' => '/'])
            ->assertNoContent();

        $this->assertDatabaseHas('daily_metrics', ['metric' => 'browser', 'value' => 'Bot']);
        $this->assertDatabaseHas('daily_metrics', ['metric' => 'os', 'value' => 'Other']);
        $this->assertDatabaseHas('daily_metrics', ['metric' => 'device', 'value' => Device::Bot->value]);
    }

    public function test_browser_collection_must_come_from_the_configured_domain(): void
    {
        $website = $this->website();

        $this->withHeader('Origin', 'https://evil.example')
            ->postJson(route('collect.raw', $website), ['path' => '/'])
            ->assertForbidden();

        $this->assertDatabaseCount('daily_metrics', 0);

        $this->withHeaders([
            'Origin' => 'https://example.com',
            'User-Agent' => 'Mozilla/5.0 Firefox/142.0',
        ])->postJson(route('collect.raw', $website), ['path' => '/'])->assertNoContent();

        $this->assertDatabaseCount('daily_metrics', 6);
    }

    private function website(bool $shouldTrackBots = true): Website
    {
        return Website::query()->create([
            'name' => 'Example',
            'domain' => 'example.com',
            'timezone' => 'UTC',
            'should_track_bots' => $shouldTrackBots,
        ]);
    }
}
