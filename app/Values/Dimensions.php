<?php

namespace App\Values;

use App\Enums\Format;
use App\Enums\Metric;
use App\Models\Website;
use App\Normalizers\CountryNormalizer;
use App\Normalizers\PathNormalizer;
use App\Normalizers\ReferrerNormalizer;
use App\Normalizers\ValueNormalizer;

final readonly class Dimensions
{
    public static function from(
        string $path,
        string $country,
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
        return new self(
            path: PathNormalizer::make()->normalize($path),
            country: CountryNormalizer::make()->normalize($country),
            userAgent: $userAgent,
            format: $format,
            referrer: ReferrerNormalizer::make()->normalize($referrer, $website->domain),
            utmSource: ValueNormalizer::make()->normalize($utmSource),
            utmMedium: ValueNormalizer::make()->normalize($utmMedium),
            utmCampaign: ValueNormalizer::make()->normalize($utmCampaign),
            utmTerm: ValueNormalizer::make()->normalize($utmTerm),
            utmContent: ValueNormalizer::make()->normalize($utmContent),
        );
    }

    public function __construct(
        public string $path,
        public string $country,
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
}
