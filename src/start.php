<?php

/*
	Section: Global functions
*/

/*
	Function: xajax_trans

	Parameters

	$sText - (string):  The text to translate.
	$aPlaceHolders - (array): The placeholders in the text.
	$sLanguage - (string): The language to translate to.

	Returns:

	string : The translated text.
*/
function xajax_trans($sText, array $aPlaceHolders = array(), $sLanguage = null)
{
	return \Xajax\Utils\Translator::getInstance()->trans($sText, $aPlaceHolders, $sLanguage);
}

/*
 * Load request plugins
 */
\Xajax\Plugin\Manager::getInstance()->loadPlugins();
