<?php

/**
 * LibraryInterface.php
 *
 * Interface for javascript dialog library adapters.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-dialogs
 */

namespace Jaxon\App\Dialog\Library;

interface LibraryInterface
{
    /**
     * Get the library name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get the CSS urls
     *
     * @return array
     */
    public function getCssUrls(): array;

    /**
     * Get the CSS header code
     *
     * @return string
     */
    public function getCssCode(): string;

    /**
     * Get the javascript files
     *
     * @return array
     */
    public function getJsUrls(): array;

    /**
     * Get the javascript code
     *
     * @return string
     */
    public function getJsCode(): string;
}
