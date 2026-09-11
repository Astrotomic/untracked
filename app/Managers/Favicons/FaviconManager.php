<?php

namespace App\Managers\Favicons;

use App\Concerns\Resolvable;
use App\Contracts\FaviconDriver;
use App\Managers\Favicons\Drivers\DuckDuckGoFaviconDriver;
use Illuminate\Support\Manager;

final class FaviconManager extends Manager
{
    use Resolvable;

    public function url(string $domain): string
    {
        return $this->driver()->url($domain, config()->integer('favicons.size', 32));
    }

    public function clientUrl(string $client): ?string
    {
        /** @var array<string, string> $clients */
        $clients = config('favicons.clients', []);
        $domain = $clients[$client] ?? null;

        return $domain === null ? null : $this->url($domain);
    }

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
