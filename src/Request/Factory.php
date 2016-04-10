<?php

namespace Xajax\Request;

class Factory
{
	/**
	 * Return the javascript call to an Xajax function or object method
	 *
	 * @param string 		$sName			The function method (with class) name
	 * @param ...			$xParams		The parameters of the method
	 *
	 * @return object
	 */
	public static function make()
	{
		// There should be at least on argument to this method, the name of the Xajax function or method
		if(($nArgs = func_num_args()) < 1 || !is_string(($sName = func_get_arg(0))))
		{
			return null;
		}
		// Make the request
		$xRequest = new Request($sName);
		$xRequest->useSingleQuote();
		for($nArg = 1; $nArg < $nArgs; $nArg++)
		{
			$xParam = func_get_arg($nArg);
			if(is_numeric($xParam))
			{
				$xRequest->addParameter(XAJAX_NUMERIC_VALUE, $xParam);
			}
			else if(is_string($xParam))
			{
				$xRequest->addParameter(XAJAX_QUOTED_VALUE, $xParam);
			}
			else if(is_array($xParam))
			{
				$xRequest->addParameter($xParam[0], $xParam[1]);
			}
		}
		return $xRequest;
	}

	/**
	 * Make a parameter of type XAJAX_FORM_VALUES
	 * 
	 * @param string $sFormId the id of the HTML form
	 * @return array
	 */
	public static function form($sFormId)
	{
		return array(XAJAX_FORM_VALUES, $sFormId);
	}

	/**
	 * Make a parameter of type XAJAX_INPUT_VALUE
	 * 
	 * @param string $sInputId the id of the HTML input element
	 * @return array
	 */
	public static function input($sInputId)
	{
		return array(XAJAX_INPUT_VALUE, $sInputId);
	}

	/**
	 * Make a parameter of type XAJAX_CHECKED_VALUE
	 * 
	 * @param string $sInputId the name of the HTML form element
	 * @return array
	 */
	public static function checked($sInputId)
	{
		return array(XAJAX_CHECKED_VALUE, $sInputId);
	}

	/**
	 * Make a parameter of type XAJAX_CHECKED_VALUE
	 * 
	 * @param string $sInputId the name of the HTML form element
	 * @return array
	 */
	public static function select($sInputId)
	{
		return self::checked($sInputId);
	}

	/**
	 * Make a parameter of type XAJAX_ELEMENT_INNERHTML
	 * 
	 * @param string $sElementId the id of the HTML element
	 * @return array
	 */
	public static function html($sElementId)
	{
		return array(XAJAX_ELEMENT_INNERHTML, $sElementId);
	}

	/**
	 * Make a parameter of type XAJAX_QUOTED_VALUE
	 * 
	 * @param string $sValue the value of the parameter
	 * @return array
	 */
	public static function string($sValue)
	{
		return array(XAJAX_QUOTED_VALUE, $sValue);
	}

	/**
	 * Make a parameter of type XAJAX_NUMERIC_VALUE
	 * 
	 * @param numeric $nValue the value of the parameter
	 * @return array
	 */
	public static function numeric($nValue)
	{
		return array(XAJAX_NUMERIC_VALUE, intval($nValue));
	}

	/**
	 * Make a parameter of type XAJAX_NUMERIC_VALUE
	 * 
	 * @param numeric $nValue the value of the parameter
	 * @return array
	 */
	public static function int($nValue)
	{
		return self::numeric($nValue);
	}

	/**
	 * Make a parameter of type XAJAX_JS_VALUE
	 * 
	 * @param string $sValue the javascript code of the parameter
	 * @return array
	 */
	public static function javascript($sValue)
	{
		return array(XAJAX_JS_VALUE, $sValue);
	}

	/**
	 * Make a parameter of type XAJAX_JS_VALUE
	 * 
	 * @param string $sValue the javascript code of the parameter
	 * @return array
	 */
	public static function js($sValue)
	{
		return self::javascript($sValue);
	}

	/**
	 * Make a parameter of type XAJAX_PAGE_NUMBER
	 * 
	 * @return array
	 */
	public static function page()
	{
		// By default, the value of a parameter of type XAJAX_PAGE_NUMBER is 0.
		return array(XAJAX_PAGE_NUMBER, 0);
	}
}
