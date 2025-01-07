<?php

namespace Jaxon;

use Jaxon\App\Ajax\Lib;
use Jaxon\App\View\AttrHelper;
use Jaxon\Exception\SetupException;
use Jaxon\Script\Factory\ParameterFactory;
use Jaxon\Script\JqCall;
use Jaxon\Script\JsCall;
use Jaxon\Script\JxnCall;

/**
 * functions.php
 *
 * This file is automatically loaded by the Composer autoloader
 *
 * The Jaxon global functions are defined here, and the library is initialised.
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
 * @return Lib
 */
function jaxon(): Lib
{
    return Lib::getInstance();
}

/**
 * Get an instance of a registered PHP class.
 *
 * @param string $sClassName
 *
 * @return mixed
 * @throws SetupException
 */
function cl(string $sClassName)
{
    return jaxon()->cl($sClassName);
}

/**
 * Factory for ajax calls to a registered PHP class or function.
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
 * Get the factory for calls to a js object or function.
 *
 * @param string $sJsObject
 *
 * @return JsCall
 */
function js(string $sJsObject = ''): JsCall
{
    return jaxon()->di()->getCallFactory()->js($sJsObject);
}

/**
 * Shortcut to get the factory for calls to a global js object or function.
 *
 * @return JsCall
 */
function jw(): JsCall
{
    return js('w');
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
 * Create a JQuery selector with a given path
 *
 * @param string $sPath    The jQuery selector path
 * @param mixed $xContext    A context associated to the selector
 *
 * @return JqCall
 */
function jq(string $sPath = '', $xContext = null): JqCall
{
    return jaxon()->di()->getCallFactory()->jq($sPath, $xContext);
}

/**
 * Get the custom attributes helper
 *
 * @return AttrHelper
 */
function attr(): AttrHelper
{
    return jaxon()->di()->getCustomAttrHelper();
}
