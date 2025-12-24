<?php

$baseDir = dirname(__DIR__, 2) . '/src';

return [
    'app' => [
        'directories' => [
            $baseDir . '/dir',
            $baseDir . '/Ns/Ajax' => [
                'namespace' => "Jaxon\\Tests\\Ns\\Ajax",
                'autoload' => false,
            ],
            $baseDir . '/dir_ns' => "Jaxon\\NsTests",
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
