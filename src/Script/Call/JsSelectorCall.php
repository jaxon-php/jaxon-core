<?php

/**
 * JsSelectorCall.php
 *
 * Factory for a Javascript element selector.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Script\Call;

use Jaxon\App\Dialog\Manager\DialogCommand;
use Jaxon\Script\Action\HtmlReader;
use Jaxon\Script\Action\Selector;
use Jaxon\Script\JsExpr;
use Closure;

class JsSelectorCall extends AbstractJsCall
{
    /**
     * The constructor.
     *
     * @param DialogCommand $xDialog
     * @param Closure|null $xExprCb
     * @param string $sElementId    The DOM element id
     */
    public function __construct(DialogCommand $xDialog, ?Closure $xExprCb,
        protected string $sElementId)
    {
        parent::__construct($xDialog, $xExprCb);
    }

    /**
     * Get the json expression
     *
     * @return JsExpr
     */
    protected function _expr(): JsExpr
    {
        // If the value is '', return the js "this" object, otherwise, the selected DOM element.
        $xJsExpr = new JsExpr($this->xDialog,
            new Selector('js', $this->sElementId ?: 'this'));
        return $this->_initExpr($xJsExpr);
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
        return $this->_expr()->addEventListener('click', $xHandler);
    }

    /**
     * @return HtmlReader
     */
    public function rd(): HtmlReader
    {
        return new HtmlReader($this->sElementId);
    }
}
