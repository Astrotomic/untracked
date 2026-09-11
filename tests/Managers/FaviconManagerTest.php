<?php

namespace Tests\Managers;

use App\Managers\Favicons\FaviconManager;
use PHPUnit\Framework\Assert;
use Tests\TestCase;

class FaviconManagerTest extends TestCase
{
    public function test_it_uses_duckduckgo_by_default(): void
    {
        Assert::assertSame(
            'https://icons.duckduckgo.com/ip3/google.com.ico',
            FaviconManager::make()->url('google.com'),
        );
    }

    public function test_it_resolves_known_client_domains(): void
    {
        Assert::assertSame(
            'https://icons.duckduckgo.com/ip3/openai.com.ico',
            FaviconManager::make()->clientUrl('OpenAI'),
        );

        Assert::assertSame(
            'https://icons.duckduckgo.com/ip3/firefox.com.ico',
            FaviconManager::make()->clientUrl('Firefox'),
        );

        Assert::assertNull(FaviconManager::make()->clientUrl('Ladybird'));
    }
}
