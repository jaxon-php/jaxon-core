<?php

/**
 * Validator.php - Trait for validation functions
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Utils\Traits;

use Jaxon\Utils\Container;

trait Validator
{
    /**
     * Validate a function name
     *
     * @param string        $sName            The function name
     *
     * @return bool            True if the function name is valid, and false if not
     */
    public function validateFunction($sName)
    {
        return Container::getInstance()->getValidator()->validateFunction($sName);
    }

    /**
     * Validate an event name
     *
     * @param string        $sName            The event name
     *
     * @return bool            True if the event name is valid, and false if not
     */
    public function validateEvent($sName)
    {
        return Container::getInstance()->getValidator()->validateEvent($sName);
    }

    /**
     * Validate a class name
     *
     * @param string        $sName            The class name
     *
     * @return bool            True if the class name is valid, and false if not
     */
    public function validateClass($sName)
    {
        return Container::getInstance()->getValidator()->validateClass($sName);
    }

    /**
     * Validate a method name
     *
     * @param string        $sName            The function name
     *
     * @return bool            True if the method name is valid, and false if not
     */
    public function validateMethod($sName)
    {
        return Container::getInstance()->getValidator()->validateMethod($sName);
    }
}
