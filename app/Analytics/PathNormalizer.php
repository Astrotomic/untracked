<?php

namespace App\Analytics;

use Illuminate\Support\Str;

class PathNormalizer
{
    public function normalize(string $value): string
    {
        $path = parse_url($value, PHP_URL_PATH);
        $path = is_string($path) && $path !== '' ? $path : '/';

        return Str::limit('/'.ltrim($path, '/'), 500, '');
    }
}
