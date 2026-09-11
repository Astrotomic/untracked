<?php

namespace App\Analytics;

use App\Analytics\Enums\Browser;
use App\Analytics\Enums\Device;
use App\Analytics\Enums\OperatingSystem;

readonly class UserAgent
{
    public function __construct(
        public Browser $browser,
        public OperatingSystem $os,
        public Device $device,
    ) {}

    public function isBot(): bool
    {
        return $this->browser === Browser::Bot
            || $this->os === OperatingSystem::Bot
            || $this->device === Device::Bot;
    }
}
