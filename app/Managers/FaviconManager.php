<?php

namespace App\Managers;

use App\Concerns\Resolvable;
use App\Contracts\FaviconDriver;
use App\Drivers\Favicon\DuckDuckGoFaviconDriver;
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
}
