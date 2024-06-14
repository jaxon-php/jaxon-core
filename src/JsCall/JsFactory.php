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
use Jaxon\JsCall\Js\Selector;
use Closure;

use function rtrim;

class JsFactory extends AbstractFactory
{
    /**
     * @var string
     */
    protected $sCallPrefix;

    /**
     * @var Selector
     */
    protected $xSelector = null;

    /**
     * The class constructor
     *
     * @param DialogManager $xDialog
     * @param string $sCallPrefix
     * @param Closure|null $xExprCb
     */
    public function __construct(DialogManager $xDialog, string $sCallPrefix = '',
        ?Closure $xExprCb = null)
    {
        parent::__construct($xDialog, $xExprCb);
        $this->sCallPrefix = $sCallPrefix === 'window' ? 'window.' : $sCallPrefix;
    }

    /**
     * Create a js expression
     *
     * @return JsExpr
     */
    protected function _expr(): JsExpr
    {
        $xJsExpr = $this->sCallPrefix !== '' ?
            new JsExpr($this->xDialog) :
            new JsExpr($this->xDialog, new Selector('this', 'js'));
        $this->xExprCb !== null && ($this->xExprCb)($xJsExpr);

        return $xJsExpr;
    }

    /**
     * Get the js class name
     *
     * @return string
     */
    public function _class(): string
    {
        return rtrim($this->sCallPrefix, '.');
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
