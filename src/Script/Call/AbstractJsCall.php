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

use Jaxon\Script\Action\Attr;
use Jaxon\Script\Action\Selector;
use Jaxon\Script\JsExpr;

abstract class AbstractJsCall extends AbstractCall
{
    /**
     * Get the call to add to the expression
     *
     * @return Attr|Selector
     */
    abstract protected function _exprCall(): Attr|Selector;

    /**
     * Get the json expression
     *
     * @return JsExpr
     */
    protected function _expr(): JsExpr
    {
        return new JsExpr($this->_exprCall());
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
        return $this->_cbExpr()->__get( $sAttribute);
    }

    /**
     * Set the value of an attribute of the current object
     *
     * @param string $sAttribute
     * @param mixed $xValue
     *
     * @return JsExpr
     */
    public function __set(string $sAttribute, $xValue)
    {
        return $this->_cbExpr()->__set($sAttribute, $xValue);
    }
}
