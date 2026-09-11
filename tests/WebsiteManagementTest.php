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
            ->assertSee('"human":7', false)
            ->assertSee('"bot":3', false)
            ->assertSee('requests-chart', false)
            ->assertSee('country-map', false)
            ->assertSee('analytics-dashboard-data', false);
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
}
