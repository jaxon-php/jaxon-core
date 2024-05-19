<?php

namespace Jaxon\Js;

/**
 * CallFactory.php
 *
 * Create Jaxon client side requests, which will generate the client script necessary
 * to invoke a jaxon request from the browser to registered objects.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

use Jaxon\App\Dialog\DialogManager;

use function array_shift;
use function func_get_args;

class CallFactory
{
    /**
     * @var string
     */
    protected $sPrefix;

    /**
     * @var DialogManager
     */
    protected $xDialogManager;

    /**
     * The class constructor
     *
     * @param string $sPrefix
     * @param DialogManager $xDialogManager
     */
    public function __construct(string $sPrefix, DialogManager $xDialogManager)
    {
        $this->sPrefix = $sPrefix;
        $this->xDialogManager = $xDialogManager;
    }

    /**
     * Generate the javascript code for a call to a given method or function
     *
     * @param string $sFunction
     * @param array $aArguments
     *
     * @return Call
     */
    public function __call(string $sFunction, array $aArguments): Call
    {
        $xCall = new Call($this->sPrefix . $sFunction);
        $xCall->setDialogManager($this->xDialogManager);
        $xCall->addParameters($aArguments);
        return $xCall;
    }

    /**
     * Return the javascript call to a Jaxon function or object method
     *
     * @param string $sFunction    The function or method (without class) name
     *
     * @return Call
     * @deprecated
     */
    public function call(string $sFunction): Call
    {
        $aArguments = func_get_args();
        // Remove the function name from the arguments array.
        array_shift($aArguments);
        return $this->__call($sFunction, $aArguments);
    }
}
