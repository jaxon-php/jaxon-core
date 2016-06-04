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
 * @link https://github.com/lagdo/jaxon-core
 */

/**
 * Translate a text to the selected language
 *
 * @param string        $sText                The text to translate
 * @param array            $aPlaceHolders        The placeholders in the text
 * @param string        $sLanguage            The language to translate to
 *
 * @return string
 */
function jaxon_trans($sText, array $aPlaceHolders = array(), $sLanguage = null)
{
    return \Jaxon\Utils\Container::getInstance()->getTranslator()->trans($sText, $aPlaceHolders, $sLanguage);
}

/*
 * Load the Jaxon request plugins
 */
\Jaxon\Plugin\Manager::getInstance()->loadPlugins();
