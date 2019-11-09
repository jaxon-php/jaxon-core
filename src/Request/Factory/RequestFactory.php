<?php

/**
 * RequestFactory.php - Jaxon Request Factory
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

namespace Jaxon\Request\Factory;

use Jaxon\Request\Support\CallableObject;
use Jaxon\Request\Support\CallableRepository;
use Jaxon\Utils\Pagination\Paginator;

// Extends Parameter for compatibility with older versions (see function rq())
class RequestFactory
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

        return $this->setCallable($xCallable);
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
}
