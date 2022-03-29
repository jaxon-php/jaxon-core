<?php

namespace Jaxon\Response;

abstract class AbstractResponse
{
    /**
     * Get the content type, which is always set to 'text/json'
     *
     * @return string
     */
    abstract public function getContentType(): string;

    /**
     * Merge the response commands from the specified <Response> object with
     * the response commands in this <Response> object
     *
     * @param AbstractResponse|array $mCommands    The <Response> object
     * @param bool $bBefore    Add the new commands to the beginning of the list
     *
     * @return void
     */
    public function appendResponse($mCommands, bool $bBefore = false)
    {}

    /**
     * Add a command to display a debug message to the user
     *
     * @param string $sMessage    The message to be displayed
     *
     * @return AbstractResponse
     */
    abstract public function debug(string $sMessage): AbstractResponse;

    /**
     * Return the output, generated from the commands added to the response, that will be sent to the browser
     *
     * @return string
     */
    abstract public function getOutput(): string;
}
