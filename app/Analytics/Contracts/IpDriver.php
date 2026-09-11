<?php

namespace App\Analytics\Contracts;

interface IpDriver
{
    public function country(string $ip): string;
}
