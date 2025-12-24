<?php

$baseDir = realpath(dirname(__DIR__, 2) . '');
$defsDir = realpath(dirname(__DIR__, 2) . '/src');
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
