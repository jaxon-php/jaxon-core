<?php

/**
 * JqCall.php
 *
 * Call to a jquery selector.
 *
 * @package jaxon-jquery
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-jquery
 */

namespace Jaxon\Script;

use Jaxon\App\Dialog\DialogManager;
use Jaxon\Script\Call\Selector;
use Closure;

class JqCall extends AbstractJsCall
{
    /**
     * The selector path
     *
     * @var string
     */
    protected $sSelector;

    /**
     * The selector context
     *
     * @var mixed
     */
    protected $xContext;

    /**
     * The constructor.
     *
     * @param DialogManager $xDialog
     * @param Closure|null $xExprCb
     * @param string $sSelector    The jQuery selector path
     * @param mixed $xContext    A context associated to the selector
     */
    public function __construct(DialogManager $xDialog, ?Closure $xExprCb,
        string $sSelector, $xContext = null)
    {
        parent::__construct($xDialog, $xExprCb);
        $this->sSelector = $sSelector;
        $this->xContext = $xContext;
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
