<?php

namespace App\Analytics;

use App\Analytics\Drivers\UapUserAgentDriver;
use Illuminate\Support\Manager;
use UAParser\Parser;

class UserAgentManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return (string) config('analytics.user_agent.driver', 'uap');
    }

    protected function createUapDriver(): UapUserAgentDriver
    {
        return new UapUserAgentDriver(Parser::create());
    }
}
