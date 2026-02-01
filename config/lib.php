<?php

return [
    'core' => [
        'version'               => Jaxon\Jaxon::VERSION,
        'language'              => 'en',
        'encoding'              => 'utf-8',
        'decode_utf8'           => false,
        'prefix' => [
            'function'          => 'jaxon_',
            'class'             => 'Jaxon',
        ],
        'request' => [
            'mode'              => 'asynchronous',
            'method'            => 'POST', // W3C: Method is case-sensitive
        ],
        'response' => [
            'send'              => true,
        ],
        'bag' => [
            'readable'          => false,
            'editable'          => false,
        ],
        'debug' => [
            'on'                => false,
            'verbose'           => false,
        ],
        'process' => [
            'exit'              => true,
            'timeout'           => 6000,
        ],
        'error' => [
            'handle'            => false,
            'log_file'          => '',
        ],
    ],
    'js' => [
        'lib' => [
            'output_id'         => 0,
            'queue_size'        => 0,
            'load_timeout'      => 2000,
            'show_status'       => false,
            'show_cursor'       => true,
        ],
        'app' => [
            'dir'               => '',
            'options'           => '',
        ],
    ],
];
