<?php

/**
 * AbstractCall.php
 *
 * Base class for js calls and selectors.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Script\Call;

use Jaxon\Script\JsExpr;
use JsonSerializable;

abstract class AbstractCall implements JsonSerializable
{
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
     * Convert this call to array, when converting the response into json.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->_expr()->jsonSerialize();
    }

    /**
     * Returns a call to jaxon as a string
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->_expr()->__toString();
    }
}
