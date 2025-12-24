<?php

use Jaxon\Tests\Ns\Lib\Service;
use Jaxon\Tests\Ns\Lib\ServiceAuto;
use Jaxon\Tests\Ns\Lib\ServiceInterface;

$testDir = realpath(dirname(__DIR__, 2) . '');
$defsDir = realpath(dirname(__DIR__, 2) . '/src');
require_once "$defsDir/classes.php";
require_once "$defsDir/packages.php";

return [
    'app' => [
        'metadata' => [
            'format' => 'annotations',
        ],
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
            $testDir . '/src/dir' => [
                'classes' => [
                    'ClassC' => [
                        'functions' => [
                            'methodCc' => [
                                'excluded' => true,
                            ],
                        ],
                    ],
                    'ClassD' => [
                        'excluded' => true,
                    ],
                ],
            ],
            $testDir . '/src/Ns/Ajax' => [
                'namespace' => "Jaxon\\Tests\\Ns\\Ajax",
                'autoload' => false,
            ],
            $testDir . '/src/dir_ns' => "Jaxon\\NsTests",
        ],
        'packages' => [
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
        'js' => [
            'app' => [
                'file' => 'assets',
            ],
        ],
        'assets' => [
            'include' => [
                'all' => false,
            ],
        ],
    ],
];
