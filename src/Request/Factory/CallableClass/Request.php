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

namespace Jaxon\Request\Factory\CallableClass;

use Jaxon\Request\Support\CallableObject;

use function array_merge;
use function call_user_func_array;
use function rq;

class Request
{
    /**
     * The callable object this factory is attached to
     *
     * @var CallableObject
     */
    private $xCallable;

    /**
     * The class constructor
     *
     * @param CallableObject        $xCallable
     */
    public function __construct(CallableObject $xCallable)
    {
        $this->xCallable = $xCallable;
    }

    /**
     * Generate the javascript code for a call to a given method
     *
     * @param string    $sMethod
     * @param array     $aArguments
     *
     * @return string
     */
    public function __call(string $sMethod, array $aArguments)
    {
        // Make the request
        $factory = rq()->setCallable($this->xCallable);
        return call_user_func_array([$factory, 'call'], array_merge([$sMethod], $aArguments));
    }
}
