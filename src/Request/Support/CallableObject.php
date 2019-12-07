<?php

/**
 * CallableObject.php - Jaxon callable object
 *
 * This class stores a reference to an object whose methods can be called from
 * the client via an Jaxon request
 *
 * The Jaxon plugin manager will call <CallableObject->getClientScript> so that
 * stub functions can be generated and sent to the browser.
 *
 * @package jaxon-core
 * @author Jared White
 * @author J. Max Wilson
 * @author Joseph Woolley
 * @author Steffen Konerow
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
 * @copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Request\Support;

use Jaxon\Request\Request;
use Jaxon\Response\Response;

class CallableObject
{
    /**
     * A reference to the callable object the user has registered
     *
     * @var object
     */
    private $xRegisteredObject = null;

    /**
     * The reflection class of the user registered callable object
     *
     * @var \ReflectionClass
     */
    private $xReflectionClass;

    /**
     * A list of methods of the user registered callable object the library must not export to javascript
     *
     * @var array
     */
    private $aProtectedMethods = [];

    /**
     * The namespace the callable class was registered from
     *
     * @var string
     */
    private $sNamespace = '';

    /**
     * The character to use as separator in javascript class names
     *
     * @var string
     */
    private $sSeparator = '.';

    /**
     * The class constructor
     *
     * @param string            $sCallable               The callable object instance or class name
     *
     */
    public function __construct($sCallable)
    {
        $this->xReflectionClass = new \ReflectionClass($sCallable);
    }

    /**
     * Return the class name of this callable object, without the namespace if any
     *
     * @return string
     */
    public function getClassName()
    {
        // Get the class name without the namespace.
        return $this->xReflectionClass->getShortName();
    }

    /**
     * Return the name of this callable object
     *
     * @return string
     */
    public function getName()
    {
        // Get the class name with the namespace.
        return $this->xReflectionClass->getName();
    }

    /**
     * Return the name of the corresponding javascript class
     *
     * @return string
     */
    public function getJsName()
    {
        return str_replace('\\', $this->sSeparator, $this->getName());
    }

    /**
     * Return the namespace of this callable object
     *
     * @return string
     */
    public function getNamespace()
    {
        // The namespace of the registered class.
        return $this->xReflectionClass->getNamespaceName();
    }

    /**
     * Return the namespace the callable class was registered from
     *
     * @return string
     */
    public function getRootNamespace()
    {
        // The namespace the callable class was registered from.
        return $this->sNamespace;
    }

    /**
     * Set configuration options / call options for each method
     *
     * @param string        $sName              The name of the configuration option
     * @param string|array  $sValue             The value of the configuration option
     *
     * @return void
     */
    public function configure($sName, $sValue)
    {
        switch($sName)
        {
        // Set the separator
        case 'separator':
            if($sValue == '_' || $sValue == '.')
            {
                $this->sSeparator = $sValue;
            }
            break;
        // Set the namespace
        case 'namespace':
            if(is_string($sValue))
            {
                $this->sNamespace = $sValue;
            }
            break;
        // Set the protected methods
        case 'protected':
            if(is_array($sValue))
            {
                $this->aProtectedMethods = array_merge($this->aProtectedMethods, $sValue);
            }
            elseif(is_string($sValue))
            {
                $this->aProtectedMethods[] = $sValue;
            }
            break;
        default:
            break;
        }
    }

    /**
     * Return a list of methods of the callable object to export to javascript
     *
     * @return array
     */
    public function getMethods()
    {
        $aMethods = [];
        foreach($this->xReflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $xMethod)
        {
            $sMethodName = $xMethod->getShortName();
            // Don't take magic __call, __construct, __destruct methods
            if(strlen($sMethodName) > 2 && substr($sMethodName, 0, 2) == '__')
            {
                continue;
            }
            // Don't take excluded methods
            if(in_array($sMethodName, $this->aProtectedMethods))
            {
                continue;
            }
            $aMethods[] = $sMethodName;
        }
        return $aMethods;
    }

    /**
     * Return the registered callable object
     *
     * @return null|object
     */
    public function getRegisteredObject()
    {
        if($this->xRegisteredObject == null)
        {
            $di = jaxon()->di();
            $this->xRegisteredObject = $di->make($this->xReflectionClass);
            // Initialize the object
            if($this->xRegisteredObject instanceof \Jaxon\CallableClass)
            {
                // Set the members of the object
                $cSetter = function($xCallable, $xResponse) {
                    $this->callable = $xCallable;
                    $this->response = $xResponse;
                };
                // Can now access protected attributes
                \call_user_func($cSetter->bindTo($this->xRegisteredObject,
                    $this->xRegisteredObject), $this, jaxon()->getResponse());
            }

            // Run the callback for class initialisation
            if(($xCallback = $di->getRequestHandler()->getCallbackManager()->init()))
            {
                \call_user_func($xCallback, $this->xRegisteredObject);
            }
        }
        return $this->xRegisteredObject;
    }

    /**
     * Check if the specified method name is one of the methods of the registered callable object
     *
     * @param string        $sMethod            The name of the method to check
     *
     * @return boolean
     */
    public function hasMethod($sMethod)
    {
        return $this->xReflectionClass->hasMethod($sMethod)/* || $this->xReflectionClass->hasMethod('__call')*/;
    }

    /**
     * Call the specified method of the registered callable object using the specified array of arguments
     *
     * @param string        $sMethod            The name of the method to call
     * @param array         $aArgs              The arguments to pass to the method
     *
     * @return null|Response
     */
    public function call($sMethod, $aArgs)
    {
        if(!$this->hasMethod($sMethod))
        {
            return null;
        }
        $reflectionMethod = $this->xReflectionClass->getMethod($sMethod);
        $xRegisteredObject = $this->getRegisteredObject();
        return $reflectionMethod->invokeArgs($xRegisteredObject, $aArgs);
    }
}
