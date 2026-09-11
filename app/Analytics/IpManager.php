<?php

namespace App\Analytics;

use App\Analytics\Drivers\IpApiDriver;
use App\Analytics\Drivers\MaxMindIpDriver;
use Illuminate\Support\Manager;

class IpManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return (string) config('analytics.ip.driver', 'maxmind');
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
