<?php

namespace Tests\Drivers\UserAgent;

use App\Drivers\UserAgent\UapUserAgentDriver;
use App\Enums\Device;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use UAParser\Parser;

class UapUserAgentDriverTest extends TestCase
{
    protected function setUp(): void
    {
        $this->driver = new UapUserAgentDriver(Parser::create());

        parent::setUp();
    }

    #[DataProvider('userAgentsProvider')]
    public function test_useragent_parsing(string $userAgentString, ?string $browser, ?string $os, Device $device, bool $isBot): void
    {
        $userAgent = $this->driver->resolve($userAgentString);

        Assert::assertSame($browser, $userAgent->browser);
        Assert::assertSame($os, $userAgent->os);
        Assert::assertSame($device, $userAgent->device);
        Assert::assertSame($isBot, $userAgent->isBot());
    }

    public static function userAgentsProvider(): array
    {
        return [
            [
                'Mozilla/5.0 (Mobile; TCL T435SP; rv:84.0; KAIKID) Gecko/84.0 Firefox/84.0 KAIOS/3.1',
                'Firefox', 'KaiOS', Device::Mobile, false,
            ], [
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36',
                'Chrome', 'macOS', Device::Desktop, false,
            ], [
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36; compatible; OAI-SearchBot/1.4; +https://openai.com/searchbot',
                'OpenAI', null, Device::Bot, true,
            ], [
                'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko); compatible; OAI-AdsBot/1.0; +https://openai.com/adsbot',
                'OpenAI', null, Device::Bot, true,
            ], [
                'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko); compatible; GPTBot/1.4; +https://openai.com/gptbot',
                'OpenAI', null, Device::Bot, true,
            ], [
                'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko); compatible; ChatGPT-User/1.0; +https://openai.com/bot',
                'OpenAI', null, Device::Bot, true,
            ], [
                'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; ClaudeBot/1.0; +claudebot@anthropic.com)',
                'Anthropic', null, Device::Bot, true,
            ], [
                'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Claude-User/1.0; +Claude-User@anthropic.com)',
                'Anthropic', null, Device::Bot, true,
            ], [
                'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Claude-SearchBot/1.0; +Claude-SearchBot@anthropic.com)',
                'Anthropic', null, Device::Bot, true,
            ], [
                'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
                'Google', null, Device::Bot, true,
            ], [
                'UnknownCrawler/1.0',
                null, null, Device::Bot, true,
            ], [
                'Dalvik/1.6.0 (Linux; U; Android 4.4.4; SM-T560 Build/KTU84P) [ip:213.32.4.95]',
                'Android', 'Android', Device::Mobile, false,
            ],
        ];
    }
}
