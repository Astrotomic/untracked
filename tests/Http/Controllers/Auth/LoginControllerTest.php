<?php

namespace Tests\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    #[Test]
    public function it_allows_guests_to_open_the_login_form(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertViewIs('auth.login');
    }

    #[Test]
    public function it_allows_users_to_login(): void
    {
        $user = User::factory()->create();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('websites.index'));

        Assert::assertTrue(auth()->check());
        Assert::assertSame($user->getKey(), auth()->id());
    }

    #[Test]
    #[DataProvider('invalidPayloadProvider')]
    public function it_rejects_invalid_login_payloads(string $field, mixed $value): void
    {
        $user = User::factory()->create();
        $payload = [
            'email' => $user->email,
            'password' => 'password',
        ];
        $payload[$field] = $value;

        $this->post(route('login.store'), $payload)
            ->assertSessionHasErrors($field);

        Assert::assertFalse(auth()->check());
        Assert::assertNull(auth()->user());
    }

    #[Test]
    public function it_rejects_invalid_credentials(): void
    {
        $user = User::factory()->create();

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'wrong-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email')
            ->assertSessionHasInput('email', $user->email);

        Assert::assertFalse(auth()->check());
        Assert::assertNull(auth()->user());
    }

    #[Test]
    public function it_allows_authenticated_users_to_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        Assert::assertFalse(auth()->check());
        Assert::assertNull(auth()->user());
    }

    public static function invalidPayloadProvider(): array
    {
        return [
            'email required' => ['email', null],
            'email valid' => ['email', 'not-an-email'],
            'password required' => ['password', null],
            'password string' => ['password', []],
        ];
    }
}
