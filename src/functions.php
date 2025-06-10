<?php

namespace Jaxon;

use Jaxon\App\Ajax\Lib as Jaxon;
use Jaxon\App\View\Helper\HtmlAttrHelper;
use Jaxon\Exception\SetupException;
use Jaxon\Script\Call\JqSelectorCall;
use Jaxon\Script\Call\JsObjectCall;
use Jaxon\Script\Call\JsSelectorCall;
use Jaxon\Script\Call\JxnCall;
use Jaxon\Script\ParameterFactory;

/**
 * functions.php
 *
 * This file is automatically loaded by the Composer autoloader
 * The Jaxon global functions are defined here.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
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
    return Jaxon::getInstance();
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
    return jaxon()->cl($sClassName);
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
    return jaxon()->di()->getCallFactory()->rq($sClassName);
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
    return jaxon()->di()->getCallFactory()->jo($sJsObject);
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
    return jaxon()->di()->getCallFactory()->jq($sPath, $xContext);
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
    return jaxon()->di()->getCallFactory()->je($sElementId);
}

/**
 * Get the single instance of the parameter factory
 *
 * @return ParameterFactory
 */
function pm(): ParameterFactory
{
    return jaxon()->di()->getParameterFactory();
}

/**
 * Get the HTML attributes helper
 *
 * @return HtmlAttrHelper
 */
function attr(): HtmlAttrHelper
{
    return jaxon()->di()->getHtmlAttrHelper();
}
