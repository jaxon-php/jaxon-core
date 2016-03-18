<?php

namespace Xajax\Plugin\Request;

use Xajax\Plugin\Request as Request;
use Xajax\Request\Manager as RequestManager;

/*
	File: UserFunction.php

	Contains the UserFunction class

	Title: UserFunction class

	Please see <copyright.php> for a detailed description, copyright
	and license information.
*/

/*
	@package Xajax
	@version $Id: UserFunction.php 362 2007-05-29 15:32:24Z calltoconstruct $
	@copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
	@copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
	@license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

/*
	Constant: XAJAX_FUNCTION
		Specifies that the item being registered via the <xajax->register> function
		is a php function available at global scope, or a specific function from
		an instance of an object.
*/
if(!defined ('XAJAX_FUNCTION')) define ('XAJAX_FUNCTION', 'function');

/*
	Class: UserFunction
*/
class UserFunction extends Request
{
	/*
		Array: aFunctions
		
		An array of <xajaxUserFunction> object that are registered and
		available via a <xajax.request> call.
	*/
	protected $aFunctions;

	/*
		String: sXajaxPrefix
		
		A configuration setting that is stored locally and used during
		the client script generation phase.
	*/
	protected $sXajaxPrefix;
	
	/*
		String: sDefer
		
		Configuration option that can be used to request that the
		javascript file is loaded after the page has been fully loaded.
	*/
	protected $sDefer;
	
	protected $bDeferScriptGeneration;

	/*
		String: sRequestedFunction

		This string is used to temporarily hold the name of the function
		that is being requested (during the request processing phase).

		Since canProcessRequest loads this value from the get or post
		data, it is unnecessary to load it again.
	*/
	protected $sRequestedFunction;

	/*
		Function: __construct
		
		Constructs and initializes the <UserFunction>.  The GET and POST
		data is searched for xajax function call parameters.  This will later
		be used to determine if the request is for a registered function in
		<UserFunction->canProcessRequest>
	*/
	public function __construct()
	{
		$this->aFunctions = array();

		$this->sXajaxPrefix = 'xajax_';
		$this->sDefer = '';
		$this->bDeferScriptGeneration = false;

		$this->sRequestedFunction = null;
		
		if(isset($_GET['xjxfun']))
		{
			$this->sRequestedFunction = $_GET['xjxfun'];
		}
		if(isset($_POST['xjxfun']))
		{
			$this->sRequestedFunction = $_POST['xjxfun'];
		}
	}

	/*
		Function: getName
	*/
	public function getName()
	{
		return 'UserFunction';
	}

	/*
		Function: configure
		
		Sets/stores configuration options used by this plugin.
	*/
	public function configure($sName, $mValue)
	{
		switch($sName)
		{
		case 'wrapperPrefix':
			$this->sXajaxPrefix = $mValue;
			break;
		case 'scriptDefferal':
			$this->sDefer = ($mValue === true ? 'defer' : '');
			break;
		case 'deferScriptGeneration':
			if($mValue === true || $mValue === false)
				$this->bDeferScriptGeneration = $mValue;
			else if($mValue == 'deferred')
				$this->bDeferScriptGeneration = true;
			break;
		default:
		}
	}

	/*
		Function: register
		
		Provides a mechanism for functions to be registered and made available to
		the page via the javascript <xajax.request> call.
	*/
	public function register($aArgs)
	{
		if(1 < count($aArgs))
		{
			$sType = $aArgs[0];

			if(XAJAX_FUNCTION == $sType)
			{
				$xUserFunction = $aArgs[1];

				if(!($xUserFunction instanceof \Xajax\Request\Support\UserFunction))
					$xUserFunction = new \Xajax\Request\Support\UserFunction($xUserFunction);

				if(2 < count($aArgs))
				{
					if(is_array($aArgs[2]))
					{
						foreach($aArgs[2] as $sName => $sValue)
						{
							$xUserFunction->configure($sName, $sValue);
						}
					} else {
						$xUserFunction->configure('include', $aArgs[2]);
					}
				}
				$this->aFunctions[$xUserFunction->getName()] = $xUserFunction;

				return $xUserFunction->generateRequest($this->sXajaxPrefix);
			}
		}

		return false;
	}

	public function generateHash()
	{
		$sHash = '';
		foreach($this->aFunctions as $xFunction)
		{
			$sHash .= $xFunction->getName();
		}
		return md5($sHash);
	}

	/*
		Function: getClientScript
		
		Called by the <xajaxPluginManager> during the client script generation
		phase.  This is used to generate a block of javascript code that will
		contain function declarations that can be used on the browser through
		javascript to initiate xajax requests.
	*/
	public function getClientScript()
	{
		$code = '';
		foreach($this->aFunctions as $xFunction)
		{
			$code .= $xFunction->getClientScript($this->sXajaxPrefix);
		}
		return $code;
	}

	/*
		Function: canProcessRequest
		
		Determines whether or not the current request can be processed
		by this plugin.
		
		Returns:
		
		boolean - True if the current request can be handled by this plugin;
			false otherwise.
	*/
	public function canProcessRequest()
	{
		return ($this->sRequestedFunction != null);
	}

	/*
		Function: processRequest
		
		Called by the <xajaxPluginManager> when a request needs to be
		processed.
		
		Returns:
		
		mixed - True when the request has been processed successfully.
			An error message when an error has occurred.
	*/
	public function processRequest()
	{
		if(!$this->canProcessRequest())
			return false;

		$aArgs = RequestManager::getInstance()->process();

		if(array_key_exists($this->sRequestedFunction, $this->aFunctions))
		{
			$this->aFunctions[$this->sRequestedFunction]->call($aArgs);
			return true;
		}

		return xajax_trans('errors.functions.invalid', array('name' => $this->sRequestedFunction));
	}
}
