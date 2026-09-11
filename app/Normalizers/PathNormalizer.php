<?php

namespace App\Normalizers;

use App\Concerns\Resolvable;
use App\Contracts\Normalizer;
use Illuminate\Support\Str;

final readonly class PathNormalizer implements Normalizer
{
    use Resolvable;

    public function normalize(?string $value): string
    {
        return Str::of(parse_url($value, PHP_URL_PATH) ?: '')
            ->trim('/')
            ->limit(495)
            ->start('/')
            ->toString() ?: '/';
    }
}
