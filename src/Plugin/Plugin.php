<?php

namespace Xajax\Plugin;

/*
	File: Plugin.php

	Contains the Plugin class

	Title: Plugin class

	Please see <copyright.php> for a detailed description, copyright
	and license information.
*/

/*
	@package Xajax
	@version $Id: Plugin.php 362 2007-05-29 15:32:24Z calltoconstruct $
	@copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
	@copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
	@license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

/*
	Class: Plugin
	
	The base class for all xajax plugins.
*/
abstract class Plugin
{
	/*
		Function: getClientScript
		
		Called by <Xajax\Plugin\Manager> when the page's HTML is being sent to the browser.
		This allows each plugin to inject some script / style or other appropriate tags
		into the HEAD of the document.  Each block must be appropriately enclosed, meaning
		javascript code must be enclosed in SCRIPT and /SCRIPT tags.
	*/
	abstract public function getClientScript();

	/*
		Function: isRequest

		This returns true if the object is a request plugin. Always return false here.

		Parameters:
	*/
	public function isRequest()
	{
		return false;
	}

	/*
		Function: isResponse
		
		This returns true if the object is a response plugin. Always return false here.
		
		Parameters:
	*/
	public function isResponse()
	{
		return false;
	}
 
	/*
		Function: getName
		
		Called by the <Xajax\Plugin\Manager> when the user script requests a plugin.
		This name must match the plugin name requested in the called to 
		<Xajax\Response\Response->plugin>.
	*/
	abstract public function getName();
}
