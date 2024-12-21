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

use Jaxon\Response\AjaxResponse;
use JsonSerializable;

trait DialogLibraryTrait
{
    /**
     * The dialog library helper
     *
     * @var DialogLibraryHelper
     */
    protected $xHelper;

    /**
     * @var AjaxResponse
     */
    protected $xResponse = null;

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
    public function helper(): DialogLibraryHelper
    {
        return $this->xHelper;
    }

    /**
     * Set the response to attach the messages to.
     *
     * @param AjaxResponse $xResponse    Whether to return the code
     *
     * @return void
     */
    public function setResponse(AjaxResponse $xResponse)
    {
        $this->xResponse = $xResponse;
    }

    /**
     * Get the <Jaxon\Response\Response> object
     *
     * @return AjaxResponse|null
     */
    protected function response(): ?AjaxResponse
    {
        return $this->xResponse;
    }

    /**
     * Add a client side plugin command to the response object
     *
     * @param string $sName    The command name
     * @param array|JsonSerializable $aOptions    The command options
     *
     * @return void
     */
    public function addCommand(string $sName, array $aOptions = [])
    {
        // This is usually the response plugin name. We set the library name instead.
        $this->xResponse->addCommand($sName, $aOptions)
            ->setOption('plugin', $this->getName());
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
