<?php

namespace Tests;

use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TestEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_website_for_the_current_host_and_tracks_the_request(): void
    {
        $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64; rv:142.0) Gecko/20100101 Firefox/142.0',
            'Referer' => 'https://example.org/somewhere',
        ])->get('/test?utm_source=manual')->assertOk()->assertSeeText('Tracked.');

        $website = Website::query()->sole();

        $this->assertSame('localhost', $website->domain);
        $this->assertSame('Untracked Test', $website->name);
        $this->assertDatabaseHas('daily_metrics', [
            'website_uuid' => $website->getKey(),
            'metric' => 'path',
            'value' => '/test',
            'count' => 1,
        ]);
        $this->assertDatabaseHas('daily_metrics', [
            'website_uuid' => $website->getKey(),
            'metric' => 'client',
            'value' => 'Firefox',
            'count' => 1,
        ]);
        $this->assertDatabaseHas('daily_metrics', [
            'website_uuid' => $website->getKey(),
            'metric' => 'referrer',
            'value' => 'example.org',
            'count' => 1,
        ]);
        $this->assertDatabaseHas('daily_metrics', [
            'website_uuid' => $website->getKey(),
            'metric' => 'utm_source',
            'value' => 'manual',
            'count' => 1,
        ]);
    }

    public function test_it_reuses_the_website_for_repeated_requests(): void
    {
        $website = Website::query()->create([
            'name' => 'Existing',
            'domain' => 'localhost',
            'timezone' => 'UTC',
            'should_track_bots' => true,
        ]);

        $this->get('/test')->assertOk();
        $this->get('/test')->assertOk();

        $this->assertDatabaseCount('websites', 1);
        $this->assertDatabaseHas('daily_metrics', [
            'website_uuid' => $website->getKey(),
            'metric' => 'path',
            'value' => '/test',
            'count' => 2,
        ]);
    }
}
