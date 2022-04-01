<?php

use Lagdo\TwitterFeed\Package as TwitterPackage;

return [
    'app' => [
        'packages' => [
            TwitterPackage::class => [],
        ],
    ],
    'lib' => [
        'core' => [
            'debug' => [
                'on' => false,
            ],
            'request' => [
                'uri' => 'ajax.php',
            ],
            'prefix' => [
                'class' => 'Jxn',
            ],
        ],
    ],
];
