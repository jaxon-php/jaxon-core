<?php

/**
 * AbstractFactory.php
 *
 * Base class for js call and selector factory.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\JsCall;

use Jaxon\App\Dialog\DialogManager;
use Closure;

abstract class AbstractFactory
{
    /**
     * @var DialogManager
     */
    protected $xDialog;

    /**
     * A function to call when the expression is created
     *
     * @var Closure
     */
    protected $xExprCb;

    /**
     * The constructor.
     *
     * @param DialogManager $xDialog
     * @param Closure|null $xExprCb
     */
    protected function __construct(DialogManager $xDialog, ?Closure $xExprCb)
    {
        $this->xDialog = $xDialog;
        $this->xExprCb = $xExprCb;
    }

    /**
     * Create a js expression
     *
     * @return JsExpr
     */
    abstract protected function _expr(): JsExpr;

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
        return $this->_expr()->__call($sMethod, $aArguments);
    }

    /**
     * Get the value of an attribute of the current object
     *
     * @param string  $sAttribute
     *
     * @return JsExpr
     */
    public function __get(string $sAttribute): JsExpr
    {
        return $this->_expr()->__get( $sAttribute);
    }

    /**
     * Set the value of an attribute of the current object
     *
     * @param string $sAttribute
     * @param mixed $xValue
     *
     * @return void
     */
    public function __set(string $sAttribute, $xValue)
    {
        return $this->_expr()->__set($sAttribute, $xValue);
    }
}
