<?php

namespace App\Normalizers;

use App\Concerns\Resolvable;
use App\Contracts\Normalizer;
use League\ISO3166\ISO3166;

final readonly class CountryNormalizer implements Normalizer
{
    use Resolvable;

    public function __construct(private ISO3166 $iso3166) {}

    public function normalize(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        return $this->resolve($value)['alpha2'] ?? null;
    }

    /**
     * @return null|array{name: string, alpha2: string, alpha3: string, numeric: numeric-string, currency: string[]}
     */
    private function resolve(string $value): ?array
    {
        return $this->lookup($this->iso3166->alpha2(...), $value)
            ?? $this->lookup($this->iso3166->alpha3(...), $value)
            ?? $this->lookup($this->iso3166->numeric(...), $value)
            ?? $this->lookup($this->iso3166->exactName(...), $value);
    }

    /**
     * @return null|array{name: string, alpha2: string, alpha3: string, numeric: numeric-string, currency: string[]}
     */
    private function lookup(callable $lookup, string $value): ?array
    {
        return rescue(fn () => $lookup($value));
    }
}
