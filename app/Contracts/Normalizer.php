<?php

namespace App\Contracts;

interface Normalizer
{
    public function normalize(?string $value): ?string;
}
