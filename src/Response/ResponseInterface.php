<?php

namespace Jaxon\Response;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

interface ResponseInterface
{
    /**
     * Get the content type, which is always set to 'text/json'
     *
     * @return string
     */
    public function getContentType(): string;

    /**
     * Get the commands in the response
     *
     * @return array
     */
    public function getCommands(): array;

    /**
     * Get the number of commands in the response
     *
     * @return int
     */
    public function getCommandCount(): int;

    /**
     * Clear all the commands already added to the response
     *
     * @return void
     */
    public function clearCommands();

    /**
     * Merge the commands with those in this <Response> object
     *
     * @param array $aCommands    The commands to merge
     * @param bool $bBefore    Add the new commands to the beginning of the list
     *
     * @return void
     */
    public function appendCommands(array $aCommands, bool $bBefore = false);

    /**
     * Merge the response commands with those in this <Response> object
     *
     * @param ResponseInterface    The <Response> object
     * @param bool $bBefore    Add the new commands to the beginning of the list
     *
     * @return void
     */
    public function appendResponse(ResponseInterface $xResponse, bool $bBefore = false);

    /**
     * Add a command to display a debug message to the user
     *
     * @param string $sMessage    The message to be displayed
     *
     * @return ResponseInterface
     */
    public function debug(string $sMessage): ResponseInterface;

    /**
     * Return the output, generated from the commands added to the response, that will be sent to the browser
     *
     * @return string
     */
    public function getOutput(): string;

    /**
     * Convert this response to a PSR7 response object
     *
     * @return PsrResponseInterface
     */
    public function toPsr(): PsrResponseInterface;
}
