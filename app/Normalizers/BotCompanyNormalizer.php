<?php

namespace App\Normalizers;

use App\Concerns\Resolvable;
use App\Contracts\Normalizer;
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
            Str::contains($value, ['openai.com', 'gptbot', 'chatgpt-user', 'oai-searchbot', 'oai-adsbot'], true) => 'OpenAI',
            Str::contains($value, ['anthropic.com', 'claudebot', 'claude-user', 'claude-searchbot'], true) => 'Anthropic',
            Str::contains($value, ['googlebot', 'google-extended', 'googleother', 'google-inspectiontool', 'adsbot-google', 'mediapartners-google'], true) => 'Google',
            Str::contains($value, ['bingbot', 'bingpreview', 'adidxbot'], true) => 'Microsoft',
            Str::contains($value, ['applebot'], true) => 'Apple',
            Str::contains($value, ['meta-externalagent', 'facebookexternalhit', 'facebookcatalog'], true) => 'Meta',
            Str::contains($value, ['perplexitybot', 'perplexity-user'], true) => 'Perplexity',
            Str::contains($value, ['duckduckbot'], true) => 'DuckDuckGo',
            Str::contains($value, ['bytespider'], true) => 'ByteDance',
            Str::contains($value, ['yandexbot'], true) => 'Yandex',
            Str::contains($value, ['baiduspider'], true) => 'Baidu',
            default => null,
        };
    }
}
