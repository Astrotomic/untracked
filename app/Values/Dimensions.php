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
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;

final readonly class Dimensions implements Arrayable, Jsonable, JsonSerializable
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
            Metric::Client => $this->userAgent->client,
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

        $client = $userAgent->client;

        if ($client === null || $client === '' || in_array(strtolower($client), ['bot', 'other'], true)) {
            $client = null;
        }

        return new UserAgent(
            client: $client,
            os: null,
            device: $userAgent->device,
        );
    }

    public function toArray(): array
    {
        return [
            'path' => $this->path,
            'country' => $this->country,
            'user_agent' => [
                'client' => $this->userAgent->client,
                'os' => $this->userAgent->os,
                'device' => $this->userAgent->device,
                'is_bot' => $this->userAgent->isBot(),
            ],
            'format' => $this->format,
            'referrer' => $this->referrer,
            'utm_source' => $this->utmSource,
            'utm_medium' => $this->utmMedium,
            'utm_campaign' => $this->utmCampaign,
            'utm_term' => $this->utmTerm,
            'utm_content' => $this->utmContent,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options | JSON_THROW_ON_ERROR);
    }
}
