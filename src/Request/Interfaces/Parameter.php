<?php

/**
 * Parameter.php - A parameter of a Jaxon request
 *
 * This class is used to create client side requests to the Jaxon functions and callable objects.
 *
 * @package jaxon-core
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-2-Clause BSD 2-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request\Interfaces;

interface Parameter
{
    /**
     * Get the parameter type
     * 
     * @return string
     */
    public function getType();

    /**
     * Get the parameter value
     * 
     * @return mixed
     */
    public function getValue();
}
