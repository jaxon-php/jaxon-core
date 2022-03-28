<?php

use Jaxon\Jaxon;
use Jaxon\Request\Factory\RequestFactory;
use Jaxon\Request\Factory\ParameterFactory;
use Jaxon\Response\Plugin\JQuery\DomSelector;
use Jaxon\Exception\SetupException;

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
 * Return the singleton instance of the Jaxon/Jaxon class
 *
 * @return Jaxon
 */
function jaxon(): Jaxon
{
    return Jaxon::getInstance();
}

/**
 * Get the ajax request to a PHP class or function.
 *
 * @param string $sClassName
 *
 * @return RequestFactory|null
 * @throws SetupException
 */
function rq(string $sClassName = ''): ?RequestFactory
{
    return Jaxon::getInstance()->factory()->request($sClassName);
}

/**
 * Get the single instance of the parameter factory
 *
 * @return ParameterFactory
 */
function pm(): ParameterFactory
{
    return Jaxon::getInstance()->factory()->parameter();
}

/**
 * Create a JQuery DomSelector with a given path
 *
 * The returned element is not linked to any Jaxon response, so this function shall be used
 * to insert jQuery's code into a javascript function, or as a parameter of a Jaxon function call.
 *
 * @param string $sPath    The jQuery selector path
 * @param string $sContext    A context associated to the selector
 *
 * @return DomSelector
 */
function jq(string $sPath = '', string $sContext = ''): DomSelector
{
    return Jaxon::getInstance()->di()->getJQueryPlugin()->command(false)->selector($sPath, $sContext);
}

// Register the Jaxon request and response plugins
Jaxon::getInstance()->di()->getPluginManager()->registerPlugins();
