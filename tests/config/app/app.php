<?php

use Lagdo\TwitterFeed\Package as TwitterPackage;
use Jaxon\Tests\Ns\Lib\ServiceInterface;
use Jaxon\Tests\Ns\Lib\Service;
use Jaxon\Tests\Ns\Lib\ServiceAuto;

$defsDir = rtrim(realpath(__DIR__ . '/../../defs'), '/');
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
        'classes' => [
            'Sample' => "$defsDir/sample.php",
            TheClass::class,
        ],
        'directories' => [
            __DIR__ . '/../../dir',
            __DIR__ . '/../../Ns/Ajax' => [
                'namespace' => "Jaxon\\Tests\\Ns\\Ajax",
                'autoload' => false,
            ],
            __DIR__ . '/../dir_ns' => "Jaxon\\NsTests",
        ],
        'packages' => [
            TwitterPackage::class,
        ],
        'container' => [
            'val' => [
                'service_config' => [
                    'key1' => 'value1',
                    'key2' => 'value2',
                ],
            ],
            'set' => [
                Service::class => function($c) {
                    return new Service($c->g('service_config'));
                }
            ],
            'alias' => [
                ServiceInterface::class => Service::class,
            ],
            'auto' => [
                ServiceAuto::class,
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
                'class' => 'Jxn',
            ],
        ],
    ],
];
