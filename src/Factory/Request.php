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

namespace Jaxon\Factory;

use Jaxon\Jaxon;
use Jaxon\Request\Request as JaxonRequest;
use Jaxon\Request\Support\CallableObject;

// Extends Parameter for compatibility with older versions (see function rq())
class Request extends Parameter
{
    use \Jaxon\Utils\Traits\Config;

    /**
     * The prefix to prepend on each call
     *
     * @var string
     */
    protected $sPrefix;

    /**
     * The callable dir plugin
     *
     * @var Jaxon\Request\Plugin\CallableDir;
     */
    protected $xCallableDirPlugin;

    /**
     * The callable class plugin
     *
     * @var Jaxon\Request\Plugin\CallableClass;
     */
    protected $xCallableClassPlugin;

    /**
     * The class constructor
     */
    public function __construct()
    {
        $xPluginManager = jaxon()->getPluginManager();
        $this->xCallableDirPlugin = $xPluginManager->getRequestPlugin(Jaxon::CALLABLE_DIR);
        $this->xCallableClassPlugin = $xPluginManager->getRequestPlugin(Jaxon::CALLABLE_CLASS);
    }

    /**
     * Set the name of the class to call
     *
     * @param string|null            $sClass              The callable class
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

        $xCallable = $this->xCallableClassPlugin->getCallableObject($sClass);
        if(!$xCallable)
        {
            $xCallable = $this->xCallableDirPlugin->getCallableObject($sClass);
        }
        if(!$xCallable)
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
     * @param CallableObject          $xCallable              The callable object
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
        $xRequest = new JaxonRequest($this->sPrefix . $sFunction);
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
        $xRequest = new JaxonRequest($sFunction);
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
}
