<?php

namespace App\Managers\Ip;

use App\Contracts\IpDriver;
use App\Normalizers\CountryNormalizer;
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

            if ($response->failed() || $response->json('status') !== 'success') {
                return null;
            }

            return CountryNormalizer::make()->normalize($response->json('countryCode'));
        } catch (Throwable) {
            return null;
        }
    }
}
