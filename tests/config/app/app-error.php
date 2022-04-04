<?php

$baseDir = realpath(__DIR__ . '/../..');
$defsDir = realpath(__DIR__ . '/../../src');
require_once "$defsDir/classes.php";
require_once "$defsDir/packages.php";

return [
    'app' => 'App',
    'lib' => [
        'core' => [
            'debug' => [
                'on' => true,
            ],
            'prefix' => [
                'function' => 'jxn_',
                'class' => 'Jxn',
            ],
        ],
        'js' => [
            'app' => [
                'export' => true,
                'dir' => $baseDir . '/js',
                'uri' => 'http://example.test/js',
            ],
        ],
        'assets' => [
            'include' => [
                'all' => false,
            ],
        ],
    ],
];
