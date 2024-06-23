<?php

namespace Jaxon\App\View;

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

use Jaxon\App\Component;
use Jaxon\Di\ClassContainer;
use Jaxon\Script\JsExpr;
use Jaxon\Script\JxnCall;

use function is_a;
use function json_encode;
use function htmlentities;

class AttrFormatter
{
    /**
     * The constructor
     *
     * @param ClassContainer $cls
     */
    public function __construct(protected ClassContainer $cls)
    {}

    /**
     * Format a js class name
     *
     * @param JxnCall $xJsCall
     *
     * @return string
     */
    public function show(JxnCall $xJsCall): string
    {
        return $xJsCall->_class();
    }

    /**
     * Get the component HTML code
     *
     * @param JxnCall $xJsCall
     *
     * @return string
     */
    public function html(JxnCall $xJsCall): string
    {
        $sClassName = $xJsCall->_class();
        if(!$sClassName)
        {
            return '';
        }

        $xCallable = $this->cls->makeRegisteredObject($sClassName);
        return is_a($xCallable, Component::class) ? $xCallable->html() : '';
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
