<?php

/**
 * DialogLibraryTrait.php
 *
 * Common functions for javascript dialog libraries.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-dialogs
 */

namespace Jaxon\App\Dialog\Library;

use Jaxon\Response\Response;

trait DialogLibraryTrait
{
    /**
     * The dialog library helper
     *
     * @var DialogLibraryHelper
     */
    protected $xHelper;

    /**
     * @var Response
     */
    protected $xResponse = null;

    /**
     * For MessageInterface, tells if the calls to the functions shall
     * add commands to the response or return the js code. By default, they add commands.
     *
     * @var bool
     */
    protected $bReturnCode = false;

    /**
     * Get the library name
     *
     * @return string
     */
    abstract public function getName(): string;

    /**
     * Get the helper
     *
     * @return DialogLibraryHelper
     */
    final public function helper(): DialogLibraryHelper
    {
        return $this->xHelper;
    }

    /**
     * Set the response to attach the messages to.
     *
     * @param Response $xResponse    Whether to return the code
     *
     * @return void
     */
    final public function setResponse(Response $xResponse)
    {
        $this->xResponse = $xResponse;
    }

    /**
     * Get the <Jaxon\Response\Response> object
     *
     * @return Response|null
     */
    final protected function response(): ?Response
    {
        return $this->xResponse;
    }

    /**
     * @param bool $bReturnCode
     *
     * @return void
     */
    final public function setReturnCode(bool $bReturnCode)
    {
        $this->bReturnCode = $bReturnCode;
    }

    /**
     * Check if the library should return the js code or run it in the browser.
     *
     * @return bool
     */
    final protected function returnCode(): bool
    {
        return $this->bReturnCode;
    }

    /**
     * Add a client side plugin command to the response object
     *
     * @param array $aAttributes The attributes of the command
     * @param mixed $xData The data to be added to the command
     *
     * @return void
     */
    final public function addCommand(array $aAttributes, $xData)
    {
        // This is usually the response plugin name. We set the library name instead.
        $aAttributes['plg'] = $this->getName();
        $this->xResponse->addCommand($aAttributes, $xData);
    }

    /**
     * Get the library base URI
     *
     * @return string
     */
    public function getUri(): string
    {
        return 'https://cdn.jaxon-php.org/libs';
    }

    /**
     * Get the library subdir for the URI
     *
     * @return string
     */
    public function getSubdir(): string
    {
        return '';
    }

    /**
     * Get the library version for the URI
     *
     * @return string
     */
    public function getVersion(): string
    {
        return '';
    }

    /**
     * Get the CSS header code and file includes
     *
     * @return string
     */
    public function getJs(): string
    {
        return '';
    }

    /**
     * Get the javascript header code and file includes
     *
     * @return string
     */
    public function getCss(): string
    {
        return '';
    }

    /**
     * Get the javascript code to be printed into the page
     *
     * @return string
     */
    public function getScript(): string
    {
        return '';
    }

    /**
     * Get the javascript code to be executed on page load
     *
     * @return string
     */
    public function getReadyScript(): string
    {
        return '';
    }
}
