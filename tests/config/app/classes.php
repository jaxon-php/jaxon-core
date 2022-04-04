<?php

$defsDir = rtrim(realpath(__DIR__ . '/../../src'), '/');
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
