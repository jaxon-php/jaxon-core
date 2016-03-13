<?php

$objPluginManager = \Xajax\Plugin\Manager::getInstance();
$objPluginManager->loadPlugins();

/*
	Section: Global functions
*/

/*
	Function: trans

	Parameters

	$sText - (string):  The text to translate.
	$aPlaceHolders - (array): The placeholders in the text.
	$sLanguage - (string): The language to translate to.

	Returns:

	string : The translated text.
*/
function xajax_trans($sText, array $aPlaceHolders = array(), $sLanguage = null)
{
	return \Xajax\Xajax::xTranslator->trans($sText, $aPlaceHolders, $sLanguage);
}
