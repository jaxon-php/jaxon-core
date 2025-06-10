<?php

use Jaxon\App\Ajax\Lib as Jaxon;
use Jaxon\App\View\Helper\HtmlAttrHelper;
use Jaxon\Exception\SetupException;
use Jaxon\Script\Factory\ParameterFactory;
use Jaxon\Script\JqSelectorCall;
use Jaxon\Script\JsObjectCall;
use Jaxon\Script\JsSelectorCall;
use Jaxon\Script\JxnCall;

/**
 * globals.php
 *
 * This file moves the Jaxon global functions to the global namespace,
 * so they can be called without the namespace or the use instruction.
 * It must be disabled in case of function naming conflict.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2025 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

/**
 * Return the single instance of the Lib class
 *
 * @return Jaxon
 */
function jaxon(): Jaxon
{
    return \Jaxon\jaxon();
}

/**
 * Get an instance of a registered PHP class.
 *
 * @template T
 * @param class-string<T> $sClassName the class name
 *
 * @return T|null
 * @throws SetupException
 */
function cl(string $sClassName): mixed
{
    return \Jaxon\cl($sClassName);
}

/**
 * Get a factory for a registered class.
 *
 * @param string $sClassName
 *
 * @return JxnCall
 */
function rq(string $sClassName = ''): JxnCall
{
    return \Jaxon\rq($sClassName);
}

/**
 * Get a factory for a Javascript object.
 *
 * @param string $sJsObject
 *
 * @return JsObjectCall
 */
function jo(string $sJsObject = ''): JsObjectCall
{
    return \Jaxon\jo($sJsObject);
}

/**
 * Get a factory for a JQuery selector.
 *
 * @param string $sPath    The jQuery selector path
 * @param mixed $xContext    A context associated to the selector
 *
 * @return JqSelectorCall
 */
function jq(string $sPath = '', $xContext = null): JqSelectorCall
{
    return \Jaxon\jq($sPath, $xContext);
}

/**
 * Get a factory for a Javascript element selector.
 *
 * @param string $sElementId
 *
 * @return JsSelectorCall
 */
function je(string $sElementId = ''): JsSelectorCall
{
    return \Jaxon\je($sElementId);
}

/**
 * Get the single instance of the parameter factory
 *
 * @return ParameterFactory
 */
function pm(): ParameterFactory
{
    return \Jaxon\pm();
}

/**
 * Get the HTML attributes helper
 *
 * @return HtmlAttrHelper
 */
function attr(): HtmlAttrHelper
{
    return \Jaxon\attr();
}
