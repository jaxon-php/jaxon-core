<?php

use Jaxon\Jaxon;
use Jaxon\Request\Factory\RequestFactory;
use Jaxon\Request\Factory\ParameterFactory;
use Jaxon\Response\Plugin\JQuery\Dom\Element as DomElement;
use Jaxon\Exception\SetupException;

/**
 * start.php -
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
 * Create a JQuery Element with a given selector
 *
 * The returned element is not linked to any Jaxon response, so this function shall be used
 * to insert jQuery's code into a javascript function, or as a parameter of a Jaxon function call.
 *
 * @param string $sSelector    The jQuery selector
 * @param string $sContext    A context associated to the selector
 *
 * @return DomElement
 */
function jq(string $sSelector = '', string $sContext = ''): DomElement
{
    $xConfig = Jaxon::getInstance()->config();
    $jQueryNs = $xConfig->getOption('core.jquery.no_conflict', false) ? 'jQuery' : '$';
    return new DomElement($jQueryNs, $sSelector, $sContext);
}

/**
 * Create a JQuery Element with a given selector
 *
 * The returned element is not linked to any Jaxon response, so this function shall be used
 * to insert jQuery's code into a javascript function, or as a parameter of a Jaxon function call.
 *
 * @param string $sSelector    The jQuery selector
 * @param string $sContext    A context associated to the selector
 *
 * @return DomElement
 */
function jQuery(string $sSelector = '', string $sContext = ''): DomElement
{
    return jq($sSelector, $sContext);
}

// Register the Jaxon request and response plugins
jaxon()->di()->getPluginManager()->registerRequestPlugins();
jaxon()->di()->getPluginManager()->registerResponsePlugins();
