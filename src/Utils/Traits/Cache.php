<?php

/**
 * Cache.php - Trait for cache functions
 *
 * Make functions of the utils classes available to Jaxon classes.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Utils\Traits;

use Jaxon\Utils\Container;

trait Cache
{
    /**
     * Set a cache directory for the template engine
     *
     * @param string        $sCacheDir            The cache directory
     *
     * @return void
     */
    public function setCacheDir($sCacheDir)
    {
        return Container::getInstance()->getTemplate()->setCacheDir($sCacheDir);
    }
}
