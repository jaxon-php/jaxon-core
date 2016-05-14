<?php

/**
 * UserFunction.php - Xajax user function
 *
 * This class stores a reference to a user defined function which can be called from the client via an Xajax request
 *
 * The function specification passed to the constructor of this class in one of the following formats:
 * - a three element array:
 *     (string) Alternate function name: when a method of a class has the same name as
 *              another function in the system, you can provide an alias to help avoid collisions.
 *     (object or class name) Class: the name of the class or an instance of the object which contains
 *              the function to be called.
 *     (string) Method:  the name of the method that will be called.
 * - a two element array:
 *     (object or class name) Class: the name of the class or an instance of the object which contains
 *              the function to be called.
 *     (string) Method:  the name of the method that will be called.
 * - a string:
 *     the name of the function that is available at global scope (not in a class).
 *
 * Examples:
 *      $myFunction = array('alias', 'myClass', 'myMethod');
 *      $myFunction = array('alias', &$myObject, 'myMethod');
 *      $myFunction = array('myClass', 'myMethod');
 *      $myFunction = array(&$myObject, 'myMethod');
 *      $myFunction = 'myFunction';
 *
 *      $myUserFunction = new UserFunction($myFunction);
 *      $xajax->register(Xajax::USER_FUNCTION, $myUserFunction);
 *
 * @package xajax-core
 * @author Jared White
 * @author J. Max Wilson
 * @author Joseph Woolley
 * @author Steffen Konerow
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
 * @copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-2-Clause BSD 2-Clause License
 * @link https://github.com/lagdo/xajax-core
 */

namespace Xajax\Request\Support;

use Xajax\Xajax;
use Xajax\Request\Request;
use Xajax\Request\Manager as RequestManager;
use Xajax\Response\Manager as ResponseManager;

class UserFunction
{
	use \Xajax\Utils\ContainerTrait;

	/**
	 * An alias to use for this function
	 *
	 * This is useful when you want to call the same xajax enabled function with
	 * a different set of call options from what was already registered.
	 *
	 * @var string
	 */
	private $sAlias;
	
	/**
	 * A string or an array which defines the function to be registered
	 *
	 * @var string
	 */
	private $sUserFunction;
	
	/**
	 * The path and file name of the include file where the function is defined
	 *
	 * @var string
	 */
	private $sInclude;
	
	/**
	 * An associative array containing call options that will be sent
	 * to the browser curing client script generation
	 *
	 * @var array
	 */
	private $aConfiguration;
	
	public function __construct($sUserFunction)
	{
		$this->aConfiguration = array();
		$this->sAlias = '';
		if(is_array($sUserFunction))
        {
        	if(count($sUserFunction) != 2 && count($sUserFunction) != 3)
        	{
				throw new \Xajax\Exception\Error('errors.functions.invalid-declaration');
        	}
        	if(count($sUserFunction) == 3)
			{
				$this->sAlias = $sUserFunction[0];
				$this->sUserFunction = array_slice($sUserFunction, 1);
			}
			else
			{
				$this->sUserFunction = $sUserFunction;
			}
        }
        else if(is_string($sUserFunction))
        {
			$this->sUserFunction = $sUserFunction;
        }
        else
        {
        	throw new \Xajax\Exception\Error('errors.functions.invalid-declaration');
        }
	}
	
	/**
	 * Get the name of the function being referenced
	 *
	 * @return string
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
	
	/**
	 * Set call options for this instance
	 *
	 * @param string		$sName				The name of the configuration option
	 * @param string		$sValue				The value of the configuration option
	 *
	 * @return void
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
	
	/**
	 * Constructs and returns a <xajaxRequest> object which is capable of generating
	 * the javascript call to invoke this xajax enabled function
	 *
	 * @return Xajax\Request\Request
	 */
	public function generateRequest()
	{
		$sAlias = (($this->sAlias) ? $this->sAlias : $this->getName());
		return new Request($sAlias, 'function');
	}
	
	/**
	 * Generate the javascript function stub that is sent to the browser on initial page load
	 *
	 * @return string
	 */
	public function getScript()
	{
		$sXajaxPrefix = $this->getOption('core.prefix.function');
		$sFunction = $this->getName();
		$sAlias = (($this->sAlias) ? $this->sAlias : $sFunction);

		return $this->render('support/function.js.tpl', array(
			'sPrefix' => $sXajaxPrefix,
			'sAlias' => $sAlias,
			'sFunction' => $sFunction,
			'aConfig' => $this->aConfiguration,
		));
	}

	/**
	 * Call the registered user function, including an external file if needed
	 * and passing along the specified arguments
	 *
	 * @param array 		$aArgs				The function arguments
	 *
	 * @return void
	 */
	public function call($aArgs = array())
	{
		if(($this->sInclude))
		{
			ob_start();
			require_once $this->sInclude;
			$sOutput = ob_get_clean();
			if(($sOutput))
			{
				$sOutput = $this->trans('debug.function.include', array(
					'file' => $this->sInclude,
					'output' => $sOutput
				));
				ResponseManager::getInstance()->debug($sOutput);
			}
		}
		$mFunction = $this->sUserFunction;
		ResponseManager::getInstance()->append(call_user_func_array($mFunction, $aArgs));
	}
}
