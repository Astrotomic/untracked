<?php

namespace App\Normalizers;

use App\Concerns\Resolvable;
use App\Contracts\Normalizer;
use App\Values\Client;
use Illuminate\Support\Str;

final readonly class BotCompanyNormalizer implements Normalizer
{
    use Resolvable;

    public function normalize(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        return match (true) {
            Str::contains($value, ['openai.com', 'gptbot', 'chatgpt-user', 'oai-searchbot', 'oai-adsbot'], true) => Client::OPENAI,
            Str::contains($value, ['anthropic.com', 'claudebot', 'claude-user', 'claude-searchbot'], true) => Client::ANTHROPIC,
            Str::contains($value, ['googlebot', 'google-extended', 'googleother', 'google-inspectiontool', 'adsbot-google', 'mediapartners-google', 'google-agent', 'google-gemininotebook', 'google-notebooklm'], true) => Client::GOOGLE,
            Str::contains($value, ['bingbot', 'bingpreview', 'adidxbot'], true) => Client::MICROSOFT,
            Str::contains($value, ['applebot'], true) => Client::APPLE,
            Str::contains($value, ['meta-externalagent', 'facebookexternalhit', 'facebookcatalog'], true) => Client::META,
            Str::contains($value, ['perplexitybot', 'perplexity-user'], true) => Client::PERPLEXITY,
            Str::contains($value, ['duckduckbot'], true) => Client::DUCKDUCKGO,
            Str::contains($value, ['bytespider'], true) => Client::BYTEDANCE,
            Str::contains($value, ['yandexbot'], true) => Client::YANDEX,
            Str::contains($value, ['baiduspider'], true) => Client::BAIDU,
            default => null,
        };
    }
}
