<?php

$defsDir = realpath(__DIR__ . '/../../src');
require_once "$defsDir/classes.php";

return [
    'app' => [
        'functions' => [
            'my_first_function' => "$defsDir/first.php",
            'my_second_function' => [
                'alias' => 'my_alias_function',
                'upload' => "'html_field_id'",
            ],
            'myMethod' => [
                'alias' => 'my_third_function',
                'class' => Sample::class,
            ],
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
                'function' => 'jxn_',
            ],
        ],
    ],
];
