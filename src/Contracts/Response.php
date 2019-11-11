<?php

namespace Jaxon\Contracts;

interface Response
{
    /**
     * Get the content type, which is always set to 'text/json'
     *
     * @return string
     */
    public function getContentType();

    /**
     * Get the configured character encoding
     *
     * @return string
     */
    public function getCharacterEncoding();

    /**
     * Used internally to generate the response headers
     *
     * @return void
     */
    public function sendHeaders();

    /**
     * Return the output, generated from the commands added to the response, that will be sent to the browser
     *
     * @return string
     */
    public function getOutput();

    /**
     * Print the output, generated from the commands added to the response, that will be sent to the browser
     *
     * @return void
     */
    public function printOutput();

    /**
     * Add a command to display a debug message to the user
     *
     * @param string        $sMessage            The message to be displayed
     *
     * @return \Jaxon\Plugin\Response
     */
    public function debug($sMessage);
}
