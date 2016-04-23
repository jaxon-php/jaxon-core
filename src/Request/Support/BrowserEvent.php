<?php

namespace Xajax\Request\Support;

use Xajax\Request\Request;
use Xajax\Request\Manager as RequestManager;
use Xajax\Response\Manager as ResponseManager;

/*
	File: BrowserEvent.php

	Definition of the xajax Event object.

	Title: BrowserEvent

	Please see <copyright.php> for a detailed description, copyright
	and license information.
*/

/*
	@package Xajax
	@version $Id: BrowserEvent.php 362 2007-05-29 15:32:24Z calltoconstruct $
	@copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
	@copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
	@license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

/*
	Class: BrowserEvent
	
	A container class which holds a reference to handler functions and configuration
	options associated with a registered event.
*/
class BrowserEvent
{
	use \Xajax\Utils\ContainerTrait;

	/*
		String: sName
		
		The name of the event.
	*/
	private $sName;
	
	/*
		Array: aConfiguration
		
		Configuration / call options to be used when initiating a xajax request
		to trigger this event.
	*/
	private $aConfiguration;
	
	/*
		Array: aHandlers
		
		A list of <xajaxUserFunction> objects associated with this registered
		event.  Each of these functions will be called when the event is triggered.
	*/
	private $aHandlers;
	
	/*
		Function: __construct
		
		Construct and initialize this <BrowserEvent> object.
	*/
	public function __construct($sName)
	{
		$this->sName = $sName;
		$this->aConfiguration = array();
		$this->aHandlers = array();
	}
	
	/*
		Function: getName
		
		Returns the name of the event.
		
		Returns:
		
		string - the name of the event.
	*/
	public function getName()
	{
		return $this->sName;
	}
	
	/*
		Function: configure
		
		Sets/stores configuration options that will be used when generating
		the client script that is sent to the browser.
	*/
	public function configure($sName, $mValue)
	{
		$this->aConfiguration[$sName] = $mValue;
	}
	
	/*
		Function: addHandler
		
		Adds a <UserFunction> object to the list of handlers that will
		be fired when the event is triggered.
	*/
	public function addHandler($xuf)
	{
		$this->aHandlers[] = $xuf;
	}
	
	/*
		Function: generateRequest
		
		Generates a <xajaxRequest> object that corresponds to the
		event so that the client script can easily invoke this event.
	*/
	public function generateRequest()
	{
		$sEvent = $this->sName;
		return new Request($sEvent, 'event');
	}



 	/*
 		Function: getClientScript
 		
 		Generates a block of javascript code that declares a stub function
 		that can be used to easily trigger the event from the browser.
 	*/
 	public function getClientScript()
	{
		$sEventPrefix = $this->getOption('core.prefix.event');
		$sMode = '';
		$sMethod = '';
		if(isset($this->aConfiguration['mode']))
		{
			$sMode = $this->aConfiguration['mode'];
		}
		if(isset($this->aConfiguration['method']))
		{
			$sMethod = $this->aConfiguration['method'];
		}

		return $this->render('support/event.js.tpl', array(
			'sPrefix' => $sEventPrefix,
			'sEvent' => $this->sName,
			'sMode' => $sMode,
			'sMethod' => $sMethod,
		));
	}
	
	/*
		Function: fire
		
		Called by the <Plugin\BrowserEvent> when the event has been triggered.
	*/
	public function fire($aArgs)
	{
		foreach($this->aHandlers as $xHandler)
		{
			$xHandler->call($aArgs);
		}
	}
}
