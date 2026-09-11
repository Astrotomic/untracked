<?php

namespace App\Analytics;

use App\Analytics\Drivers\UapUserAgentDriver;
use App\Analytics\Normalizers\BrowserNormalizer;
use App\Analytics\Normalizers\OperatingSystemNormalizer;
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
        return new UapUserAgentDriver(
            Parser::create(),
            $this->container->make(BrowserNormalizer::class),
            $this->container->make(OperatingSystemNormalizer::class),
        );
    }
}
