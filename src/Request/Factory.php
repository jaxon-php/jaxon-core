<?php

/**
 * Factory.php - Jaxon Request Factory
 *
 * Create Jaxon client side requests, which will generate the client script necessary
 * to invoke a jaxon request from the browser to registered objects.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-2-Clause BSD 2-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request;

use Jaxon\Jaxon;
use Jaxon\Utils\Container;

class Factory
{
    /**
     * Return the javascript call to an Jaxon function or object method
     *
     * @param string         $sName            The function or method (with class) name
     * @param ...            $xParams        The parameters of the function or method
     *
     * @return \Jaxon\Request\Request
     */
    public static function call($sName)
    {
        // There should be at least on argument to this method, the name of the Jaxon function or method
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
            if($xParam instanceof Interfaces\Parameter)
            {
                $xRequest->addParameter($xParam->getType(), $xParam->getValue());
            }
            else if(is_numeric($xParam))
            {
                $xRequest->addParameter(Jaxon::NUMERIC_VALUE, $xParam);
            }
            else if(is_string($xParam))
            {
                $xRequest->addParameter(Jaxon::QUOTED_VALUE, $xParam);
            }
            else if(is_bool($xParam))
            {
                $xRequest->addParameter(Jaxon::BOOL_VALUE, $xParam);
            }
            else if(is_array($xParam) || is_object($xParam))
            {
                $xRequest->addParameter(Jaxon::JS_VALUE, $xParam);
            }
        }
        return $xRequest;
    }

    /**
     * Make the pagination links for a registered Jaxon class method
     *
     * @param integer $nItemsTotal the total number of items
     * @param integer $nItemsPerPage the number of items per page page
     * @param integer $nCurrentPage the current page
     * @param string  $sMethod the name of function or a method prepended with its class name
     * @param ... $parameters the parameters of the method
     *
     * @return string the pagination links
     */
    public static function paginate($nItemsTotal, $nItemsPerPage, $nCurrentPage, $sMethod)
    {
        // Get the args list starting from the $sMethod
        $aArgs = array_slice(func_get_args(), 3);
        // Make the request
        $request = call_user_func_array('self::call', $aArgs);
        $paginator = Container::getInstance()->getPaginator();
        $paginator->setup($nItemsTotal, $nItemsPerPage, $nCurrentPage, $request);
        return $paginator->toHtml();
    }

    /**
     * Make a parameter of type Jaxon::FORM_VALUES
     * 
     * @param string $sFormId the id of the HTML form
     * 
     * @return Parameter
     */
    public static function form($sFormId)
    {
        return new Parameter(Jaxon::FORM_VALUES, $sFormId);
    }

    /**
     * Make a parameter of type Jaxon::INPUT_VALUE
     * 
     * @param string $sInputId the id of the HTML input element
     * 
     * @return Parameter
     */
    public static function input($sInputId)
    {
        return new Parameter(Jaxon::INPUT_VALUE, $sInputId);
    }

    /**
     * Make a parameter of type Jaxon::CHECKED_VALUE
     * 
     * @param string $sInputId the name of the HTML form element
     * 
     * @return Parameter
     */
    public static function checked($sInputId)
    {
        return new Parameter(Jaxon::CHECKED_VALUE, $sInputId);
    }

    /**
     * Make a parameter of type Jaxon::CHECKED_VALUE
     * 
     * @param string $sInputId the name of the HTML form element
     * 
     * @return Parameter
     */
    public static function select($sInputId)
    {
        return self::input($sInputId);
    }

    /**
     * Make a parameter of type Jaxon::ELEMENT_INNERHTML
     * 
     * @param string $sElementId the id of the HTML element
     * 
     * @return Parameter
     */
    public static function html($sElementId)
    {
        return new Parameter(Jaxon::ELEMENT_INNERHTML, $sElementId);
    }

    /**
     * Make a parameter of type Jaxon::QUOTED_VALUE
     * 
     * @param string $sValue the value of the parameter
     * 
     * @return Parameter
     */
    public static function string($sValue)
    {
        return new Parameter(Jaxon::QUOTED_VALUE, $sValue);
    }

    /**
     * Make a parameter of type Jaxon::NUMERIC_VALUE
     * 
     * @param numeric $nValue the value of the parameter
     * 
     * @return Parameter
     */
    public static function numeric($nValue)
    {
        return new Parameter(Jaxon::NUMERIC_VALUE, intval($nValue));
    }

    /**
     * Make a parameter of type Jaxon::NUMERIC_VALUE
     * 
     * @param numeric $nValue the value of the parameter
     * 
     * @return Parameter
     */
    public static function int($nValue)
    {
        return self::numeric($nValue);
    }

    /**
     * Make a parameter of type Jaxon::JS_VALUE
     * 
     * @param string $sValue the javascript code of the parameter
     * 
     * @return Parameter
     */
    public static function javascript($sValue)
    {
        return new Parameter(Jaxon::JS_VALUE, $sValue);
    }

    /**
     * Make a parameter of type Jaxon::JS_VALUE
     * 
     * @param string $sValue the javascript code of the parameter
     * 
     * @return Parameter
     */
    public static function js($sValue)
    {
        return self::javascript($sValue);
    }

    /**
     * Make a parameter of type Jaxon::PAGE_NUMBER
     * 
     * @return Parameter
     */
    public static function page()
    {
        // By default, the value of a parameter of type Jaxon::PAGE_NUMBER is 0.
        return new Parameter(Jaxon::PAGE_NUMBER, 0);
    }
}
