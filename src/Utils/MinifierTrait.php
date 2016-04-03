<?php

namespace Xajax\Utils;

/*
	File: MinifierTrait.php

	Contains the Minifier trait.

	Title: Minifier trait

	Please see <copyright.php> for a detailed description, copyright
	and license information.
*/

/*
	@package Xajax
	@version $Id: MinifierTrait.php 362 2007-05-29 15:32:24Z calltoconstruct $
	@copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
	@copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
	@license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

trait MinifierTrait
{
    /*
		Function: minify

		Parameters

		$sCode - (string):  The javascript code to minify.

		Returns:

		string : The minified code.
	*/
	public function minify($sCode)
	{
		return Minifier::getInstance()->minify($sCode);
	}
}
