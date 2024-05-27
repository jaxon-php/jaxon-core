<?php

namespace Jaxon\JsCall;

/**
 * JsFactory.php
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
use Jaxon\JsCall\JsExpr;

class JsFactory extends AbstractFactory
{
    /**
     * @var string
     */
    protected $sCallPrefix;

    /**
     * The class constructor
     *
     * @param DialogManager $xDialog
     * @param string $sCallPrefix
     */
    public function __construct(DialogManager $xDialog, string $sCallPrefix = '')
    {
        $this->xDialog = $xDialog;
        $this->sCallPrefix = $sCallPrefix;
    }

    /**
     * Create a js expression
     */
    protected function _expr(): JsExpr
    {
        return new JsExpr($this->xDialog);
    }

    /**
     * Add a call to a js function on the current object
     *
     * @param string  $sMethod
     * @param array  $aArguments
     *
     * @return JsExpr
     */
    public function __call(string $sMethod, array $aArguments): JsExpr
    {
        // Append the prefix to the method name
        return parent::__call($this->sCallPrefix . $sMethod, $aArguments);
    }
}
