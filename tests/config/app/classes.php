<?php

$defsDir = rtrim(realpath(dirname(__DIR__, 2) . '/src'), '/');
require_once "$defsDir/classes.php";

return [
    'app' => [
        'classes' => [
            'Sample' => "$defsDir/sample.php",
            TheClass::class,
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
