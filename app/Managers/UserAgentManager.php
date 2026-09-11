<?php

namespace App\Managers;

use App\Concerns\Resolvable;
use App\Contracts\UserAgentDriver;
use App\Drivers\UserAgent\UapUserAgentDriver;
use App\Normalizers\BrowserNormalizer;
use App\Normalizers\OperatingSystemNormalizer;
use Illuminate\Support\Manager;
use UAParser\Parser;

final class UserAgentManager extends Manager
{
    use Resolvable;

    public function getDefaultDriver(): string
    {
        return config()->string('analytics.user_agent.driver', 'uap');
    }

    public function driver($driver = null): UserAgentDriver
    {
        return parent::driver($driver);
    }

    protected function createDriver($driver): UserAgentDriver
    {
        return parent::createDriver($driver);
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
