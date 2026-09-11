<?php

namespace App\Values;

final readonly class Client
{
    public const string OTHER = 'Other';

    public const string BOT = 'Bot';

    public const string FIREFOX = 'Firefox';

    public const string CHROME = 'Chrome';

    public const string CHROME_WEBVIEW = 'Chrome WebView';

    public const string CHROMIUM = 'Chromium';

    public const string EDGE = 'Edge';

    public const string IE = 'IE';

    public const string OPERA = 'Opera';

    public const string SAFARI = 'Safari';

    public const string SAFARI_WEBVIEW = 'Safari WebView';

    public const string DUCKDUCKGO = 'DuckDuckGo';

    public const string ECOSIA = 'Ecosia';

    public const string QQ_BROWSER = 'QQ Browser';

    public const string OPENAI = 'OpenAI';

    public const string ANTHROPIC = 'Anthropic';

    public const string GOOGLE = 'Google';

    public const string MICROSOFT = 'Microsoft';

    public const string APPLE = 'Apple';

    public const string META = 'Meta';

    public const string PERPLEXITY = 'Perplexity';

    public const string BYTEDANCE = 'ByteDance';

    public const string YANDEX = 'Yandex';

    public const string BAIDU = 'Baidu';

    public function __construct(
        public string $value,
    ) {}

    public function domain(): ?string
    {
        return match ($this->value) {
            self::FIREFOX => 'firefox.com',
            self::CHROME, self::CHROME_WEBVIEW => 'chrome.google.com',
            self::CHROMIUM => 'chromium.org',
            self::EDGE, self::IE, self::MICROSOFT => 'microsoft.com',
            self::OPERA => 'opera.com',
            self::SAFARI, self::SAFARI_WEBVIEW, self::APPLE => 'apple.com',
            self::DUCKDUCKGO => 'duckduckgo.com',
            self::ECOSIA => 'ecosia.org',
            self::QQ_BROWSER => 'browser.qq.com',
            self::OPENAI => 'openai.com',
            self::ANTHROPIC => 'anthropic.com',
            self::GOOGLE => 'google.com',
            self::META => 'meta.com',
            self::PERPLEXITY => 'perplexity.ai',
            self::BYTEDANCE => 'bytedance.com',
            self::YANDEX => 'yandex.com',
            self::BAIDU => 'baidu.com',
            default => null,
        };
    }
}
