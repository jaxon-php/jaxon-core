<?php

namespace Jaxon\Script;

/**
 * JsCall.php
 *
 * Call to a js function.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

use Jaxon\App\Dialog\Manager\DialogCommand;
use Jaxon\Script\Call\Attr;
use Jaxon\Script\Call\Selector;
use Closure;

class JsCall extends AbstractJsCall
{
    /**
     * The class constructor
     *
     * @param DialogCommand $xDialog
     * @param Closure|null $xExprCb
     * @param string $sJsObject
     */
    public function __construct(DialogCommand $xDialog, ?Closure $xExprCb, protected string $sJsObject)
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
        $xJsExpr = match($this->sJsObject) {
            // An empty string returns the js "this" var.
            '' => new JsExpr($this->xDialog, new Selector('js', 'this')),
            // The '.' string returns the js "window" object. No selector needed.
            '.' => new JsExpr($this->xDialog),
            // Otherwise, the corresponding js object will be returned.
            default => new JsExpr($this->xDialog, Attr::get($this->sJsObject, false)),
        };
        return $this->_initExpr($xJsExpr);
    }
}
