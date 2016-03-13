<?php

namespace Xajax\Translation;

/*
	File: TranslatorTrait.php

	Contains the Translator trait.

	Title: Translator trait

	Please see <copyright.php> for a detailed description, copyright
	and license information.
*/

/*
	@package Xajax
	@version $Id: TranslatorTrait.php 362 2007-05-29 15:32:24Z calltoconstruct $
	@copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
	@copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
	@license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

trait TranslatorTrait
{
	/*
		Function: setTranslator

		Parameters

		xTranslator - (Translator):  The translator instance.

	*/
    public function setTranslator($xTranslator)
    {
        $this->xTranslator = $xTranslator;
    }
	
	/*
		Function: trans

		Parameters

		$sText - (string):  The text to translate.
		$aPlaceHolders - (array): The placeholders in the text.
		$sLanguage - (string): The language to translate to.

		Returns:

		string : The translated text.
	*/
	public function trans($sText, array $aPlaceHolders = array(), $sLanguage = null)
	{
		return $this->xTranslator->trans($sText, $aPlaceHolders, $sLanguage);
	}
}
