<?php

namespace App\Analytics;

enum Metric: string
{
    case Path = 'path';
    case Country = 'country';
    case Browser = 'browser';
    case OperatingSystem = 'os';
    case Device = 'device';
    case Format = 'format';
}
