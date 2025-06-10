<?php

namespace Jaxon\Script\Call;

/**
 * AbstractJsCall
 *
 * Base class for js (not Jaxon) calls and selectors.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

use Jaxon\App\Dialog\Manager\DialogCommand;
use Jaxon\Script\JsExpr;
use Closure;

abstract class AbstractJsCall extends AbstractCall
{
    /**
     * A function to call when the expression is created
     *
     * @var Closure
     */
    protected $xExprCb;

    /**
     * The constructor.
     *
     * @param DialogCommand $xDialog
     * @param Closure|null $xExprCb
     */
    protected function __construct(DialogCommand $xDialog, ?Closure $xExprCb)
    {
        $this->xDialog = $xDialog;
        $this->xExprCb = $xExprCb;
    }

    /**
     * Call the js expression callback
     *
     * @param JsExpr $xJsExpr
     *
     * @return JsExpr
     */
    protected function _initExpr(JsExpr $xJsExpr): JsExpr
    {
        $this->xExprCb !== null && ($this->xExprCb)($xJsExpr);
        return $xJsExpr;
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
