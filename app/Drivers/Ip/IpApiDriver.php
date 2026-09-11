<?php

namespace App\Drivers\Ip;

use App\Contracts\IpDriver;
use Illuminate\Support\Facades\Http;
use Throwable;

final readonly class IpApiDriver implements IpDriver
{
    public function __construct(
        private string $url,
        private int $timeout,
    ) {}

    public function country(string $ip): ?string
    {
        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->get(rtrim($this->url, '/').'/'.rawurlencode($ip), [
                    'fields' => 'status,countryCode',
                ]);

            if (! $response->successful() || $response->json('status') !== 'success') {
                return null;
            }

            $country = strtoupper((string) $response->json('countryCode'));
        } catch (Throwable) {
            return null;
        }

        return preg_match('/^[A-Z]{2}$/', $country) === 1 ? $country : null;
    }
}
