<?php

namespace App\Managers;

use App\Concerns\Resolvable;
use App\Contracts\FaviconDriver;
use App\Managers\Favicon\DuckDuckGoFaviconDriver;
use App\Managers\Favicon\GoogleFaviconDriver;
use App\Managers\Favicon\LogoDevFaviconDriver;
use App\Managers\Favicon\UnavatarFaviconDriver;
use Illuminate\Support\Manager;

final class FaviconManager extends Manager
{
    use Resolvable;

    public function getDefaultDriver(): string
    {
        return config()->string('favicons.default', 'duckduckgo');
    }

    public function driver($driver = null): FaviconDriver
    {
        return parent::driver($driver);
    }

    protected function createDriver($driver): FaviconDriver
    {
        return parent::createDriver($driver);
    }

    protected function createDuckduckgoDriver(): DuckDuckGoFaviconDriver
    {
        return new DuckDuckGoFaviconDriver;
    }

    protected function createGstaticDriver(): GoogleFaviconDriver
    {
        return new GoogleFaviconDriver;
    }

    protected function createUnavatarDriver(): UnavatarFaviconDriver
    {
        return new UnavatarFaviconDriver;
    }

    protected function createLogoDevDriver(): LogoDevFaviconDriver
    {
        return new LogoDevFaviconDriver(
            config()->string('favicons.drivers.logo_dev.token', ''),
        );
    }
}
