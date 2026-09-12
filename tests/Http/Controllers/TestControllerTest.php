<?php

namespace Tests\Http\Controllers;

use App\Enums\Device;
use App\Enums\Format;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\Assertions\DailyMetricsAssertions;
use Tests\TestCase;

class TestControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_creates_a_website_for_the_current_host_and_tracks_the_request(): void
    {
        $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64; rv:142.0) Gecko/20100101 Firefox/142.0',
            'Referer' => 'https://example.org/somewhere',
        ])->get('/test?utm_source=manual')
            ->assertOk()
            ->assertJsonPath('path', '/test')
            ->assertJsonPath('user_agent.client', 'Firefox')
            ->assertJsonPath('user_agent.os', 'Linux')
            ->assertJsonPath('user_agent.device', Device::Desktop->value)
            ->assertJsonPath('user_agent.is_bot', false)
            ->assertJsonPath('format', Format::Html->value)
            ->assertJsonPath('referrer', 'example.org')
            ->assertJsonPath('utm_source', 'manual');

        $website = Website::query()->sole();

        Assert::assertSame('localhost', $website->domain);
        Assert::assertSame('Untracked Test', $website->name);
        Assert::assertSame('UTC', $website->timezone);
        Assert::assertTrue($website->should_track_bots);

        DailyMetricsAssertions::assertEquals([
            'path' => ['/test' => 1],
            'client' => ['Firefox' => 1],
            'os' => ['Linux' => 1],
            'device' => [Device::Desktop->value => 1],
            'format' => [Format::Html->value => 1],
            'referrer' => ['example.org' => 1],
            'utm_source' => ['manual' => 1],
        ], $website);
    }

    #[Test]
    public function it_reuses_the_website_for_repeated_requests(): void
    {
        $website = Website::query()->create([
            'name' => 'Existing',
            'domain' => 'localhost',
            'timezone' => 'UTC',
            'should_track_bots' => true,
        ]);
        $headers = [
            'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64; rv:142.0) Gecko/20100101 Firefox/142.0',
        ];

        $this->withHeaders($headers)->get('/test')->assertOk();
        $this->withHeaders($headers)->get('/test')->assertOk();

        Assert::assertSame(1, Website::query()->count());
        Assert::assertSame($website->getKey(), Website::query()->sole()->getKey());

        DailyMetricsAssertions::assertEquals([
            'path' => ['/test' => 2],
            'client' => ['Firefox' => 2],
            'os' => ['Linux' => 2],
            'device' => [Device::Desktop->value => 2],
            'format' => [Format::Html->value => 2],
        ], $website);
    }
}
