<?php

/**
 * DialogLibraryInterface.php - Interface for javascript dialog library adapters.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-dialogs
 */

namespace Jaxon\App\Dialog;

use Jaxon\App\Dialog\Library\DialogLibraryInterface;

interface LibraryInterface extends DialogLibraryInterface
{
    /**
     * Get the library name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get the library base URI
     *
     * @return string
     */
    public function getUri(): string;

    /**
     * Get the library subdir for the URI
     *
     * @return string
     */
    public function getSubdir(): string;

    /**
     * Get the library version for the URI
     *
     * @return string
     */
    public function getVersion(): string;

    /**
     * Get the CSS header code and file includes
     *
     * @return string
     */
    public function getCss(): string;

    /**
     * Get the javascript header code and file includes
     *
     * @return string
     */
    public function getJs(): string;

    /**
     * Get the javascript code to be printed into the page
     *
     * @return string
     */
    public function getScript(): string;

    /**
     * Get the javascript code to be executed on page load
     *
     * @return string
     */
    public function getReadyScript(): string;
}
