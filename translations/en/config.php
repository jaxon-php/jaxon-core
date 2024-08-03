<?php

return [
    'errors' => [
        'data' => [
            'depth' => "Incorrect depth :depth reached while setting option :key",
            'missing' => "Option :key missing in config data",
        ],
        'file' => [
            'access' => "Unable to access config file at :path",
            'content' => "Unable to get data array from config file at :path",
            'extension' => "The config file extension is not supported :path",
        ],
        'yaml' => [
            'install' => "The Yaml package for PHP is not installed",
        ],
    ],
];
