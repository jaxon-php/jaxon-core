<?php

namespace Xajax\Plugin\Request;

use Xajax\Plugin\Request as Request;
use Xajax\Request\Manager as RequestManager;

/*
	File: CallableObject.php

	Contains the CallableObject class

	Title: CallableObject class

	Please see <copyright.php> for a detailed description, copyright
	and license information.
*/

/*
	@package Xajax
	@version $Id: CallableObject.php 362 2007-05-29 15:32:24Z calltoconstruct $
	@copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
	@copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
	@license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

/*
	Constant: XAJAX_CALLABLE_OBJECT
		Specifies that the item being registered via the <xajax->register> function is a
		object who's methods will be callable from the browser.
*/
if(!defined ('XAJAX_CALLABLE_OBJECT')) define ('XAJAX_CALLABLE_OBJECT', 'callable object');

/*
	Class: CallableObject
*/
class CallableObject extends Request
{
	/*
		Array: aCallableObjects
	*/
	protected $aCallableObjects;

	/*
		Array: aClassPaths
	*/
	protected $aClassPaths;

	/*
		String: sXajaxPrefix
	*/
	protected $sXajaxPrefix;
	
	/*
		String: sDefer
	*/
	protected $sDefer;
	
	protected $bDeferScriptGeneration;

	/*
		String: sRequestedClass
	*/
	protected $sRequestedClass;
	
	/*
		String: sRequestedMethod
	*/
	protected $sRequestedMethod;

	/*
		Function: __construct
	*/
	public function __construct()
	{
		$this->aCallableObjects = array();
		$this->aClassPaths = array();

		$this->sXajaxPrefix = 'xajax_';
		$this->sDefer = '';
		$this->bDeferScriptGeneration = false;

		$this->sRequestedClass = NULL;
		$this->sRequestedMethod = NULL;

		if(!empty($_GET['xjxcls']))
		{
			$this->sRequestedClass = $_GET['xjxcls'];
		}
		if(!empty($_GET['xjxmthd']))
		{
			$this->sRequestedMethod = $_GET['xjxmthd'];
		}
		if(!empty($_POST['xjxcls']))
		{
			$this->sRequestedClass = $_POST['xjxcls'];
		}
		if(!empty($_POST['xjxmthd']))
		{
			$this->sRequestedMethod = $_POST['xjxmthd'];
		}
	}

	/*
		Function: getName
	*/
	public function getName()
	{
		return 'CallableObject';
	}

	/*
		Function: setRequestedClass
	*/
	public function setRequestedClass($sRequestedClass)
	{
		$this->sRequestedClass = $sRequestedClass;
	}

	/*
		Function: configure
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
	*/
	public function register($aArgs)
	{
		if(1 < count($aArgs))
		{
			$sType = $aArgs[0];

			if(XAJAX_CALLABLE_OBJECT == $sType)
			{
				$xCallableObject = $aArgs[1];

				if(!is_object($xCallableObject))
				{
					throw new \Xajax\Exception\Error('errors.objects.instance');
				}

				if(!($xCallableObject instanceof \Xajax\Request\Support\CallableObject))
					$xCallableObject = new \Xajax\Request\Support\CallableObject($xCallableObject);

				if(2 < count($aArgs) && is_array($aArgs[2]))
				{
					foreach($aArgs[2] as $sKey => $aValue)
					{
						foreach($aValue as $sName => $sValue)
						{
							if($sName == 'classpath' && $sValue != '')
								$this->aClassPaths[] = $sValue;
							$xCallableObject->configure($sKey, $sName, $sValue);
						}
					}
				}
				$this->aCallableObjects[$xCallableObject->getName()] = $xCallableObject;

				return $xCallableObject->generateRequests($this->sXajaxPrefix);
			}
		}

		return false;
	}


	public function generateHash()
	{
		$sHash = '';
		foreach($this->aCallableObjects as $xCallableObject)
		{
			$sHash .= $xCallableObject->getName();
			$sHash .= implode('|', $xCallableObject->getMethods());
		}
		return md5($sHash);
	}

	/*
		Function: getClientScript
	*/
	public function getClientScript()
	{
		// Generate code for javascript classes declaration
		$code = '';
		$classes = array();
		foreach($this->aClassPaths as $sClassPath)
		{
			$offset = 0;
			$sClassPath .= '.Null'; // This is a sentinel. The last token is not processed in the while loop.
			while(($dotPosition = strpos($sClassPath, '.', $offset)) !== false)
			{
				$class = substr($sClassPath, 0, $dotPosition);
				// Generate code for this class
				if(!array_key_exists($class, $classes))
				{
					$code .= "{$this->sXajaxPrefix}$class = {};\n";
					$classes[$class] = $class;
				}
				$offset = $dotPosition + 1;
			}
		}
		$classes = null;

		foreach($this->aCallableObjects as $xCallableObject)
		{
			$code .= $xCallableObject->getClientScript($this->sXajaxPrefix);
		}
		return $code;
	}

	/*
		Function: canProcessRequest
	*/
	public function canProcessRequest()
	{
		return ($this->sRequestedClass != NULL && $this->sRequestedMethod != NULL);
	}

	/*
		Function: processRequest
	*/
	public function processRequest()
	{
		if(!$this->canProcessRequest())
			return false;

		$aArgs = RequestManager::getInstance()->process();

		if(array_key_exists($this->sRequestedClass, $this->aCallableObjects))
		{
			$xCallableObject = $this->aCallableObjects[$this->sRequestedClass];
			if($xCallableObject->hasMethod($this->sRequestedMethod))
			{
				$xCallableObject->call($this->sRequestedMethod, $aArgs);
				return true;
			}
		}

		return xajax_trans('errors.objects.invalid',
			array('class' => $this->sRequestedClass, 'method' => $this->sRequestedMethod));
	}
}
