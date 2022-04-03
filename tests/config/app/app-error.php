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
