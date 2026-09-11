<?php

namespace App\Values;

use App\Enums\Format;
use App\Enums\Metric;
use App\Managers\IpManager;
use App\Managers\UserAgentManager;
use App\Models\Website;
use App\Normalizers\CountryNormalizer;
use App\Normalizers\PathNormalizer;
use App\Normalizers\ReferrerNormalizer;
use App\Normalizers\ValueNormalizer;

final readonly class Dimensions
{
    public static function fromRaw(
        string $url,
        string $ip,
        string $userAgent,
        ?string $referrer,
        Website $website,
    ): self {
        $userAgent = UserAgentManager::make()->driver()->resolve($userAgent);
        $query = [];

        if (! $userAgent->isBot()) {
            parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        }

        $utm = static fn (string $key): ?string => is_string($query[$key] ?? null)
            ? $query[$key]
            : null;

        return self::from(
            path: $url,
            country: $userAgent->isBot() ? null : IpManager::make()->driver()->country($ip),
            userAgent: $userAgent,
            format: Format::Html,
            referrer: $referrer,
            utmSource: $utm('utm_source'),
            utmMedium: $utm('utm_medium'),
            utmCampaign: $utm('utm_campaign'),
            utmTerm: $utm('utm_term'),
            utmContent: $utm('utm_content'),
            website: $website,
        );
    }

    public static function from(
        string $path,
        ?string $country,
        UserAgent $userAgent,
        Format $format,
        ?string $referrer,
        ?string $utmSource,
        ?string $utmMedium,
        ?string $utmCampaign,
        ?string $utmTerm,
        ?string $utmContent,
        Website $website,
    ): self {
        $userAgent = self::normalizeUserAgent($userAgent);
        $isBot = $userAgent->isBot();

        return new self(
            path: PathNormalizer::make()->normalize($path),
            country: $isBot ? null : CountryNormalizer::make()->normalize($country),
            userAgent: $userAgent,
            format: $format,
            referrer: $isBot ? null : ReferrerNormalizer::make()->normalize($referrer, $website->domain),
            utmSource: $isBot ? null : ValueNormalizer::make()->normalize($utmSource),
            utmMedium: $isBot ? null : ValueNormalizer::make()->normalize($utmMedium),
            utmCampaign: $isBot ? null : ValueNormalizer::make()->normalize($utmCampaign),
            utmTerm: $isBot ? null : ValueNormalizer::make()->normalize($utmTerm),
            utmContent: $isBot ? null : ValueNormalizer::make()->normalize($utmContent),
        );
    }

    public function __construct(
        public string $path,
        public ?string $country,
        public UserAgent $userAgent,
        public Format $format,
        public ?string $referrer = null,
        public ?string $utmSource = null,
        public ?string $utmMedium = null,
        public ?string $utmCampaign = null,
        public ?string $utmTerm = null,
        public ?string $utmContent = null,
    ) {}

    public function value(Metric $metric): ?string
    {
        return match ($metric) {
            Metric::Path => $this->path,
            Metric::Country => $this->country,
            Metric::Browser => $this->userAgent->browser,
            Metric::OperatingSystem => $this->userAgent->os,
            Metric::Device => $this->userAgent->device->value,
            Metric::Format => $this->format->value,
            Metric::Referrer => $this->referrer,
            Metric::UtmSource => $this->utmSource,
            Metric::UtmMedium => $this->utmMedium,
            Metric::UtmCampaign => $this->utmCampaign,
            Metric::UtmTerm => $this->utmTerm,
            Metric::UtmContent => $this->utmContent,
        };
    }

    private static function normalizeUserAgent(UserAgent $userAgent): UserAgent
    {
        if (! $userAgent->isBot()) {
            return $userAgent;
        }

        $browser = $userAgent->browser;

        if ($browser === null || $browser === '' || in_array(strtolower($browser), ['bot', 'other'], true)) {
            $browser = null;
        }

        return new UserAgent(
            browser: $browser,
            os: null,
            device: $userAgent->device,
        );
    }
}
