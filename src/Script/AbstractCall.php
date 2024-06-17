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

namespace Jaxon\Script;

use Jaxon\App\Dialog\DialogManager;

abstract class AbstractCall
{
    /**
     * @var DialogManager
     */
    protected $xDialog;

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
}
