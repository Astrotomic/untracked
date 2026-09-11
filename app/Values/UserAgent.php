<?php

namespace App\Values;

use App\Enums\Device;
use App\Normalizers\ClientNormalizer;
use App\Normalizers\OperatingSystemNormalizer;

final readonly class UserAgent
{
    public static function from(?string $client, ?string $os, string|Device $device): self
    {
        $client = $client === null ? null : ClientNormalizer::make()->normalize($client);
        $os = $os === null ? null : OperatingSystemNormalizer::make()->normalize($os);
        $device = is_string($device) ? Device::from($device) : $device;

        return new self(
            client: $client,
            os: $os,
            device: $device,
        );
    }

    public function __construct(
        public ?string $client,
        public ?string $os,
        public Device $device,
    ) {}

    public function isBot(): bool
    {
        return $this->device === Device::Bot;
    }
}
