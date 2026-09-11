<?php

namespace App\Drivers\UserAgent;

use App\Contracts\UserAgentDriver;
use App\Enums\Device;
use App\Normalizers\OperatingSystemNormalizer;
use App\Values\UserAgent;
use UAParser\Parser;

final readonly class UapUserAgentDriver implements UserAgentDriver
{
    public function __construct(
        private Parser $parser,
    ) {}

    public function resolve(string $userAgent): UserAgent
    {
        $result = $this->parser->parse($userAgent);
        $osFamily = $result->os->family;

        $os = OperatingSystemNormalizer::make()->normalize($osFamily);

        return UserAgent::from(
            browser: $result->ua->family,
            os: $os,
            device: Device::normalize(
                family: $result->device->family,
                os: $os,
                userAgent: $userAgent
            )
        );
    }
}
