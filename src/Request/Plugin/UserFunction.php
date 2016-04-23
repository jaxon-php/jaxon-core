<?php

namespace Xajax\Request\Plugin;

use Xajax\Xajax;
use Xajax\Plugin\Request as RequestPlugin;
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
	Class: UserFunction
*/
class UserFunction extends RequestPlugin
{
	use \Xajax\Utils\ContainerTrait;

	/*
		Array: aFunctions
		
		An array of <xajaxUserFunction> object that are registered and
		available via a <xajax.request> call.
	*/
	protected $aFunctions;

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
		Function: register
		
		Provides a mechanism for functions to be registered and made available to
		the page via the javascript <xajax.request> call.
	*/
	public function register($aArgs)
	{
		if(count($aArgs) > 1)
		{
			$sType = $aArgs[0];

			if($sType == Xajax::USER_FUNCTION)
			{
				$xUserFunction = $aArgs[1];

				if(!($xUserFunction instanceof \Xajax\Request\Support\UserFunction))
					$xUserFunction = new \Xajax\Request\Support\UserFunction($xUserFunction);

				if(count($aArgs) > 2)
				{
					if(is_array($aArgs[2]))
					{
						foreach($aArgs[2] as $sName => $sValue)
						{
							$xUserFunction->configure($sName, $sValue);
						}
					}
					else
					{
						$xUserFunction->configure('include', $aArgs[2]);
					}
				}
				$this->aFunctions[$xUserFunction->getName()] = $xUserFunction;

				return $xUserFunction->generateRequest();
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
			$code .= $xFunction->getClientScript();
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
		// Check the validity of the function name
		if(($this->sRequestedFunction) && !$this->validateFunction($this->sRequestedFunction))
		{
			$this->sRequestedFunction = null;
		}
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
		// Unable to find the requested function
		throw new \Xajax\Exception\Error('errors.functions.invalid', array('name' => $this->sRequestedFunction));
	}
}
