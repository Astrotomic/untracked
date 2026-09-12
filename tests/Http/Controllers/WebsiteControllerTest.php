<?php

namespace Tests\Http\Controllers;

use App\Enums\Device;
use App\Enums\Metric;
use App\Models\DailyMetric;
use App\Models\User;
use App\Models\Website;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WebsiteControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_guests_are_redirected_to_login(): void
    {
        $this->get(route('websites.index'))->assertRedirect(route('login'));
    }

    #[Test]
    public function it_authenticated_users_can_open_the_website_list(): void
    {
        $this->withoutVite();
        $this->actingAs(User::factory()->create());

        $this->get(route('websites.index'))->assertOk();
    }

    #[Test]
    public function it_website_list_shows_human_traffic_insights(): void
    {
        $this->withoutVite();
        $this->actingAs(User::factory()->create());
        $website = $this->website();
        $today = now('UTC')->startOfDay();

        DailyMetric::query()->insert(self::trafficRows($website, $today));

        $this->get(route('websites.index'))
            ->assertOk()
            ->assertSee('human today')
            ->assertSee('7d trend')
            ->assertSee('"today":6', false)
            ->assertSee('"sparkline":[2,2,2,2,2,2,6]', false)
            ->assertSee('"trend_direction":"up"', false)
            ->assertSee('"trend_percentage":100', false)
            ->assertSee('website-list-data', false);
    }

    #[Test]
    public function it_authenticated_users_can_open_a_website_dashboard(): void
    {
        $this->withoutVite();
        $this->actingAs(User::factory()->create());
        $website = $this->website(timezone: 'Europe/Berlin');
        $date = now($website->timezone)->toDateString();

        DailyMetric::query()->insert([
            [
                'website_uuid' => $website->getKey(),
                'date' => $date,
                'metric' => Metric::Path->value,
                'value' => '/',
                'count' => 10,
            ],
            [
                'website_uuid' => $website->getKey(),
                'date' => $date,
                'metric' => Metric::Device->value,
                'value' => Device::Bot->value,
                'count' => 3,
            ],
        ]);

        $this->get(route('websites.show', $website))
            ->assertOk()
            ->assertSee('Requests over time')
            ->assertSee('Bot requests')
            ->assertSee('"human":7', false)
            ->assertSee('"bot":3', false)
            ->assertSee('"shouldTrackBots":true', false)
            ->assertSee('requests-chart', false)
            ->assertSee('country-map', false)
            ->assertSee('analytics-dashboard-data', false);
    }

    #[Test]
    public function it_bot_analytics_are_hidden_when_bot_tracking_is_disabled(): void
    {
        $this->withoutVite();
        $this->actingAs(User::factory()->create());
        $website = $this->website(timezone: 'Europe/Berlin', shouldTrackBots: false);
        $date = now($website->timezone)->toDateString();

        DailyMetric::query()->insert([
            [
                'website_uuid' => $website->getKey(),
                'date' => $date,
                'metric' => Metric::Path->value,
                'value' => '/',
                'count' => 10,
            ],
            [
                'website_uuid' => $website->getKey(),
                'date' => $date,
                'metric' => Metric::Device->value,
                'value' => Device::Bot->value,
                'count' => 3,
            ],
        ]);

        $this->get(route('websites.show', $website))
            ->assertOk()
            ->assertDontSee('Bot requests')
            ->assertSee('"human":7', false)
            ->assertDontSee('"bot":3', false)
            ->assertSee('"shouldTrackBots":false', false);
    }

    #[Test]
    public function it_authenticated_users_can_open_the_create_form(): void
    {
        $this->withoutVite();
        $this->actingAs(User::factory()->create());

        $this->get(route('websites.create'))->assertOk();
    }

    #[Test]
    public function it_authenticated_users_can_create_websites(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->post(route('websites.store'), [
            'name' => 'gummibeer.dev',
            'domain' => 'gummibeer.dev',
            'timezone' => 'Europe/Berlin',
            'should_track_bots' => '1',
        ]);

        $website = Website::query()->sole();

        $response->assertRedirect(route('websites.show', $website));
        Assert::assertNotNull($website->uuid);
        Assert::assertSame($website->uuid, $website->getKey());
        Assert::assertSame('gummibeer.dev', $website->name);
        Assert::assertSame('gummibeer.dev', $website->domain);
        Assert::assertSame('Europe/Berlin', $website->timezone);
        Assert::assertTrue($website->should_track_bots);
    }

    #[Test]
    public function it_authenticated_users_can_open_the_edit_form(): void
    {
        $this->withoutVite();
        $this->actingAs(User::factory()->create());
        $website = $this->website();

        $this->get(route('websites.edit', $website))->assertOk();
    }

    #[Test]
    public function it_authenticated_users_can_update_a_website(): void
    {
        $this->actingAs(User::factory()->create());
        $website = $this->website();

        $this->put(route('websites.update', $website), [
            'name' => 'Updated Test',
            'domain' => 'example.com',
            'timezone' => 'Europe/Berlin',
            'should_track_bots' => '0',
        ])->assertRedirect(route('websites.show', $website));

        $website->refresh();

        Assert::assertSame('Updated Test', $website->name);
        Assert::assertSame('example.com', $website->domain);
        Assert::assertSame('Europe/Berlin', $website->timezone);
        Assert::assertFalse($website->should_track_bots);
    }

    #[Test]
    public function it_authenticated_users_can_delete_a_website(): void
    {
        $this->actingAs(User::factory()->create());
        $website = $this->website();
        $key = $website->getKey();

        $this->delete(route('websites.destroy', $website))
            ->assertRedirect(route('websites.index'));

        Assert::assertFalse(Website::query()->whereKey($key)->exists());
    }

    private function website(string $timezone = 'UTC', bool $shouldTrackBots = true): Website
    {
        return Website::query()->create([
            'name' => 'gummibeer.dev',
            'domain' => 'example.com',
            'timezone' => $timezone,
            'should_track_bots' => $shouldTrackBots,
        ]);
    }

    private static function trafficRows(Website $website, CarbonInterface $today): array
    {
        return collect(range(0, 14))
            ->flatMap(static function (int $offset) use ($website, $today): array {
                $date = $today->copy()->subDays($offset)->toDateString();
                $human = match (true) {
                    $offset === 0 => 6,
                    $offset <= 7 => 2,
                    default => 1,
                };
                $bots = $offset === 0 ? 2 : 1;

                return [
                    [
                        'website_uuid' => $website->getKey(),
                        'date' => $date,
                        'metric' => Metric::Path->value,
                        'value' => '/',
                        'count' => $human + $bots,
                    ],
                    [
                        'website_uuid' => $website->getKey(),
                        'date' => $date,
                        'metric' => Metric::Device->value,
                        'value' => Device::Bot->value,
                        'count' => $bots,
                    ],
                ];
            })
            ->all();
    }
}
