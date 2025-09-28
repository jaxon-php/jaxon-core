<?php

namespace Jaxon\Script\Call;

/**
 * JxnCall.php
 *
 * Call to a registered function or class.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

use Jaxon\Script\Action\Attr;
use Jaxon\Script\JsExpr;

class JxnCall extends AbstractCall
{
    /**
     * The constructor.
     *
     * @param string $sPrefix The call prefix
     */
    public function __construct(protected string $sPrefix)
    {}

    /**
     * Create a js expression
     *
     * @return JsExpr
     */
    protected function _expr(): JsExpr
    {
        return new JsExpr();
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
        return parent::__call("{$this->sPrefix}{$sMethod}", $aArguments);
    }

    /**
     * @return string
     */
    public function _class(): string
    {
        return '';
    }

    /**
     * Convert this call to array, when converting the response into json.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return (new JsExpr(Attr::get($this->_class())))->jsonSerialize();
    }

    /**
     * Returns a call to jaxon as a string
     *
     * @return string
     */
    public function __toString(): string
    {
        return (new JsExpr(Attr::get($this->_class())))->__toString();
    }
}
