<?php

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
 * Return the only instance of the Jaxon/Jaxon class
 *
 * @return Jaxon\Jaxon
 */
function jaxon()
{
    return \Jaxon\Utils\Container::getInstance()->getJaxon();
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
function jaxon_trans($sText, array $aPlaceHolders = array(), $sLanguage = null)
{
    return \Jaxon\Utils\Container::getInstance()->getTranslator()->trans($sText, $aPlaceHolders, $sLanguage);
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
    \Jaxon\Utils\Container::getInstance()->getJaxon()->registerPlugin($xPlugin, $nPriority);
}

/**
 * Get an instance of the request factory
 *
 * The returned element is not linked to any Jaxon class, so this function shall
 * only be used as a parameter of a Jaxon function call.
 *
 * @return Jaxon\Request\Factory
 */
function rq($classname = null)
{
    if(($classname))
    {
        return jaxon()->sentry()->request($classname);
    }
    return \Jaxon\Utils\Container::getInstance()->getRequestFactory();
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
 * @return Jaxon\JQuery\Dom\Element
 */
function jq($sSelector = '', $sContext = '')
{
    return new \Jaxon\JQuery\Dom\Element($sSelector, $sContext);
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
 * @return Jaxon\JQuery\Dom\Element
 */
function jQuery($sSelector = '', $sContext = '')
{
    return jq($sSelector, $sContext);
}

/*
 * Load the Jaxon request plugins
 */
jaxon()->registerRequestPlugins();

/*
 * Load the Jaxon response plugins
 */
jaxon()->registerResponsePlugins();
