<?php

namespace Xajax\Request\Support;

use Xajax\Request\Request;
use Xajax\Request\Manager as RequestManager;
use Xajax\Response\Manager as ResponseManager;
use Xajax\Template\EngineTrait as TemplateTrait;

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
	
	Construct instances of this class to define functions that will be registered
	with the <xajax> request processor.  This class defines the parameters that
	are needed for the definition of a xajax enabled function.  While you can
	still specify functions by name during registration, it is advised that you
	convert to using this class when you wish to register external functions or 
	to specify call options as well.
*/
class UserFunction
{
	use TemplateTrait;

	/*
		String: sAlias
		
		An alias to use for this function.  This is useful when you want
		to call the same xajax enabled function with a different set of
		call options from what was already registered.
	*/
	private $sAlias;
	
	/*
		Object: sUserFunction
		
		A string or array which defines the function to be registered.
	*/
	private $sUserFunction;
	
	/*
		String: sInclude
		
		The path and file name of the include file that contains the function.
	*/
	private $sInclude;
	
	/*
		Array: aConfiguration
		
		An associative array containing call options that will be sent to the
		browser curing client script generation.
	*/
	private $aConfiguration;
	
	/*
		Function: __construct
		
		Constructs and initializes the <UserFunction> object.
		
		$sUserFunction - (mixed): A function specification in one of the following formats:
		
			- a three element array:
				(string) Alternate function name: when a method of a class has the same
					name as another function in the system, you can provide an alias to 
					help avoid collisions.
				(object or class name) Class: the name of the class or an instance of
					the object which contains the function to be called.
				(string) Method:  the name of the method that will be called.
			- a two element array:
				(object or class name) Class: the name of the class or an instance of
					the object which contains the function to be called.
				(string) Method:  the name of the method that will be called.
			- a string:
				the name of the function that is available at global scope (not in a 
				class.
		
		$sInclude - deprecated syntax - use ->configure('include','/path/to/file'); instead
		$sInclude - (string, optional):  The path and file name of the include file
			that contains the class or function to be called.
			
		$aConfiguration - marked as deprecated - might become reactivated as argument #2
		$aConfiguration - (array, optional):  An associative array of call options
			that will be used when sending the request from the client.
			
		Examples:
		
			$myFunction = array('alias', 'myClass', 'myMethod');
			$myFunction = array('alias', &$myObject, 'myMethod');
			$myFunction = array('myClass', 'myMethod');
			$myFunction = array(&$myObject, 'myMethod');
			$myFunction = 'myFunction';
			
			$myUserFunction = new UserFunction($myFunction, 'myFile.php', array(
				'method' => 'get',
				'mode' => 'synchronous'
				));
				
			$xajax->register(XAJAX_FUNCTION, $myUserFunction);				
	*/
	public function __construct($sUserFunction)
	{
//SkipDebug
		if(is_array($this->sUserFunction) && count($this->sUserFunction) != 2)
        {
			throw new \Xajax\Exception\Error('errors.functions.invalid-declaration');
        }
//EndSkipDebug
		$this->sAlias = '';
		$this->sUserFunction = $sUserFunction;
		$this->aConfiguration = array();

		if(is_array($this->sUserFunction) && count($this->sUserFunction) > 0)
		{
			$this->sAlias = $this->sUserFunction[0];
			$this->sUserFunction = array_slice($this->sUserFunction, 1);
		}

		// Set the template manager
		$this->setTemplate(RequestManager::getInstance()->getTemplate());
	}
	
	/*
		Function: getName
		
		Get the name of the function being referenced.
		
		Returns:
		
		string - the name of the function contained within this object.
	*/
	public function getName()
	{
		// Do not use sAlias here!
		if(is_array($this->sUserFunction))
        {
			return $this->sUserFunction[1];
        }
		return $this->sUserFunction;
	}
	
	/*
		Function: configure
		
		Call this to set call options for this instance.
	*/
	public function configure($sName, $sValue)
	{
        switch($sName)
        {
		case 'alias':
			$this->sAlias = $sValue;
            break;
		case 'include':
			$this->sInclude = $sValue;
            break;
		default:
			$this->aConfiguration[$sName] = $sValue;
            break;
        }
	}
	
	/*
		Function: generateRequest
		
		Constructs and returns a <xajaxRequest> object which is capable
		of generating the javascript call to invoke this xajax enabled
		function.
	*/
	public function generateRequest($sXajaxPrefix)
	{
		$sAlias = (($this->sAlias) ? $this->sAlias : $this->getName());
		return new Request("{$sXajaxPrefix}{$sAlias}");
	}
	
	/*
		Function: getClientScript
		
		Called by the <xajaxPlugin> that is referencing this function
		reference during the client script generation phase.  This function
		will generate the javascript function stub that is sent to the
		browser on initial page load.
	*/
	public function getClientScript($sXajaxPrefix)
	{
		$sFunction = $this->getName();
		$sAlias = (($this->sAlias) ? $this->sAlias : $sFunction);

		return $this->render('support/function.js.tpl', array(
			'sPrefix' => $sXajaxPrefix,
			'sAlias' => $sAlias,
			'sFunction' => $sFunction,
			'aConfig' => $this->aConfiguration,
		));
	}

	/*
		Function: call
		
		Called by the <Xajax\Plugin> that references this function during the
		request processing phase.  This function will call the specified
		function, including an external file if needed and passing along 
		the specified arguments.
	*/
	public function call($aArgs = array())
	{
		if(($this->sInclude))
		{
			ob_start();
			require_once $this->sInclude;
			$sOutput = ob_get_clean();
			
//SkipDebug
			if(($sOutput))
			{
				$sOutput = xajax_trans('debug.function.include', array(
					'file' => $this->sInclude,
					'output' => $sOutput
				));
				ResponseManager::getInstance()->debug($sOutput);
			}
//EndSkipDebug
		}
		
		$mFunction = $this->sUserFunction;
		ResponseManager::getInstance()->append(call_user_func_array($mFunction, $aArgs));
	}
}
