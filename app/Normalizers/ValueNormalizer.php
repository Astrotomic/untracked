<?php

namespace App\Normalizers;

use App\Concerns\Resolvable;
use App\Contracts\Normalizer;
use Illuminate\Support\Str;

final readonly class ValueNormalizer implements Normalizer
{
    use Resolvable;

    public function normalize(?string $value): ?string
    {
        return Str::of((string) $value)
            ->trim()
            ->replaceMatches('/[\x00-\x1F\x7F]/u', '')
            ->trim()
            ->limit(255)
            ->toString() ?: null;
    }
}
