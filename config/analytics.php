<?php

return [
    'user_agent' => [
        'driver' => env('ANALYTICS_USER_AGENT_DRIVER', 'uap'),
    ],

    'ip' => [
        'driver' => env('ANALYTICS_IP_DRIVER', 'maxmind'),

        'maxmind' => [
            'database' => env('ANALYTICS_MAXMIND_DATABASE', storage_path('app/GeoLite2-Country.mmdb')),
        ],

        'ip-api' => [
            'url' => env('ANALYTICS_IP_API_URL', 'http://ip-api.com/json'),
            'timeout' => (int) env('ANALYTICS_IP_API_TIMEOUT', 2),
        ],
    ],
];
