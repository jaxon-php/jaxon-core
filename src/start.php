<?php

namespace Jaxon;

use Jaxon\App\Ajax\Lib;
use Jaxon\Exception\SetupException;
use Jaxon\Script\AttrFormatter;
use Jaxon\Script\JqCall;
use Jaxon\Script\JsCall;
use Jaxon\Script\ParameterFactory;

/**
 * start.php
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
 * @return JsCall
 * @throws SetupException
 */
function rq(string $sClassName = ''): JsCall
{
    return jaxon()->factory()->rq($sClassName);
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
    return jaxon()->factory()->js($sJsObject);
}

/**
 * Get the single instance of the parameter factory
 *
 * @return ParameterFactory
 */
function pm(): ParameterFactory
{
    return jaxon()->factory()->pm();
}

/**
 * Create a JQuery JqCall with a given path
 *
 * The returned element is not linked to any Jaxon response, so this function shall be used
 * to insert jQuery's code into a javascript function, or as a parameter of a Jaxon function call.
 *
 * @param string $sPath    The jQuery selector path
 * @param mixed $xContext    A context associated to the selector
 *
 * @return JqCall
 */
function jq(string $sPath = '', $xContext = null): JqCall
{
    return jaxon()->factory()->jq($sPath, $xContext);
}

/**
 * Get the custom attributes formatter
 *
 * @return AttrFormatter
 */
function attr(): AttrFormatter
{
    return jaxon()->di()->getCustomAttrFormatter();
}

// Register the Jaxon request and response plugins
jaxon()->di()->getPluginManager()->registerPlugins();
