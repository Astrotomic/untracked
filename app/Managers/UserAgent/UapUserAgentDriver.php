<?php

namespace App\Managers\UserAgent;

use App\Contracts\UserAgentDriver;
use App\Enums\Device;
use App\Normalizers\BotCompanyNormalizer;
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
        $botCompany = BotCompanyNormalizer::make()->normalize($userAgent);
        $device = $botCompany === null
            ? Device::normalize(
                family: $result->device->family,
                os: $os,
                userAgent: $userAgent,
            )
            : Device::Bot;

        if ($device === Device::Bot) {
            return new UserAgent(
                client: $botCompany,
                os: null,
                device: Device::Bot,
            );
        }

        return UserAgent::from(
            client: $result->ua->family,
            os: $os,
            device: $device,
        );
    }
}
