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
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request;

use Jaxon\Jaxon;

class Factory
{
    use \Jaxon\Utils\Traits\Config;

    /**
     * The prefix to prepend on each call
     *
     * @var string
     */
    protected $sPrefix;

    /**
     * Set the name of the class to call
     *
     * @param string|null            $Class              The callable class
     *
     * @return Factory
     */
    public function setCallable($sClass)
    {
        $sClass = trim($sClass, '.\\ ');
        if(($sClass))
        {
            $xCallable = jaxon()->di()->get($sClass);
            $this->sPrefix = $this->getOption('core.prefix.class') . $xCallable->getJsName() . '.';
        }
        else
        {
            $this->sPrefix = $this->getOption('core.prefix.function');
        }
    }

    /**
     * Return the javascript call to a Jaxon function or object method
     *
     * @param string            $sFunction          The function or method (without class) name
     * @param ...               $xParams            The parameters of the function or method
     *
     * @return \Jaxon\Request\Request
     */
    public function call($sFunction)
    {
        $aArguments = func_get_args();
        $sFunction = (string)$sFunction;
        // Remove the function name from the arguments array.
        array_shift($aArguments);

        // Makes legacy code works
        if(strpos($sFunction, '.') !== false)
        {
            // If there is a dot in the name, then it is a call to a class
            $this->sPrefix = $this->getOption('core.prefix.class');
        }

        // Make the request
        $xRequest = new Request($this->sPrefix . $sFunction);
        $xRequest->useSingleQuote();
        $xRequest->addParameters($aArguments);
        return $xRequest;
    }

    /**
     * Return the javascript call to a generic function
     *
     * @param string            $sFunction          The function or method (with class) name
     * @param ...               $xParams            The parameters of the function or method
     *
     * @return \Jaxon\Request\Request
     */
    public function func($sFunction)
    {
        $aArguments = func_get_args();
        $sFunction = (string)$sFunction;
        // Remove the function name from the arguments array.
        array_shift($aArguments);
        // Make the request
        $xRequest = new Request($sFunction);
        $xRequest->useSingleQuote();
        $xRequest->addParameters($aArguments);
        return $xRequest;
    }

    /**
     * Make the pagination links for a registered Jaxon class method
     *
     * @param integer       $nItemsTotal            The total number of items
     * @param integer       $nItemsPerPage          The number of items per page page
     * @param integer       $nCurrentPage           The current page
     * @param string        $sMethod                The name of function or a method prepended with its class name
     * @param ...           $xParams                The parameters of the function or method
     *
     * @return string the pagination links
     */
    public function paginate($nItemsTotal, $nItemsPerPage, $nCurrentPage, $sMethod)
    {
        // Get the args list starting from the $sMethod
        $aArgs = array_slice(func_get_args(), 3);
        // Make the request
        $request = call_user_func_array('self::call', $aArgs);
        $paginator = jaxon()->paginator($nItemsTotal, $nItemsPerPage, $nCurrentPage, $request);
        return $paginator->toHtml();
    }

    /**
     * Make a parameter of type Jaxon::FORM_VALUES
     *
     * @param string        $sFormId                The id of the HTML form
     *
     * @return Parameter
     */
    public function form($sFormId)
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
    public function input($sInputId)
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
    public function checked($sInputId)
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
    public function select($sInputId)
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
    public function html($sElementId)
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
    public function string($sValue)
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
    public function numeric($nValue)
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
    public function int($nValue)
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
    public function javascript($sValue)
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
    public function js($sValue)
    {
        return self::javascript($sValue);
    }

    /**
     * Make a parameter of type Jaxon::PAGE_NUMBER
     *
     * @return Parameter
     */
    public function page()
    {
        // By default, the value of a parameter of type Jaxon::PAGE_NUMBER is 0.
        return new Parameter(Jaxon::PAGE_NUMBER, 0);
    }
}
