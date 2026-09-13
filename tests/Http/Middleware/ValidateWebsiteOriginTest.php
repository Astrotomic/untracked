<?php

namespace Tests\Http\Middleware;

use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Assertions\DailyMetricsAssertions;
use Tests\TestCase;

class ValidateWebsiteOriginTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_allows_requests_without_an_origin(): void
    {
        $website = $this->website();

        $this->postJson(route('collect.raw', $website), ['url' => 'https://example.com/'])
            ->assertNoContent();
    }

    #[Test]
    public function it_allows_requests_from_the_website_domain(): void
    {
        $website = $this->website();

        $this->withHeader('Origin', 'https://example.com')
            ->postJson(route('collect.raw', $website), ['url' => 'https://example.com/'])
            ->assertNoContent();
    }

    #[Test]
    public function it_rejects_requests_from_other_origins(): void
    {
        $website = $this->website();

        $this->withHeader('Origin', 'https://evil.example')
            ->postJson(route('collect.raw', $website), ['url' => 'https://example.com/'])
            ->assertForbidden();

        DailyMetricsAssertions::assertEquals([], $website);
    }

    private function website(): Website
    {
        return Website::query()->create([
            'name' => 'Example',
            'domain' => 'example.com',
            'timezone' => 'UTC',
            'should_track_bots' => true,
            'metric_preferences' => Website::defaultMetricPreferences(),
        ]);
    }
}
