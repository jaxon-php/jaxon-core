<?php

/**
 * Factory.php - Trait for Jaxon Request Factory
 *
 * Make functions of the Jaxon Request Factory class available to Jaxon classes.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Features\Request;

trait Factory
{
    /**
     * Return the javascript call to an Jaxon object method
     *
     * @return \Jaxon\Request\Request
     */
    public function call()
    {
        // Make the request
        return call_user_func_array([rq(get_class($this)), 'call'], func_get_args());
    }
}
