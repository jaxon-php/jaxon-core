<?php

/**
 * DI.php - Trait for dependency injection
 *
 * Define closures for instanciating classes, and return class instances.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Utils\Traits;

use Jaxon\Utils\Container;

trait DI
{
    /**
     * Get a class instance
     *
     * @param string                $sClass             A full class name
     *
     * @return object               The class instance
     */
    public function diGet($sClass)
    {
        return Container::getInstance()->get($sClass);
    }
}
