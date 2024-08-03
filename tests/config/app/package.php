<?php

return [
    'app' => [
        'packages' => [
            // TwitterPackage::class => [],
            SamplePackage::class => [],
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
