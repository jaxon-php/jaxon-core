<?php

namespace Xajax\Template;

/*
	File: EngineTrait.php

	Contains the Engine trait.

	Title: Engine trait

	Please see <copyright.php> for a detailed description, copyright
	and license information.
*/

/*
	@package Xajax
	@version $Id: EngineTrait.php 362 2007-05-29 15:32:24Z calltoconstruct $
	@copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
	@copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
	@license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

trait EngineTrait
{
	/*
		Function: setTemplate

		Parameters

		xTemplate - (Xajax\Template\Engine):  The template engine.

	*/
    public function setTemplate($xTemplate)
    {
        $this->xTemplate = $xTemplate;
    }

	/*
		Function: render

		Parameters

		$sTemplate - (string):  The template file.
		$aVars - (array): The data to write in the template.

		Returns:

		string : The rendered template.
	*/
	public function render($sTemplate, array $aVars = array())
	{
		return $this->xTemplate->render($sTemplate, $aVars);
	}
}
