<?php

return [
    'default' => env('FAVICON_DRIVER', 'duckduckgo'),

    'drivers' => [
        'logo_dev' => [
            'token' => env('LOGO_DEV_TOKEN'),
        ],
    ],
];
