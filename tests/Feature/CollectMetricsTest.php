<?php

namespace Tests\Feature;

use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CollectMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stores_only_independent_daily_counters(): void
    {
        $website = Website::query()->create([
            'name' => 'Example',
            'domain' => 'example.com',
            'timezone' => 'UTC',
            'track_bots' => true,
        ]);

        $payload = [
            'path' => '/blog/example?utm_source=test',
            'country' => 'DE',
            'browser' => 'Firefox',
            'os' => 'Linux',
            'device' => 'desktop',
            'format' => 'markdown',
        ];

        $this->postJson(route('collect', $website), $payload)->assertNoContent();
        $this->postJson(route('collect', $website), $payload)->assertNoContent();

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

    public function test_it_can_reject_bots_before_recording_any_metric(): void
    {
        $website = Website::query()->create([
            'name' => 'Example',
            'domain' => 'example.com',
            'timezone' => 'UTC',
            'track_bots' => false,
        ]);

        $this->withHeader('User-Agent', 'Googlebot/2.1')
            ->postJson(route('collect', $website), ['path' => '/'])
            ->assertNoContent();

        $this->assertDatabaseCount('daily_metrics', 0);
    }

    public function test_it_coarsens_bots_when_they_are_enabled(): void
    {
        $website = Website::query()->create([
            'name' => 'Example',
            'domain' => 'example.com',
            'timezone' => 'UTC',
            'track_bots' => true,
        ]);

        $this->withHeader('User-Agent', 'Googlebot/2.1')
            ->postJson(route('collect', $website), ['path' => '/'])
            ->assertNoContent();

        $this->assertDatabaseHas('daily_metrics', ['metric' => 'browser', 'value' => 'Bot']);
        $this->assertDatabaseHas('daily_metrics', ['metric' => 'os', 'value' => 'Bot']);
        $this->assertDatabaseHas('daily_metrics', ['metric' => 'device', 'value' => 'bot']);
    }

    public function test_it_uses_coarse_country_headers_without_storing_an_ip(): void
    {
        $website = Website::query()->create([
            'name' => 'Example',
            'domain' => 'example.com',
            'timezone' => 'UTC',
            'track_bots' => true,
        ]);

        $this->withHeaders([
            'CF-IPCountry' => 'PL',
            'User-Agent' => 'Mozilla/5.0 Firefox/142.0',
        ])->postJson(route('collect', $website), ['path' => '/'])->assertNoContent();

        $this->assertDatabaseHas('daily_metrics', ['metric' => 'country', 'value' => 'PL']);
        $this->assertFalse(Schema::hasColumn('daily_metrics', 'ip'));
        $this->assertFalse(Schema::hasColumn('daily_metrics', 'user_agent'));
    }
}
