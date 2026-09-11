<?php

namespace App\Drivers\Ip;

use App\Contracts\IpDriver;
use App\Normalizers\CountryNormalizer;
use GeoIp2\Database\Reader;
use Throwable;

final readonly class MaxMindIpDriver implements IpDriver
{
    public function __construct(private string $database) {}

    public function country(string $ip): ?string
    {
        if (! is_file($this->database)) {
            return null;
        }

        try {
            $country = (new Reader($this->database))->country($ip)->country->isoCode;

            return CountryNormalizer::make()->normalize($country);
        } catch (Throwable) {
            return null;
        }
    }
}
