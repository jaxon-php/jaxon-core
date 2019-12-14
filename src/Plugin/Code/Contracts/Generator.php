<?php

/**
 * Generator.php - Code generator interface
 *
 * Any class generating css or js code must implement this interface.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Plugin\Code\Contracts;

interface Generator
{
    /**
     * Get the value to be hashed
     *
     * @return string
     */
    public function getHash();

    /**
     * Get the HTML tags to include CSS code and files into the page
     *
     * The code must be enclosed in the appropriate HTML tags.
     *
     * @return string
     */
    public function getCss();

    /**
     * Get the HTML tags to include javascript code and files into the page
     *
     * The code must be enclosed in the appropriate HTML tags.
     *
     * @return string
     */
    public function getJs();

    /**
     * Get the javascript code to include into the page
     *
     * The code must NOT be enclosed in HTML tags.
     *
     * @return string
     */
    public function getScript();

    /**
     * Get the javascript code to execute after page load
     *
     * The code must NOT be enclosed in HTML tags.
     *
     * @return string
     */
    public function getReadyScript();

    /**
     * Whether to include the getReadyScript() in the generated code.
     *
     * @return boolean
     */
    public function readyEnabled();

    /**
     * Whether to export the getReadyScript() in external javascript files.
     *
     * @return boolean
     */
    public function readyInlined();
}
