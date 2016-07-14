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
 * @license https://opensource.org/licenses/BSD-2-Clause BSD 2-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

/**
 * Return the only instance of the Jaxon/Jaxon class
 *
 * @param string        $sRequestURI            The URI to send the requests to
 * @param string        $sLanguage              The language of the library
 *
 * @return Jaxon\Jaxon
 */
function jaxon($sRequestURI = null, $sLanguage = null)
{
    $jaxon = \Jaxon\Jaxon::getInstance();
    // Set the request URI
    if(($sRequestURI))
    {
        $jaxon->setOption('core.request.uri', $sRequestURI);
    }
    // Set the language
    if(($sLanguage))
    {
        $jaxon->setOption('core.language', $sLanguage);
    }
    return $jaxon;
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
function registerJaxonPlugin(\Jaxon\Plugin\Plugin $xPlugin, $nPriority = 1000)
{
    \Jaxon\Jaxon::getInstance()->registerPlugin($xPlugin, $nPriority);
}

/*
 * Load the Jaxon request plugins
 */
\Jaxon\Jaxon::getInstance()->registerRequestPlugins();
