<?php

/**
 * Factory.php - Jaxon Request Factory
 *
 * Create Jaxon client side requests to a given class.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Factory\CallableObject;

use Jaxon\DI\Container;
use Jaxon\Request\Support\CallableObject;

class Request
{
    /**
     * The callable object this factory is attached to
     *
     * @var CallableObject
     */
    private $xCallable;

    /**
     * Create a new Factory instance.
     *
     * @return void
     */
    public function __construct(CallableObject $xCallable)
    {
        $this->xCallable = $xCallable;
    }

    /**
     * Generate the corresponding javascript code for a call to any method
     *
     * @return string
     */
    public function __call($sMethod, $aArguments)
    {
        // Make the request
        $factory = Container::getInstance()->getRequestFactory()->setCallable($this->xCallable);
        return call_user_func_array([$factory, 'call'], array_merge([$sMethod], $aArguments));
    }
}
