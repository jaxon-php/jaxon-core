<?php

namespace Xajax\Request\Plugin;

use Xajax\Plugin\Request as RequestPlugin;
use Xajax\Plugin\Manager as PluginManager;
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
class CallableObject extends RequestPlugin
{
	use \Xajax\Utils\ConfigTrait;

	/*
		Array: aCallableObjects
	*/
	protected $aCallableObjects;

	/*
		Array: aClassPaths
	*/
	protected $aClassPaths;

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

		$this->sRequestedClass = null;
		$this->sRequestedMethod = null;

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
		Function: register
	*/
	public function register($aArgs)
	{
		if(count($aArgs) > 1)
		{
			$sType = $aArgs[0];

			if($sType == XAJAX_CALLABLE_OBJECT)
			{
				$xCallableObject = $aArgs[1];

				if(!is_object($xCallableObject))
				{
					throw new \Xajax\Exception\Error('errors.objects.instance');
				}

				if(!($xCallableObject instanceof \Xajax\Request\Support\CallableObject))
					$xCallableObject = new \Xajax\Request\Support\CallableObject($xCallableObject);

				if(count($aArgs) > 2 && is_array($aArgs[2]))
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

				return $xCallableObject->generateRequests($this->getOption('wrapperPrefix'));
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
		$sXajaxPrefix = $this->getOption('wrapperPrefix');
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
					$code .= "$sXajaxPrefix$class = {};\n";
					$classes[$class] = $class;
				}
				$offset = $dotPosition + 1;
			}
		}
		$classes = null;

		foreach($this->aCallableObjects as $xCallableObject)
		{
			$code .= $xCallableObject->getClientScript($sXajaxPrefix);
		}
		return $code;
	}

	/*
		Function: canProcessRequest
	*/
	public function canProcessRequest()
	{
		return ($this->sRequestedClass != null && $this->sRequestedMethod != null);
	}

	/*
		Function: processRequest
	*/
	public function processRequest()
	{
		if(!$this->canProcessRequest())
			return false;

		$aArgs = RequestManager::getInstance()->process();

		// Try to register an instance of the requested class, if it isn't yet
		if(!array_key_exists($this->sRequestedClass, $this->aCallableObjects))
		{
			PluginManager::getInstance()->registerClass($this->sRequestedClass);
		}

		if(array_key_exists($this->sRequestedClass, $this->aCallableObjects))
		{
			$xCallableObject = $this->aCallableObjects[$this->sRequestedClass];
			if($xCallableObject->hasMethod($this->sRequestedMethod))
			{
				$xCallableObject->call($this->sRequestedMethod, $aArgs);
				return true;
			}
		}
		// Unable to find the requested object or method
		throw new \Xajax\Exception\Error('errors.objects.invalid',
			array('class' => $this->sRequestedClass, 'method' => $this->sRequestedMethod));
	}
}
