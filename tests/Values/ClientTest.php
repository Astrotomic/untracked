<?php

namespace Tests\Values;

use App\Values\Client;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ClientTest extends TestCase
{
    #[Test]
    #[DataProvider('domainProvider')]
    public function it_resolves_known_client_domains(string $client, ?string $expected): void
    {
        Assert::assertSame($expected, (new Client($client))->domain());
    }

    public static function domainProvider(): array
    {
        return [
            'firefox' => [Client::FIREFOX, 'firefox.com'],
            'chrome' => [Client::CHROME, 'chrome.google.com'],
            'chrome webview' => [Client::CHROME_WEBVIEW, 'chrome.google.com'],
            'chromium' => [Client::CHROMIUM, 'chromium.org'],
            'edge' => [Client::EDGE, 'microsoft.com'],
            'internet explorer' => [Client::IE, 'microsoft.com'],
            'opera' => [Client::OPERA, 'opera.com'],
            'safari' => [Client::SAFARI, 'apple.com'],
            'safari webview' => [Client::SAFARI_WEBVIEW, 'apple.com'],
            'duckduckgo' => [Client::DUCKDUCKGO, 'duckduckgo.com'],
            'ecosia' => [Client::ECOSIA, 'ecosia.org'],
            'qq browser' => [Client::QQ_BROWSER, 'browser.qq.com'],
            'openai' => [Client::OPENAI, 'openai.com'],
            'anthropic' => [Client::ANTHROPIC, 'anthropic.com'],
            'google' => [Client::GOOGLE, 'google.com'],
            'microsoft' => [Client::MICROSOFT, 'microsoft.com'],
            'apple' => [Client::APPLE, 'apple.com'],
            'meta' => [Client::META, 'meta.com'],
            'perplexity' => [Client::PERPLEXITY, 'perplexity.ai'],
            'bytedance' => [Client::BYTEDANCE, 'bytedance.com'],
            'yandex' => [Client::YANDEX, 'yandex.com'],
            'baidu' => [Client::BAIDU, 'baidu.com'],
            'generic bot' => [Client::BOT, null],
            'other' => [Client::OTHER, null],
            'unknown' => ['Ladybird', null],
        ];
    }
}
