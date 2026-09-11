<?php

namespace App\Drivers\Ip;

use App\Contracts\IpDriver;
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
        } catch (Throwable) {
            return null;
        }

        return $this->normalize($country);
    }

    private function normalize(?string $country): ?string
    {
        if ($country === null) {
            return null;
        }

        $country = strtoupper($country);

        return preg_match('/^[A-Z]{2}$/', $country) === 1 ? $country : null;
    }
}
