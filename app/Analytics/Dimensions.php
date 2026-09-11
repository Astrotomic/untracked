<?php

namespace App\Analytics;

use App\Analytics\Enums\Browser;
use App\Analytics\Enums\Device;
use App\Analytics\Enums\Format;
use App\Analytics\Enums\OperatingSystem;

readonly class Dimensions
{
    public function __construct(
        public string $path,
        public string $country,
        public Browser $browser,
        public OperatingSystem $os,
        public Device $device,
        public Format $format,
    ) {}

    public function isBot(): bool
    {
        return $this->browser === Browser::Bot
            || $this->os === OperatingSystem::Bot
            || $this->device === Device::Bot;
    }

    public function value(Metric $metric): string
    {
        return match ($metric) {
            Metric::Path => $this->path,
            Metric::Country => $this->country,
            Metric::Browser => $this->browser->value,
            Metric::OperatingSystem => $this->os->value,
            Metric::Device => $this->device->value,
            Metric::Format => $this->format->value,
        };
    }
}
