<?php

/**
 * ContainerInterface.php - Interface for dependency injection
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\DI;

interface ContainerInterface
{
    /**
     * Check if a given class is defined in the container
     *
     * @param string                $sClass             A full class name
     *
     * @return bool
     */
    public function has($sClass);

    /**
     * Get a class instance
     *
     * @param string                $sClass             A full class name
     *
     * @return mixed                The class instance
     */
    public function get($sClass);
}
