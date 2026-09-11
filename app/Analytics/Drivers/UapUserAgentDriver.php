<?php

namespace App\Analytics\Drivers;

use App\Analytics\Contracts\UserAgentDriver;
use App\Analytics\Enums\Device;
use App\Analytics\Normalizers\BrowserNormalizer;
use App\Analytics\Normalizers\OperatingSystemNormalizer;
use App\Analytics\UserAgent;
use UAParser\Parser;

readonly class UapUserAgentDriver implements UserAgentDriver
{
    public function __construct(
        private Parser $parser,
        private BrowserNormalizer $browsers,
        private OperatingSystemNormalizer $operatingSystems,
    ) {}

    public function resolve(string $userAgent): UserAgent
    {
        $result = $this->parser->parse($userAgent);
        $browserFamily = (string) $result->ua->family;
        $osFamily = (string) $result->os->family;
        $deviceFamily = (string) $result->device->family;

        if ($this->isBot($userAgent, $browserFamily, $deviceFamily)) {
            return new UserAgent('Bot', 'Bot', Device::Bot);
        }

        $os = $this->operatingSystems->normalize($osFamily);

        return new UserAgent(
            $this->browsers->normalize($browserFamily),
            $os,
            Device::normalize($deviceFamily, $os, $userAgent),
        );
    }

    private function isBot(string $userAgent, string $browserFamily, string $deviceFamily): bool
    {
        if (strcasecmp($deviceFamily, 'Spider') === 0) {
            return true;
        }

        return preg_match('/bot|crawler|spider|headless/i', $browserFamily.' '.$userAgent) === 1;
    }
}
