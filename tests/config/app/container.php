<?php

use Jaxon\Tests\Ns\Lib\Service;
use Jaxon\Tests\Ns\Lib\ServiceAuto;
use Jaxon\Tests\Ns\Lib\ServiceAutoClassParam;
use Jaxon\Tests\Ns\Lib\ServiceAutoParam;
use Jaxon\Tests\Ns\Lib\ServiceInterface;

return [
    'app' => [
        'container' => [
            'val' => [
                'service_config' => [
                    'key1' => 'value1',
                    'key2' => 'value2',
                ],
            ],
            'set' => [
                Service::class => function($c) {
                    $service = new Service($c->g('service_config'));
                    $service->setSource('Class only');
                    return $service;
                },
                Service::class . ' $serv' => function($c) {
                    $service = new Service($c->g('service_config'));
                    $service->setSource('Class + parameter');
                    return $service;
                },
                '$service' => function($c) {
                    $service = new Service($c->g('service_config'));
                    $service->setSource('Parameter only');
                    return $service;
                },
            ],
            'alias' => [
                ServiceInterface::class => Service::class,
            ],
            'auto' => [
                ServiceAuto::class,
                ServiceAutoClassParam::class,
                ServiceAutoParam::class,
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
                'class' => 'Jxn',
            ],
        ],
    ],
];
