<?php

/**
 * start.php - 
 *
 * This file is automatically loaded by the Composer autoloader
 *
 * The Xajax global functions are defined here, and the library is initialised.
 *
 * @package xajax-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-2-Clause BSD 2-Clause License
 * @link https://github.com/lagdo/xajax-core
 */

/**
 * Translate a text to the selected language
 *
 * @param string		$sText				The text to translate
 * @param array			$aPlaceHolders		The placeholders in the text
 * @param string		$sLanguage			The language to translate to
 *
 * @return string
 */
function xajax_trans($sText, array $aPlaceHolders = array(), $sLanguage = null)
{
	return \Xajax\Utils\Container::getInstance()->getTranslator()->trans($sText, $aPlaceHolders, $sLanguage);
}

/*
 * Load the Xajax request plugins
 */
\Xajax\Plugin\Manager::getInstance()->loadPlugins();
