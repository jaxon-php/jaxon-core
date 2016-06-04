<?php

return array(
    'debug.function.include' => "From include file: :file => :output",
    'errors.debug.ts-message' => "** Jaxon Error Log - :timestamp ** :message \n",
    'errors.debug.write-log' => "Jaxon was unable to write to the error log file: :file",
    'errors.debug.message' => "PHP Error Messages: :message",
    'errors.response.result.invalid' => "An invalid response was returned while processing this request.",
    'errors.response.data.invalid' => "The Jaxon response object could not load commands as the data provided was not valid.",
    // A afficher lorsque l'exception DetectUri est lancée.
    'errors.uri.detect.message' => "Jaxon failed to automatically identify your Request URI.",
    'errors.uri.detect.advice' => "Please set the Request URI explicitly when you instantiate the Jaxon object.",
    'errors.request.conversion' => "The incoming Jaxon data could not be converted from UTF-8.",
    'errors.mismatch.content-types' => "Cannot mix content types in a single response: :type",
    'errors.mismatch.encodings' => "Cannot mix character encodings in a single response: :encoding",
    'errors.mismatch.entities' => "Cannot mix output entities (true/false) in a single response: :entities",
    'errors.mismatch.types' => "Cannot mix response types while processing a single request: :class",
    'errors.events.invalid' => "Invalid event request received; no event was registered with the name :name.",
    'errors.functions.invalid' => "Invalid function request received; no request processor found with the name :name.",
    'errors.functions.invalid-declaration' => "Invalid function declaration.",
    'errors.objects.invalid' => "Invalid object request received; no object :class or method :method found.",
    'errors.objects.instance' => "To register a callable object, please provide an instance of the desired class.",
    'errors.register.method' => "Failed to locate registration method for the following: :args",
    'errors.register.invalid' => "Attempt to register invalid plugin: :name; " .
        "should be derived from Jaxon\\Plugin\\Request or Jaxon\\Plugin\\Response.",
    'errors.component.load' => "The :name javascript component could not be included. Perhaps the URL is incorrect?\\nURL: :url",
    'errors.output.already-sent' => "Output has already been sent to the browser at :location.",
    'errors.output.advice' => "Please make sure the command \$jaxon->processRequest() is placed before this.",
);
