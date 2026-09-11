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
            'client' => 'Firefox',
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
        $this->assertDatabaseHas('daily_metrics', [
            'website_uuid' => $website->getKey(),
            'metric' => 'client',
            'value' => 'Firefox',
            'count' => 2,
        ]);
        $this->assertDatabaseMissing('daily_metrics', ['metric' => 'browser']);
        $this->assertDatabaseMissing('daily_metrics', ['metric' => 'utm_source']);

        $this->assertFalse(Schema::hasColumn('daily_metrics', 'created_at'));
        $this->assertFalse(Schema::hasColumn('daily_metrics', 'updated_at'));
    }

    public function test_processed_collection_skips_country_when_missing(): void
    {
        $website = $this->website();

        $this->postJson(route('collect.processed', $website), [
            'path' => '/',
            'country' => null,
            'client' => 'Firefox',
            'os' => 'Linux',
            'device' => Device::Desktop->value,
            'format' => Format::Html->value,
        ])->assertNoContent();

        $this->assertDatabaseCount('daily_metrics', 5);
        $this->assertDatabaseMissing('daily_metrics', ['metric' => 'country']);
    }

    public function test_processed_collection_stores_attribution_as_independent_optional_counters(): void
    {
        $website = $this->website();

        $this->postJson(route('collect.processed', $website), [
            'path' => '/landing?secret=discarded',
            'country' => 'DE',
            'client' => 'Firefox iOS',
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
            'client' => 'Firefox',
            'os' => 'Linux',
            'device' => Device::Desktop->value,
            'format' => Format::Html->value,
            'referrer' => 'https://www.example.com/from-here',
        ])->assertNoContent();

        $this->assertDatabaseCount('daily_metrics', 6);
        $this->assertDatabaseMissing('daily_metrics', ['metric' => 'referrer']);
    }

    public function test_processed_collection_normalizes_client_and_os_families(): void
    {
        $website = $this->website();

        $this->postJson(route('collect.processed', $website), [
            'path' => '/',
            'country' => 'DE',
            'client' => 'Firefox iOS',
            'os' => 'Windows XP',
            'device' => Device::Desktop->value,
            'format' => Format::Html->value,
        ])->assertNoContent();

        $this->assertDatabaseHas('daily_metrics', ['metric' => 'client', 'value' => 'Firefox']);
        $this->assertDatabaseHas('daily_metrics', ['metric' => 'os', 'value' => 'Windows']);
    }

    public function test_processed_collection_still_rejects_values_outside_closed_enums(): void
    {
        $website = $this->website();

        $this->postJson(route('collect.processed', $website), [
            'path' => '/',
            'country' => 'DE',
            'client' => 'Firefox',
            'os' => 'Linux',
            'device' => 'laptop',
            'format' => Format::Html->value,
        ])->assertUnprocessable();

        $this->assertDatabaseCount('daily_metrics', 0);
    }

    public function test_processed_bot_collection_drops_audience_dimensions(): void
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

        $this->assertDatabaseCount('daily_metrics', 4);
        $this->assertDatabaseHas('daily_metrics', ['metric' => 'path', 'value' => '/blog/example']);
        $this->assertDatabaseHas('daily_metrics', ['metric' => 'client', 'value' => 'OpenAI']);
        $this->assertDatabaseHas('daily_metrics', ['metric' => 'device', 'value' => Device::Bot->value]);
        $this->assertDatabaseHas('daily_metrics', ['metric' => 'format', 'value' => Format::Markdown->value]);
        $this->assertDatabaseMissing('daily_metrics', ['metric' => 'country']);
        $this->assertDatabaseMissing('daily_metrics', ['metric' => 'os']);
        $this->assertDatabaseMissing('daily_metrics', ['metric' => 'referrer']);
        $this->assertDatabaseMissing('daily_metrics', ['metric' => 'utm_source']);
        $this->assertDatabaseMissing('daily_metrics', ['metric' => 'utm_medium']);
        $this->assertDatabaseMissing('daily_metrics', ['metric' => 'utm_campaign']);
        $this->assertDatabaseMissing('daily_metrics', ['metric' => 'utm_term']);
        $this->assertDatabaseMissing('daily_metrics', ['metric' => 'utm_content']);
    }

    public function test_raw_collection_derives_dimensions_from_url_ip_user_agent_and_referrer(): void
    {
        $website = $this->website();

        $url = 'https://example.com/landing?utm_source=newsletter&utm_medium=email&utm_campaign=launch&utm_term=privacy%20analytics&utm_content=hero-link&secret=discarded';
        $referrer = 'https://www.google.com/search?q=private';

        $this->withHeader('User-Agent', 'Mozilla/5.0 (X11; Linux x86_64; rv:142.0) Gecko/20100101 Firefox/142.0')
            ->postJson(route('collect.raw', $website), [
                'url' => $url,
                'referrer' => $referrer,
            ])
            ->assertNoContent();

        $this->assertDatabaseCount('daily_metrics', 11);
        $this->assertDatabaseHas('daily_metrics', ['metric' => 'path', 'value' => '/landing']);
        $this->assertDatabaseMissing('daily_metrics', ['metric' => 'country']);
        $this->assertDatabaseHas('daily_metrics', ['metric' => 'client', 'value' => 'Firefox']);
        $this->assertDatabaseHas('daily_metrics', ['metric' => 'os', 'value' => 'Linux']);
        $this->assertDatabaseHas('daily_metrics', ['metric' => 'device', 'value' => Device::Desktop->value]);
        $this->assertDatabaseHas('daily_metrics', ['metric' => 'format', 'value' => Format::Html->value]);
        $this->assertDatabaseHas('daily_metrics', ['metric' => 'referrer', 'value' => 'google.com']);
        $this->assertDatabaseHas('daily_metrics', ['metric' => 'utm_source', 'value' => 'newsletter']);
        $this->assertDatabaseHas('daily_metrics', ['metric' => 'utm_medium', 'value' => 'email']);
        $this->assertDatabaseHas('daily_metrics', ['metric' => 'utm_campaign', 'value' => 'launch']);
        $this->assertDatabaseHas('daily_metrics', ['metric' => 'utm_term', 'value' => 'privacy analytics']);
        $this->assertDatabaseHas('daily_metrics', ['metric' => 'utm_content', 'value' => 'hero-link']);
        $this->assertDatabaseMissing('daily_metrics', ['value' => $url]);
        $this->assertDatabaseMissing('daily_metrics', ['value' => $referrer]);
        $this->assertFalse(Schema::hasColumn('daily_metrics', 'ip'));
        $this->assertFalse(Schema::hasColumn('daily_metrics', 'user_agent'));
    }

    public function test_raw_collection_can_reject_bots_before_recording_any_metric(): void
    {
        $website = $this->website(shouldTrackBots: false);

        $this->withHeader('User-Agent', 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)')
            ->postJson(route('collect.raw', $website), ['url' => 'https://example.com/'])
            ->assertNoContent();

        $this->assertDatabaseCount('daily_metrics', 0);
    }

    public function test_raw_bot_collection_keeps_bot_identity_without_audience_dimensions(): void
    {
        $website = $this->website();

        $this->withHeader('User-Agent', 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)')
            ->postJson(route('collect.raw', $website), [
                'url' => 'https://example.com/blog/example?utm_source=newsletter&utm_campaign=launch',
                'referrer' => 'https://example.org/recommended',
            ])
            ->assertNoContent();

        $this->assertDatabaseCount('daily_metrics', 4);
        $this->assertDatabaseHas('daily_metrics', ['metric' => 'path', 'value' => '/blog/example']);
        $this->assertDatabaseHas('daily_metrics', ['metric' => 'client', 'value' => 'Google']);
        $this->assertDatabaseHas('daily_metrics', ['metric' => 'device', 'value' => Device::Bot->value]);
        $this->assertDatabaseHas('daily_metrics', ['metric' => 'format', 'value' => Format::Html->value]);
        $this->assertDatabaseMissing('daily_metrics', ['metric' => 'country']);
        $this->assertDatabaseMissing('daily_metrics', ['metric' => 'os']);
        $this->assertDatabaseMissing('daily_metrics', ['metric' => 'referrer']);
        $this->assertDatabaseMissing('daily_metrics', ['metric' => 'utm_source']);
        $this->assertDatabaseMissing('daily_metrics', ['metric' => 'utm_campaign']);
    }

    public function test_browser_collection_must_come_from_the_configured_domain(): void
    {
        $website = $this->website();

        $this->withHeader('Origin', 'https://evil.example')
            ->postJson(route('collect.raw', $website), ['url' => 'https://example.com/'])
            ->assertForbidden();

        $this->assertDatabaseCount('daily_metrics', 0);

        $this->withHeaders([
            'Origin' => 'https://example.com',
            'User-Agent' => 'Mozilla/5.0 Firefox/142.0',
        ])->postJson(route('collect.raw', $website), ['url' => 'https://example.com/'])->assertNoContent();

        $this->assertDatabaseCount('daily_metrics', 5);
        $this->assertDatabaseMissing('daily_metrics', ['metric' => 'country']);
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
