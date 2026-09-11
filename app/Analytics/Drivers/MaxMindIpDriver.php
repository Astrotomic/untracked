<?php

namespace App\Analytics\Drivers;

use App\Analytics\Contracts\IpDriver;
use GeoIp2\Database\Reader;
use Throwable;

readonly class MaxMindIpDriver implements IpDriver
{
    public function __construct(private string $database) {}

    public function country(string $ip): string
    {
        if (! is_file($this->database)) {
            return 'XX';
        }

        try {
            $country = (new Reader($this->database))->country($ip)->country->isoCode;
        } catch (Throwable) {
            return 'XX';
        }

        return $this->normalize((string) $country);
    }

    private function normalize(string $country): string
    {
        $country = strtoupper($country);

        return preg_match('/^[A-Z]{2}$/', $country) === 1 ? $country : 'XX';
    }
}
