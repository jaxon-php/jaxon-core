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

use Jaxon\Request\Factory\Request;
use Jaxon\Request\Factory\Parameter;
use Jaxon\Request\Support\CallableObject;
use Jaxon\Request\Support\CallableRepository;
use Jaxon\Utils\Pagination\Paginator;

// Extends Parameter for compatibility with older versions (see function rq())
class Factory
{
    use \Jaxon\Features\Config;

    /**
     * The prefix to prepend on each call
     *
     * @var string
     */
    protected $sPrefix = '';

    /**
     * The callable repository
     *
     * @var CallableRepository
     */
    protected $xRepository = null;

    /**
     * The class constructor
     *
     * @param CallableRepository    $xRepository
     */
    public function __construct(CallableRepository $xRepository)
    {
        $this->xRepository = $xRepository;
    }

    /**
     * Set the name of the class to call
     *
     * @param string|null       $sClass              The callable class
     *
     * @return Factory
     */
    public function setClassName($sClass)
    {
        $this->sPrefix = $this->getOption('core.prefix.function');

        $sClass = trim($sClass, '.\\ ');
        if(!$sClass)
        {
            return $this;
        }

        if(!($xCallable = $this->xRepository->getCallableObject($sClass)))
        {
            // Todo: decide which of these values to return
            // return null;
            return $this;
        }

        $this->sPrefix = $this->getOption('core.prefix.class') . $xCallable->getJsName() . '.';
        return $this;
    }

    /**
     * Set the callable object to call
     *
     * @param CallableObject    $xCallable              The callable object
     *
     * @return Factory
     */
    public function setCallable(CallableObject $xCallable)
    {
        $this->sPrefix = $this->getOption('core.prefix.class') . $xCallable->getJsName() . '.';

        return $this;
    }

    /**
     * Return the javascript call to a Jaxon function or object method
     *
     * @param string            $sFunction          The function or method (without class) name
     * @param ...               $xParams            The parameters of the function or method
     *
     * @return Request
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
     * @return Request
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
     *
     * @return Paginator
     */
    public function paginate($nItemsTotal, $nItemsPerPage, $nCurrentPage)
    {
        // Get the args list starting from the $sMethod
        $aArgs = array_slice(func_get_args(), 3);
        // Make the request
        $xRequest = call_user_func_array([$this, 'call'], $aArgs);

        return jaxon()->di()->getPaginator()
            ->setup($nItemsTotal, $nItemsPerPage, $nCurrentPage, $xRequest);
    }

    /**
     * Make a parameter of type Parameter::FORM_VALUES
     *
     * @param string        $sFormId                The id of the HTML form
     *
     * @return Parameter
     */
    public function form($sFormId)
    {
        return new Parameter(Parameter::FORM_VALUES, $sFormId);
    }

    /**
     * Make a parameter of type Parameter::INPUT_VALUE
     *
     * @param string $sInputId the id of the HTML input element
     *
     * @return Parameter
     */
    public function input($sInputId)
    {
        return new Parameter(Parameter::INPUT_VALUE, $sInputId);
    }

    /**
     * Make a parameter of type Parameter::CHECKED_VALUE
     *
     * @param string $sInputId the name of the HTML form element
     *
     * @return Parameter
     */
    public function checked($sInputId)
    {
        return new Parameter(Parameter::CHECKED_VALUE, $sInputId);
    }

    /**
     * Make a parameter of type Parameter::CHECKED_VALUE
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
     * Make a parameter of type Parameter::ELEMENT_INNERHTML
     *
     * @param string $sElementId the id of the HTML element
     *
     * @return Parameter
     */
    public function html($sElementId)
    {
        return new Parameter(Parameter::ELEMENT_INNERHTML, $sElementId);
    }

    /**
     * Make a parameter of type Parameter::QUOTED_VALUE
     *
     * @param string $sValue the value of the parameter
     *
     * @return Parameter
     */
    public function string($sValue)
    {
        return new Parameter(Parameter::QUOTED_VALUE, $sValue);
    }

    /**
     * Make a parameter of type Parameter::NUMERIC_VALUE
     *
     * @param numeric $nValue the value of the parameter
     *
     * @return Parameter
     */
    public function numeric($nValue)
    {
        return new Parameter(Parameter::NUMERIC_VALUE, intval($nValue));
    }

    /**
     * Make a parameter of type Parameter::NUMERIC_VALUE
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
     * Make a parameter of type Parameter::JS_VALUE
     *
     * @param string $sValue the javascript code of the parameter
     *
     * @return Parameter
     */
    public function javascript($sValue)
    {
        return new Parameter(Parameter::JS_VALUE, $sValue);
    }

    /**
     * Make a parameter of type Parameter::JS_VALUE
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
     * Make a parameter of type Parameter::PAGE_NUMBER
     *
     * @return Parameter
     */
    public function page()
    {
        // By default, the value of a parameter of type Parameter::PAGE_NUMBER is 0.
        return new Parameter(Parameter::PAGE_NUMBER, 0);
    }
}
