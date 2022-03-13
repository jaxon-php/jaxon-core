<?php

namespace Jaxon\Request\Factory;

/**
 * RequestFactory.php
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

use Jaxon\Request\Call\Call;
use Jaxon\Ui\Dialogs\Dialog;
use Jaxon\Ui\Pagination\Paginator;

use function array_shift;
use function func_get_args;

class RequestFactory
{
    /**
     * The prefix to prepend on each call
     *
     * @var string
     */
    protected $sPrefix;

    /**
     * @var Dialog
     */
    protected $xDialog;

    /**
     * @var Paginator
     */
    protected $xPaginator;

    /**
     * The class constructor
     *
     * @param string $sPrefix
     * @param Dialog $xDialog
     * @param Paginator $xPaginator
     */
    public function __construct(string $sPrefix, Dialog $xDialog, Paginator $xPaginator)
    {
        $this->sPrefix = $sPrefix;
        $this->xDialog = $xDialog;
        $this->xPaginator = $xPaginator;
    }

    /**
     * Generate the javascript code for a call to a given method
     *
     * @param string $sFunction
     * @param array $aArguments
     *
     * @return Call
     */
    public function __call(string $sFunction, array $aArguments): Call
    {
        // Make the request
        $xCall = new Call($this->sPrefix . $sFunction, $this->xDialog, $this->xPaginator);
        $xCall->useSingleQuote();
        $xCall->addParameters($aArguments);
        return $xCall;
    }

    /**
     * Return the javascript call to a Jaxon function or object method
     *
     * @param string $sFunction    The function or method (without class) name
     *
     * @return Call
     */
    public function call(string $sFunction): Call
    {
        $aArguments = func_get_args();
        // Remove the function name from the arguments array.
        array_shift($aArguments);
        // Make the request
        return $this->__call($sFunction, $aArguments);
    }
}
