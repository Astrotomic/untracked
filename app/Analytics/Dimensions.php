<?php

namespace App\Analytics;

use App\Analytics\Enums\Device;
use App\Analytics\Enums\Format;

readonly class Dimensions
{
    public function __construct(
        public string $path,
        public string $country,
        public string $browser,
        public string $os,
        public Device $device,
        public Format $format,
        public ?string $referrer = null,
        public ?string $utmSource = null,
        public ?string $utmMedium = null,
        public ?string $utmCampaign = null,
        public ?string $utmTerm = null,
        public ?string $utmContent = null,
    ) {}

    public function isBot(): bool
    {
        return $this->browser === 'Bot'
            || $this->os === 'Bot'
            || $this->device === Device::Bot;
    }

    public function value(Metric $metric): ?string
    {
        return match ($metric) {
            Metric::Path => $this->path,
            Metric::Country => $this->country,
            Metric::Browser => $this->browser,
            Metric::OperatingSystem => $this->os,
            Metric::Device => $this->device->value,
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
