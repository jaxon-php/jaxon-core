<?php

namespace Xajax\Plugin;

/*
	File: Response.php

	Contains the Response class

	Title: Response class

	Please see <copyright.php> for a detailed description, copyright
	and license information.
*/

/*
	@package Xajax
	@version $Id: Response.php 362 2007-05-29 15:32:24Z calltoconstruct $
	@copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
	@copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
	@license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

/*
	Class: Response
	
	Base class for all xajax response plugins.
	
	A response plugin provides additional services not already provided by the 
	<Xajax\Response\Response> class with regard to sending response commands to the
	client.  In addition, a response command may send javascript to the browser
	at page load to aid in the processing of it's response commands.
*/

abstract class Response extends Plugin
{
	/*
		Object: objResponse
		
		A reference to the current <Xajax\Response\Response> object that is being used
		to build the response that will be sent to the client browser.
	*/
	protected $objResponse;
	
	/*
		Function: setResponse
		
		Called by the <Xajax\Response\Response> object that is currently being used
		to build the response that will be sent to the client browser.
		
		Parameters:
		
		objResponse - (object):  A reference to the <Xajax\Response\Response> object
	*/
	public function setResponse($objResponse)
	{
		$this->objResponse = $objResponse;
	}
	
	/*
		Function: addCommand
		
		Used internally to add a command to the response command list.  This
		will call <Xajax\Response\Response->addPluginCommand> using the reference provided
		in <Xajax\Response\Response->setResponse>.
	*/
 	public function addCommand($aAttributes, $sData)
 	{
 		$this->objResponse->addPluginCommand($this, $aAttributes, $sData);
 	}

 	/*
 	 Function: isResponse

 	 This returns true if the object is a response plugin. Always return true here.

 	 Parameters:
 	 */
 	public function isResponse()
 	{
 		return true;
 	}
 
	/*
		Function: getName
		
		Called by the <Xajax\Plugin\Manager> when the user script requests a plugin.
		This name must match the plugin name requested in the called to 
		<Xajax\Response\Response->plugin>.
	*/
	abstract public function getName();
	
	/*
		Function: process
		
		Called by <Xajax\Response\Response> when a user script requests the service of a
		response plugin.  The parameters provided by the user will be used to
		determine which response command and parameters will be sent to the
		client upon completion of the xajax request process.
	*/
	// abstract public function process();
}
