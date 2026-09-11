<?php

namespace App\Analytics;

use App\Analytics\Enums\Device;

readonly class UserAgent
{
    public function __construct(
        public string $browser,
        public string $os,
        public Device $device,
    ) {}

    public function isBot(): bool
    {
        return $this->browser === 'Bot'
            || $this->os === 'Bot'
            || $this->device === Device::Bot;
    }
}
