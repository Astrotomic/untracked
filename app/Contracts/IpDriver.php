<?php

namespace App\Contracts;

interface IpDriver
{
    public function country(string $ip): string;
}
