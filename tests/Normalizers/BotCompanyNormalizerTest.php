<?php

namespace Tests\Normalizers;

use App\Normalizers\BotCompanyNormalizer;
use App\Values\Client;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BotCompanyNormalizerTest extends TestCase
{
    #[Test]
    #[DataProvider('botProvider')]
    public function it_resolves_bot_companies(?string $value, ?string $expected): void
    {
        Assert::assertSame($expected, (new BotCompanyNormalizer)->normalize($value));
    }

    public static function botProvider(): array
    {
        return [
            'openai domain' => ['https://openai.com/gptbot', Client::OPENAI],
            'openai user agent' => ['OAI-SearchBot/1.4', Client::OPENAI],
            'anthropic' => ['Claude-SearchBot/1.0', Client::ANTHROPIC],
            'google' => ['Googlebot/2.1', Client::GOOGLE],
            'microsoft' => ['bingbot/2.0', Client::MICROSOFT],
            'apple' => ['AppleBot/0.1', Client::APPLE],
            'meta' => ['meta-externalagent/1.1', Client::META],
            'perplexity' => ['PerplexityBot/1.0', Client::PERPLEXITY],
            'duckduckgo' => ['DuckDuckBot/1.0', Client::DUCKDUCKGO],
            'bytedance' => ['Bytespider/1.0', Client::BYTEDANCE],
            'yandex' => ['YandexBot/3.0', Client::YANDEX],
            'baidu' => ['Baiduspider/2.0', Client::BAIDU],
            'unknown bot' => ['UnknownCrawler/1.0', null],
            'empty' => ['', null],
            'missing' => [null, null],
        ];
    }
}
