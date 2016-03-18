<?php

namespace Xajax\Exception;

use Exception;

/*
	File: Error.php

	Contains the Error class.

	Title: Error class

	Please see <copyright.php> for a detailed description, copyright
	and license information.
*/

/*
	@package Xajax
	@version $Id: Error.php 362 2007-05-29 15:32:24Z calltoconstruct $
	@copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
	@copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
	@license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

class Error extends Exception
{
    public function __construct($sMessageKey, array $aPlaceHolders = array())
    {
        $sMessage = xajax_trans($sMessageKey, $aPlaceHolders);
        parent::__construct($sMessage);
    }
}
