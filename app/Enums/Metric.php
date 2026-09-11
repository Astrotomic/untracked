<?php

namespace App\Enums;

enum Metric: string
{
    case Path = 'path';
    case Country = 'country';
    case Browser = 'browser';
    case OperatingSystem = 'os';
    case Device = 'device';
    case Format = 'format';
    case Referrer = 'referrer';
    case UtmSource = 'utm_source';
    case UtmMedium = 'utm_medium';
    case UtmCampaign = 'utm_campaign';
    case UtmTerm = 'utm_term';
    case UtmContent = 'utm_content';
}
