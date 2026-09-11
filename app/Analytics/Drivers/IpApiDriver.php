<?php

namespace App\Analytics\Drivers;

use App\Analytics\Contracts\IpDriver;
use Illuminate\Support\Facades\Http;
use Throwable;

readonly class IpApiDriver implements IpDriver
{
    public function __construct(
        private string $url,
        private int $timeout,
    ) {}

    public function country(string $ip): string
    {
        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->get(rtrim($this->url, '/').'/'.rawurlencode($ip), [
                    'fields' => 'status,countryCode',
                ]);

            if (! $response->successful() || $response->json('status') !== 'success') {
                return 'XX';
            }

            $country = strtoupper((string) $response->json('countryCode'));
        } catch (Throwable) {
            return 'XX';
        }

        return preg_match('/^[A-Z]{2}$/', $country) === 1 ? $country : 'XX';
    }
}
