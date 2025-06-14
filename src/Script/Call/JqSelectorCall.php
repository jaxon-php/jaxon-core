<?php

/**
 * JqSelectorCall.php
 *
 * Factory for a JQuery selector.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Script\Call;

use Jaxon\Script\Action\Selector;
use Jaxon\Script\JsExpr;
use Closure;

class JqSelectorCall extends AbstractJsCall
{
    /**
     * The constructor.
     *
     * @param Closure|null $xExprCb
     * @param string $sSelector    The jQuery selector path
     * @param mixed $xContext    A context associated to the selector
     */
    public function __construct(?Closure $xExprCb, protected string $sSelector,
        protected $xContext = null)
    {
        parent::__construct($xExprCb);
    }

    /**
     * Get the call to add to the expression
     *
     * @return Selector
     */
    protected function _exprCall(): Selector
    {
        // If the value is '', return the js "this" object, otherwise, the selected DOM element.
        return new Selector('jq', $this->sSelector ?: 'this', $this->xContext);
    }

    /**
     * Set an event handler on the first selected element
     *
     * @param string $sName
     * @param JsExpr $xHandler
     *
     * @return JsExpr
     */
    public function on(string $sName, JsExpr $xHandler): JsExpr
    {
        return $this->_expr()->event('jq', $sName, $xHandler);
    }

    /**
     * Set an "click" event handler on the first selected element
     *
     * @param JsExpr $xHandler
     *
     * @return JsExpr
     */
    public function click(JsExpr $xHandler): JsExpr
    {
        return $this->_expr()->event('jq', 'click', $xHandler);
    }
}
