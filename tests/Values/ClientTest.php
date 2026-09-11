<?php

namespace Tests\Values;

use App\Values\Client;
use PHPUnit\Framework\Assert;
use Tests\TestCase;

class ClientTest extends TestCase
{
    public function test_it_resolves_known_client_domains(): void
    {
        Assert::assertSame('openai.com', (new Client(Client::OPENAI))->domain());
        Assert::assertSame('firefox.com', (new Client(Client::FIREFOX))->domain());
        Assert::assertNull((new Client('Ladybird'))->domain());
    }
}
