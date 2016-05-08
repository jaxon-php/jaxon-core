<?php

/**
 * Factory.php - Xajax Request Factory
 *
 * Create Xajax client side requests, which will generate the client script necessary
 * to invoke a xajax request from the browser to registered objects.
 *
 * @package xajax-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-2-Clause BSD 2-Clause License
 * @link https://github.com/lagdo/xajax-core
 */

namespace Xajax\Request;

use Xajax\Xajax;

class Factory
{
	/**
	 * Return the javascript call to an Xajax function or object method
	 *
	 * @param string 		$sName			The function or method (with class) name
	 * @param ...			$xParams		The parameters of the function or method
	 *
	 * @return object
	 */
	public static function make($sName)
	{
		// There should be at least on argument to this method, the name of the Xajax function or method
		if(($nArgs = func_num_args()) < 1 || !is_string(($sName = func_get_arg(0))))
		{
			return null;
		}
		// If there is a dot in the name, then it is a call to a class, else it is a call to a function
		$sType = (strpos($sName, '.') === false ? 'function' : 'class');
		// Make the request
		$xRequest = new Request($sName, $sType);
		$xRequest->useSingleQuote();
		for($nArg = 1; $nArg < $nArgs; $nArg++)
		{
			$xParam = func_get_arg($nArg);
			if(is_numeric($xParam))
			{
				$xRequest->addParameter(Xajax::NUMERIC_VALUE, $xParam);
			}
			else if(is_string($xParam))
			{
				$xRequest->addParameter(Xajax::QUOTED_VALUE, $xParam);
			}
			else if(is_array($xParam))
			{
				$xRequest->addParameter($xParam[0], $xParam[1]);
			}
		}
		return $xRequest;
	}

	/**
	 * Make a parameter of type Xajax::FORM_VALUES
	 * 
	 * @param string $sFormId the id of the HTML form
	 * @return array
	 */
	public static function form($sFormId)
	{
		return array(Xajax::FORM_VALUES, $sFormId);
	}

	/**
	 * Make a parameter of type Xajax::INPUT_VALUE
	 * 
	 * @param string $sInputId the id of the HTML input element
	 * @return array
	 */
	public static function input($sInputId)
	{
		return array(Xajax::INPUT_VALUE, $sInputId);
	}

	/**
	 * Make a parameter of type Xajax::CHECKED_VALUE
	 * 
	 * @param string $sInputId the name of the HTML form element
	 * @return array
	 */
	public static function checked($sInputId)
	{
		return array(Xajax::CHECKED_VALUE, $sInputId);
	}

	/**
	 * Make a parameter of type Xajax::CHECKED_VALUE
	 * 
	 * @param string $sInputId the name of the HTML form element
	 * @return array
	 */
	public static function select($sInputId)
	{
		return self::input($sInputId);
	}

	/**
	 * Make a parameter of type Xajax::ELEMENT_INNERHTML
	 * 
	 * @param string $sElementId the id of the HTML element
	 * @return array
	 */
	public static function html($sElementId)
	{
		return array(Xajax::ELEMENT_INNERHTML, $sElementId);
	}

	/**
	 * Make a parameter of type Xajax::QUOTED_VALUE
	 * 
	 * @param string $sValue the value of the parameter
	 * @return array
	 */
	public static function string($sValue)
	{
		return array(Xajax::QUOTED_VALUE, $sValue);
	}

	/**
	 * Make a parameter of type Xajax::NUMERIC_VALUE
	 * 
	 * @param numeric $nValue the value of the parameter
	 * @return array
	 */
	public static function numeric($nValue)
	{
		return array(Xajax::NUMERIC_VALUE, intval($nValue));
	}

	/**
	 * Make a parameter of type Xajax::NUMERIC_VALUE
	 * 
	 * @param numeric $nValue the value of the parameter
	 * @return array
	 */
	public static function int($nValue)
	{
		return self::numeric($nValue);
	}

	/**
	 * Make a parameter of type Xajax::JS_VALUE
	 * 
	 * @param string $sValue the javascript code of the parameter
	 * @return array
	 */
	public static function javascript($sValue)
	{
		return array(Xajax::JS_VALUE, $sValue);
	}

	/**
	 * Make a parameter of type Xajax::JS_VALUE
	 * 
	 * @param string $sValue the javascript code of the parameter
	 * @return array
	 */
	public static function js($sValue)
	{
		return self::javascript($sValue);
	}

	/**
	 * Make a parameter of type Xajax::PAGE_NUMBER
	 * 
	 * @return array
	 */
	public static function page()
	{
		// By default, the value of a parameter of type Xajax::PAGE_NUMBER is 0.
		return array(Xajax::PAGE_NUMBER, 0);
	}
}
