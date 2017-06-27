<?php

/**
 * Minifier.php - Trait for minify functions
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Utils\Traits;

use Jaxon\Utils\Container;

trait Minifier
{
    /**
     * Minify javascript code
     *
     * @param string        $sJsFile            The javascript file to be minified
     * @param string        $sMinFile            The minified javascript file
     *
     * @return boolean        True if the file was minified
     */
    public function minify($sJsFile, $sMinFile)
    {
        return Container::getInstance()->getMinifier()->minify($sJsFile, $sMinFile);
    }
}
