<?php

namespace Jaxon\Script;

/**
 * JsObjectCall.php
 *
 * Factory for a Javascript object.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

use Jaxon\App\Dialog\Manager\DialogCommand;
use Jaxon\Script\Call\Attr;
use Closure;

class JsObjectCall extends AbstractJsCall
{
    /**
     * The class constructor
     *
     * @param DialogCommand $xDialog
     * @param Closure|null $xExprCb
     * @param string $sJsObject
     */
    public function __construct(DialogCommand $xDialog, ?Closure $xExprCb,
        protected string $sJsObject)
    {
        parent::__construct($xDialog, $xExprCb);
    }

    /**
     * Create a js expression
     *
     * @return JsExpr
     */
    protected function _expr(): JsExpr
    {
        // If the value is '', return the js "window" object, otherwise, the corresponding js object.
        $xJsExpr = new JsExpr($this->xDialog,
            Attr::get($this->sJsObject ?: 'window'));
        return $this->_initExpr($xJsExpr);
    }
}
