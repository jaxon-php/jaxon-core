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

use Jaxon\App\Dialog\Manager\DialogCommand;
use Jaxon\Script\Action\Selector;
use Jaxon\Script\JsExpr;
use Closure;

class JqSelectorCall extends AbstractJsCall
{
    /**
     * The constructor.
     *
     * @param DialogCommand $xDialog
     * @param Closure|null $xExprCb
     * @param string $sSelector    The jQuery selector path
     * @param mixed $xContext    A context associated to the selector
     */
    public function __construct(DialogCommand $xDialog, ?Closure $xExprCb,
        protected string $sSelector, protected $xContext = null)
    {
        parent::__construct($xDialog, $xExprCb);
    }

    /**
     * Get the json expression
     */
    protected function _expr(): JsExpr
    {
        return $this->_initExpr(new JsExpr($this->xDialog,
            new Selector('jq', $this->sSelector, $this->xContext)));
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
        return $this->_expr()->on($sName, $xHandler);
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
        return $this->on('click', $xHandler);
    }
}
