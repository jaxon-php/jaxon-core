<?php

namespace Xajax\Plugin;

/*
	File: Request.php

	Contains the Request class

	Title: Request class

	Please see <copyright.php> for a detailed description, copyright
	and license information.
*/

/*
	@package Xajax
	@version $Id: Request.php 362 2007-05-29 15:32:24Z calltoconstruct $
	@copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
	@copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
	@license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

/*
	Class: Request
	
	The base class for all Xajax request plugins.
	
	Request plugins handle the registration, client script generation and processing of
	xajax enabled requests.  Each plugin should have a unique signature for both
	the registration and processing of requests.  During registration, the user will
	specify a type which will allow the plugin to detect and handle it.  During client
	script generation, the plugin will generate a <xajax.request> stub with the
	prescribed call options and request signature.  During request processing, the
	plugin will detect the signature generated previously and process the request
	accordingly.
*/

abstract class Request extends Plugin
{
	/*
		Function: register
		
		Called by the <Xajax\Plugin\Manager> when a user script when a function, event 
		or callable object is to be registered.  Additional plugins may support other 
		registration types.
	*/
	abstract public function register($aArgs);

	abstract public function generateHash();

	/*
	 Function: isRequest
	
	 This returns true if the object is a request plugin. Always return true here.
	
	 Parameters:
	 */
	public function isRequest()
	{
		return true;
	}

	/*
		Function: canProcessRequest
		
		Called by the <Xajax\Plugin\Manager> when a request has been received to determine
		if the request is for a xajax enabled function or for the initial page load.
	*/
	abstract public function canProcessRequest();
	
	/*
		Function: processRequest
		
		Called by the <Xajax\Plugin\Manager> when a request is being processed.  This 
		will only occur when <Xajax> has determined that the current request is a valid
		(registered) xajax enabled function via <xajax->canProcessRequest>.
	*/
	abstract public function processRequest();
}
