<?php

use Jaxon\Dialogs\Bootbox\BootboxLibrary;
use Jaxon\Tests\Ns\Lib\Service;
use Jaxon\Tests\Ns\Lib\ServiceAuto;
use Jaxon\Tests\Ns\Lib\ServiceInterface;
use Lagdo\TwitterFeed\Package as TwitterPackage;

$testDir = realpath(__DIR__ . '/../..');
$defsDir = realpath(__DIR__ . '/../../src');
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
            $testDir . '/src/dir',
            $testDir . '/src/Ns/Ajax' => [
                'namespace' => "Jaxon\\Tests\\Ns\\Ajax",
                'autoload' => false,
            ],
            $testDir . '/src/dir_ns' => "Jaxon\\NsTests",
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
        'options' => [
            'views' => [
                'default' => 'jaxon',
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
        'dialogs' => [
            'default' => [
                'modal' => BootboxLibrary::NAME,
                'message' => BootboxLibrary::NAME,
                'question' => BootboxLibrary::NAME,
            ],
            'assets' => [
                'include' => [
                    'all' => true,
                ],
            ],
        ],
        'js' => [
            'app' => [
            ],
        ],
        'assets' => [
            'include' => [
                'all' => false,
            ],
        ],
    ],
];
