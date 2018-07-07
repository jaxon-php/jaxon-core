<?php

return [
    'errors' => [
        'debug' => [
            'ts-message' => "** Jaxon Error Log - :timestamp ** :message \n",
            'write-log' => "Jaxon was unable to write to the error log file: :file",
            'message' => "PHP Error Messages: :message",
        ],
        'response' => [
            'result.invalid' => "An invalid response was returned while processing this request.",
            'data.invalid' => "The Jaxon response object could not load commands as the data provided was not valid.",
        ],
        // A afficher lorsque l'exception DetectUri est lancée.
        'uri' => [
            'detect' => [
                'message' => "Jaxon failed to automatically identify your Request URI.",
                'advice' => "Please set the Request URI explicitly when you instantiate the Jaxon object.",
            ],
        ],
        'request' => [
            'conversion' => "The incoming Jaxon data could not be converted from UTF-8.",
        ],
        'mismatch' => [
            'content-types' => "Cannot mix content types in a single response: :type",
            'encodings' => "Cannot mix character encodings in a single response: :encoding",
            'entities' => "Cannot mix output entities (true/false) in a single response: :entities",
            'types' => "Cannot mix response types while processing a single request: :class",
        ],
        'events' => [
            'invalid' => "Invalid event request received; no event was registered with the name :name.",
        ],
        'functions' => [
            'invalid' => "Invalid function request received; no request processor found with the name :name.",
            'invalid-declaration' => "Invalid function declaration.",
        ],
        'objects' => [
            'invalid' => "Invalid object request received; no object :class or method :method found.",
            'instance' => "To register a callable object, please provide an instance of the desired class.",
        ],
        'register' => [
            'method' => "Failed to locate registration method for the following: :args",
            'invalid' => "Attempt to register invalid plugin: :name; " .
                "should be derived from Jaxon\\Plugin\\Request or Jaxon\\Plugin\\Response.",
        ],
        'component' => [
            'load' => "The :name javascript component could not be included. Perhaps the URL is incorrect?\\nURL: :url",
        ],
        'output' => [
            'already-sent' => "Output has already been sent to the browser at :location.",
            'advice' => "Please make sure the command \$jaxon->processRequest() is placed before this.",
        ],
        'magic' => [
            'get' => "Trying to read unknown property :name with magic property __get at line :line in file :file.",
            'set' => "Trying to write unknown property :name with magic property __set at line :line in file :file.",
        ],
    ],
];
