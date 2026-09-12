<?php

namespace Tests\Http\Requests;

use App\Models\User;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WebsiteRequestTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    #[DataProvider('invalidPayloadProvider')]
    public function it_rejects_invalid_website_values(string $field, mixed $value): void
    {
        $this->actingAs(User::factory()->create());
        $payload = array_replace($this->validPayload(), [$field => $value]);

        $this->post(route('websites.store'), $payload)
            ->assertSessionHasErrors($field);

        Assert::assertSame(0, Website::query()->count());
    }

    #[Test]
    public function it_requires_the_domain_to_be_unique(): void
    {
        $this->actingAs(User::factory()->create());
        Website::query()->create($this->validPayload());

        $this->post(route('websites.store'), array_replace($this->validPayload(), [
            'name' => 'Another Website',
        ]))->assertSessionHasErrors('domain');

        Assert::assertSame(1, Website::query()->count());
    }

    #[Test]
    public function it_allows_an_existing_website_to_keep_its_domain(): void
    {
        $this->actingAs(User::factory()->create());
        $website = Website::query()->create($this->validPayload());

        $this->put(route('websites.update', $website), array_replace($this->validPayload(), [
            'name' => 'Updated Website',
        ]))->assertRedirect(route('websites.show', $website));

        $website->refresh();

        Assert::assertSame('Updated Website', $website->name);
        Assert::assertSame('example.com', $website->domain);
        Assert::assertSame(1, Website::query()->count());
    }

    public static function invalidPayloadProvider(): array
    {
        return [
            'name required' => ['name', null],
            'name string' => ['name', []],
            'name max' => ['name', str_repeat('x', 256)],
            'domain required' => ['domain', null],
            'domain string' => ['domain', []],
            'domain max' => ['domain', str_repeat('x', 256)],
            'timezone required' => ['timezone', null],
            'timezone valid' => ['timezone', 'Mars/Olympus'],
            'bot tracking required' => ['should_track_bots', null],
            'bot tracking boolean' => ['should_track_bots', 'sometimes'],
        ];
    }

    private function validPayload(): array
    {
        return [
            'name' => 'Example',
            'domain' => 'example.com',
            'timezone' => 'UTC',
            'should_track_bots' => true,
        ];
    }
}
