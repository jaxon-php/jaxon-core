<?php

namespace Jaxon\JsCall;

/**
 * AttrFormatter.php
 *
 * Formatter for Jaxon custom HTML attributes.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

use function json_encode;
use function htmlentities;

class AttrFormatter
{
    /**
     * Format a js class name
     *
     * @param JsFactory $xJsFactory
     *
     * @return string
     */
    public function show(JsFactory $xJsFactory): string
    {
        return $xJsFactory->_class();
    }

    /**
     * Format a function call (json expression)
     *
     * @param JsExpr $xJsExpr
     *
     * @return string
     */
    public function func(JsExpr $xJsExpr): string
    {
        return htmlentities(json_encode($xJsExpr->jsonSerialize()));
    }
}
