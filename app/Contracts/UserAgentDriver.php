<?php

namespace App\Contracts;

use App\Values\UserAgent;

interface UserAgentDriver
{
    public function resolve(string $userAgent): UserAgent;
}
