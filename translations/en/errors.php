<?php

return [
    'errors' => [
        'debug' => [
            'ts-message' => "** Jaxon Error Log - :timestamp ** :message \n",
            'write-log' => "Jaxon was unable to write to the error log file: :file",
            'message' => "PHP Error Messages: :message",
        ],
        'class' => [
            'invalid' => "Unable to find class with name :name.",
            'implements' => "The class :name does not implement the interface :interface.",
            'container' => "Unable to get an instance of class :name from the DI container.",
            'method' => "Unable to call method :method in class :class.",
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
            'plugin' => "Jaxon failed to find a plugin to process the request.",
        ],
        'mismatch' => [
            'content-types' => "Cannot mix content types in a single response: :type",
            'encodings' => "Cannot mix character encodings in a single response: :encoding",
            'entities' => "Cannot mix output entities (true/false) in a single response: :entities",
            'types' => "Cannot mix response types while processing a single request: :class",
        ],
        'functions' => [
            'call' => "An error occured during the call of function :name.",
            'invalid' => "Invalid function request received; no request processor found with the name :name.",
            'invalid-declaration' => "Invalid function declaration.",
        ],
        'objects' => [
            'call' => "An error occured during the call of method :method in of class :class.",
            'invalid' => "Invalid object request received; no object :class or method :method found.",
            'excluded' => "Trying to call the excluded method :method of class :class.",
            'instance' => "To register a callable object, please provide an instance of the desired class.",
            'invalid-declaration' => "Invalid object declaration.",
        ],
        'register' => [
            'plugin' => "No plugin with name :name to register a callable class or function.",
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
        'dialog' => [
            'library' => "There is no :type library with name :name",
        ],
        'app' => [
            'confirm' => [
                'nested' => "Calls to the confirm command cannot be nested.",
            ],
        ],
    ],
];
