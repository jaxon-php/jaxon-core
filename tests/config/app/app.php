<?php

use Lagdo\TwitterFeed\Package as TwitterPackage;
use Jaxon\Tests\Ns\Lib\ServiceInterface;
use Jaxon\Tests\Ns\Lib\Service;
use Jaxon\Tests\Ns\Lib\ServiceAuto;

$baseDir = realpath(__DIR__ . '/../..');
$defsDir = realpath(__DIR__ . '/../../defs');
require_once "$defsDir/classes.php";
require_once "$defsDir/packages.php";

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
            $baseDir . '/dir',
            $baseDir . '/Ns/Ajax' => [
                'namespace' => "Jaxon\\Tests\\Ns\\Ajax",
                'autoload' => false,
            ],
            $baseDir . '/dir_ns' => "Jaxon\\NsTests",
        ],
        'packages' => [
            TwitterPackage::class => [],
            SamplePackage::class => [],
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
                'on' => true,
            ],
            'request' => [
                'uri' => 'http://example.test/path',
            ],
            'prefix' => [
                'function' => 'jxn_',
                'class' => 'Jxn',
            ],
        ],
        'js' => [
            'app' => [
                'export' => true,
                'dir' => $baseDir . '/script',
                'uri' => 'http://example.test/script',
            ],
        ],
        'assets' => [
            'include' => [
                'all' => false,
            ],
        ],
    ],
];
