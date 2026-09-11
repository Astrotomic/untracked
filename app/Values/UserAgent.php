<?php

namespace App\Values;

use App\Enums\Device;
use App\Normalizers\BrowserNormalizer;
use App\Normalizers\OperatingSystemNormalizer;

final readonly class UserAgent
{
    public static function from(string $browser, string $os, string|Device $device): self
    {
        $browser = BrowserNormalizer::make()->normalize($browser);
        $os = OperatingSystemNormalizer::make()->normalize($os);
        $device = is_string($device) ? Device::from($device) : $device;

        return new self(
            browser: $browser,
            os: $os,
            device: $device,
        );
    }

    public function __construct(
        public string $browser,
        public string $os,
        public Device $device,
    ) {}

    public function isBot(): bool
    {
        return strcasecmp($this->browser, 'bot') === 0
            || strcasecmp($this->os, 'bot') === 0
            || $this->device === Device::Bot;
    }
}
