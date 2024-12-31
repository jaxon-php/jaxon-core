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

namespace Jaxon\Plugin\Response\Dialog\Library;

trait DialogLibraryTrait
{
    /**
     * The dialog library helper
     *
     * @var DialogLibraryHelper
     */
    protected $xHelper;

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
     * Get the library base URI
     *
     * @return string
     */
    public function getUri(): string
    {
        return '';
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
