<?php

namespace App\Analytics\Contracts;

use App\Analytics\UserAgent;

interface UserAgentDriver
{
    public function resolve(string $userAgent): UserAgent;
}
