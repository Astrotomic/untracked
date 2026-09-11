<?php

namespace App\Managers;

use App\Concerns\Resolvable;
use App\Contracts\IpDriver;
use App\Drivers\Ip\IpApiDriver;
use App\Drivers\Ip\MaxMindIpDriver;
use Illuminate\Support\Manager;

final class IpManager extends Manager
{
    use Resolvable;

    public function getDefaultDriver(): string
    {
        return config()->string('analytics.ip.driver', 'maxmind');
    }

    public function driver($driver = null): IpDriver
    {
        return parent::driver($driver);
    }

    protected function createDriver($driver): IpDriver
    {
        return parent::createDriver($driver);
    }

    protected function createMaxmindDriver(): MaxMindIpDriver
    {
        return new MaxMindIpDriver((string) config('analytics.ip.maxmind.database'));
    }

    protected function createIpApiDriver(): IpApiDriver
    {
        return new IpApiDriver(
            (string) config('analytics.ip.ip-api.url'),
            (int) config('analytics.ip.ip-api.timeout', 2),
        );
    }
}
