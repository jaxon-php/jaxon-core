<?php

/**
 * CodeGeneratorInterface.php - Code generator interface
 *
 * Any class generating css or js code must implement this interface.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Plugin;

interface CodeGeneratorInterface
{
    /**
     * Get the value to be hashed
     *
     * @return string
     */
    public function getHash(): string;

    /**
     * Get the HTML tags to include CSS code and files into the page
     *
     * The code must be enclosed in the appropriate HTML tags.
     *
     * @return string
     */
    public function getCss(): string;

    /**
     * Get the HTML tags to include javascript code and files into the page
     *
     * The code must be enclosed in the appropriate HTML tags.
     *
     * @return string
     */
    public function getJs(): string;

    /**
     * Get the javascript code to include into the page
     *
     * The code must NOT be enclosed in HTML tags.
     *
     * @return string
     */
    public function getScript(): string;
}
