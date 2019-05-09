<?php

use Jaxon\Jaxon;

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
 * @return Jaxon\Jaxon
 */
function jaxon()
{
    return Jaxon::getInstance();
}

/**
 * Translate a text to the selected language
 *
 * @param string        $sText                  The text to translate
 * @param array         $aPlaceHolders          The placeholders in the text
 * @param string        $sLanguage              The language to translate to
 *
 * @return string
 */
function jaxon_trans($sText, array $aPlaceHolders = [], $sLanguage = null)
{
    return Jaxon::getInstance()->di()->getTranslator()->trans($sText, $aPlaceHolders, $sLanguage);
}

/**
 * Register a plugin
 *
 * @param Plugin         $xPlugin               An instance of a plugin
 * @param integer        $nPriority             The plugin priority, used to order the plugins
 *
 * @return void
 */
function jaxon_register_plugin(\Jaxon\Plugin\Plugin $xPlugin, $nPriority = 1000)
{
    Jaxon::getInstance()->registerPlugin($xPlugin, $nPriority);
}

/**
 * Get the single instance of the request factory, and set the class to call.
 *
 * @return Jaxon\Request\Factory
 */
function rq($sClassName = null)
{
    return Jaxon::getInstance()->di()->getRequestFactory()->setClassName($sClassName);
}

/**
 * Get the single instance of the request factory
 *
 * Since no class is set, this function shall be used when setting request parameters.
 *
 * @return Jaxon\Request\Factory
 */
function pr()
{
    return Jaxon::getInstance()->di()->getRequestFactory();
}

/**
 * Create a JQuery Element with a given selector
 *
 * The returned element is not linked to any Jaxon response, so this function shall be used
 * to insert jQuery code into a javascript function, or as a parameter of a Jaxon function call.
 *
 * @param string        $sSelector            The jQuery selector
 * @param string        $sContext             A context associated to the selector
 *
 * @return Jaxon\Response\Plugin\JQuery\Dom\Element
 */
function jq($sSelector = '', $sContext = '')
{
    return new \Jaxon\Response\Plugin\JQuery\Dom\Element($sSelector, $sContext);
}

/**
 * Create a JQuery Element with a given selector
 *
 * The returned element is not linked to any Jaxon response, so this function shall be used
 * to insert jQuery code into a javascript function, or as a parameter of a Jaxon function call.
 *
 * @param string        $sSelector            The jQuery selector
 * @param string        $sContext             A context associated to the selector
 *
 * @return Jaxon\Response\Plugin\JQuery\Dom\Element
 */
function jQuery($sSelector = '', $sContext = '')
{
    return jq($sSelector, $sContext);
}

/*
 * Register the Jaxon request and response plugins
 */
jaxon()->di()->getPluginManager()->registerRequestPlugins();
jaxon()->di()->getPluginManager()->registerResponsePlugins();

/*
 * Setup the view manager
 */
jaxon()->di()->getViewManager()->setup();
