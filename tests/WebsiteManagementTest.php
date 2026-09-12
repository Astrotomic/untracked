<?php

namespace Tests;

use App\Enums\Device;
use App\Enums\Metric;
use App\Models\DailyMetric;
use App\Models\User;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

class WebsiteManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/websites')->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_open_the_dashboard(): void
    {
        $this->withoutVite();
        $this->actingAs(User::factory()->create());

        $this->get('/websites')->assertOk();
    }

    public function test_website_list_shows_useful_human_traffic_insights(): void
    {
        $this->withoutVite();
        $this->actingAs(User::factory()->create());

        $website = Website::query()->create([
            'name' => 'gummibeer.dev',
            'domain' => 'gummibeer.dev',
            'timezone' => 'UTC',
            'should_track_bots' => true,
        ]);

        $today = now('UTC')->startOfDay();
        $rows = [];

        foreach (range(0, 14) as $offset) {
            $date = $today->copy()->subDays($offset)->toDateString();
            $human = match (true) {
                $offset === 0 => 6,
                $offset <= 7 => 2,
                default => 1,
            };
            $bots = $offset === 0 ? 2 : 1;

            $rows[] = [
                'website_uuid' => $website->getKey(),
                'date' => $date,
                'metric' => Metric::Path->value,
                'value' => '/',
                'count' => $human + $bots,
            ];
            $rows[] = [
                'website_uuid' => $website->getKey(),
                'date' => $date,
                'metric' => Metric::Device->value,
                'value' => Device::Bot->value,
                'count' => $bots,
            ];
        }

        DailyMetric::query()->insert($rows);

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

    public function test_authenticated_users_can_open_a_website_dashboard(): void
    {
        $this->withoutVite();
        $this->actingAs(User::factory()->create());

        $website = Website::query()->create([
            'name' => 'gummibeer.dev',
            'domain' => 'gummibeer.dev',
            'timezone' => 'Europe/Berlin',
            'should_track_bots' => true,
        ]);

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

    public function test_bot_analytics_are_hidden_when_bot_tracking_is_disabled(): void
    {
        $this->withoutVite();
        $this->actingAs(User::factory()->create());

        $website = Website::query()->create([
            'name' => 'gummibeer.dev',
            'domain' => 'gummibeer.dev',
            'timezone' => 'Europe/Berlin',
            'should_track_bots' => false,
        ]);

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

    public function test_authenticated_users_can_create_websites(): void
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
        $this->assertNotNull($website->uuid);
        $this->assertSame($website->uuid, $website->getKey());
        $this->assertFalse(Schema::hasColumn('websites', 'id'));
        $this->assertTrue($website->should_track_bots);
    }

    public function test_authenticated_users_can_update_a_website_without_changing_its_domain(): void
    {
        $this->actingAs(User::factory()->create());

        $website = Website::query()->create([
            'name' => 'Untracked Test',
            'domain' => 'untracked.test',
            'timezone' => 'UTC',
            'should_track_bots' => true,
        ]);

        $this->put(route('websites.update', $website), [
            'name' => 'Updated Test',
            'domain' => 'untracked.test',
            'timezone' => 'Europe/Berlin',
            'should_track_bots' => '0',
        ])->assertRedirect(route('websites.show', $website));

        $website->refresh();

        $this->assertSame('Updated Test', $website->name);
        $this->assertSame('untracked.test', $website->domain);
        $this->assertSame('Europe/Berlin', $website->timezone);
        $this->assertFalse($website->should_track_bots);
    }
}
