<?php

namespace Tests\Feature;

use App\Analytics\Enums\Browser;
use App\Analytics\Enums\Device;
use App\Analytics\Enums\Format;
use App\Analytics\Enums\OperatingSystem;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CollectMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_processed_collection_stores_only_independent_daily_counters(): void
    {
        $website = $this->website();

        $payload = [
            'path' => '/blog/example?utm_source=test',
            'country' => 'de',
            'browser' => Browser::Firefox->value,
            'os' => OperatingSystem::Linux->value,
            'device' => Device::Desktop->value,
            'format' => Format::Markdown->value,
        ];

        $this->postJson(route('collect.processed', $website), $payload)->assertNoContent();
        $this->postJson(route('collect.processed', $website), $payload)->assertNoContent();

        $this->assertDatabaseCount('daily_metrics', 6);
        $this->assertDatabaseHas('daily_metrics', [
            'website_id' => $website->getKey(),
            'metric' => 'path',
            'value' => '/blog/example',
            'count' => 2,
        ]);
        $this->assertDatabaseHas('daily_metrics', [
            'website_id' => $website->getKey(),
            'metric' => 'country',
            'value' => 'DE',
            'count' => 2,
        ]);

        $this->assertFalse(Schema::hasColumn('daily_metrics', 'created_at'));
        $this->assertFalse(Schema::hasColumn('daily_metrics', 'updated_at'));
    }

    public function test_processed_collection_rejects_values_outside_the_coarse_enums(): void
    {
        $website = $this->website();

        $this->postJson(route('collect.processed', $website), [
            'path' => '/',
            'country' => 'DE',
            'browser' => 'Firefox 142.0.1',
            'os' => OperatingSystem::Linux->value,
            'device' => Device::Desktop->value,
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

        $this->assertDatabaseHas('daily_metrics', ['metric' => 'browser', 'value' => Browser::Firefox->value]);
        $this->assertDatabaseHas('daily_metrics', ['metric' => 'os', 'value' => OperatingSystem::Linux->value]);
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

        $this->assertDatabaseHas('daily_metrics', ['metric' => 'browser', 'value' => Browser::Bot->value]);
        $this->assertDatabaseHas('daily_metrics', ['metric' => 'os', 'value' => OperatingSystem::Bot->value]);
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
