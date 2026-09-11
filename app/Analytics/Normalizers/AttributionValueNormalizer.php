<?php

namespace App\Analytics\Normalizers;

use Illuminate\Support\Str;

class AttributionValueNormalizer
{
    public function normalize(?string $value): ?string
    {
        $value = trim((string) $value);
        $value = preg_replace('/[\x00-\x1F\x7F]/u', '', $value) ?? $value;
        $value = trim($value);

        return $value === '' ? null : Str::limit($value, 255, '');
    }
}
