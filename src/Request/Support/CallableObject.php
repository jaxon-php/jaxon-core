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

class CallableObject
{
    /**
     * A reference to the callable object the user has registered
     *
     * @var object
     */
    private $registeredObject = null;

    /**
     * The reflection class of the user registered callable object
     *
     * @var \ReflectionClass
     */
    private $reflectionClass;

    /**
     * A list of methods of the user registered callable object the library must not export to javascript
     *
     * @var array
     */
    private $aProtectedMethods = [];

    /**
     * The character to use as separator in javascript class names
     *
     * @var string
     */
    private $separator = '.';

    /**
     * The class constructor
     *
     * @param string            $sCallable               The callable object instance or class name
     *
     */
    public function __construct($sCallable)
    {
        $this->reflectionClass = new \ReflectionClass($sCallable);
    }

    /**
     * Return the class name of this callable object, without the namespace if any
     *
     * @return string
     */
    public function getClassName()
    {
        // Get the class name without the namespace.
        return $this->reflectionClass->getShortName();
    }

    /**
     * Return the name of this callable object
     *
     * @return string
     */
    public function getName()
    {
        // Get the class name with the namespace.
        return $this->reflectionClass->getName();
    }

    /**
     * Return the name of the corresponding javascript class
     *
     * @return string
     */
    public function getJsName()
    {
        return str_replace('\\', $this->separator, $this->getName());
    }

    /**
     * Return the namespace of this callable object
     *
     * @return string
     */
    public function getNamespace()
    {
        // The namespace the class was registered with.
        return $this->reflectionClass->getNamespaceName();
    }

    /**
     * Set configuration options / call options for each method
     *
     * @param string        $sName              The name of the configuration option
     * @param string        $sValue             The value of the configuration option
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
                $this->separator = $sValue;
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
        foreach($this->reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $xMethod)
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
     * @return object
     */
    public function getRegisteredObject()
    {
        if($this->registeredObject == null)
        {
            $di = jaxon()->di();
            // Use the Reflection class to get the parameters of the constructor
            if(($constructor = $this->reflectionClass->getConstructor()) != null)
            {
                $parameters = $constructor->getParameters();
                $parameterInstances = [];
                foreach($parameters as $parameter)
                {
                    // Get the parameter instance from the DI
                    $parameterInstances[] = $di->get($parameter->getClass()->getName());
                }
                $this->registeredObject = $this->reflectionClass->newInstanceArgs($parameterInstances);
            }
            else
            {
                $this->registeredObject = $this->reflectionClass->newInstance();
            }
        }
        return $this->registeredObject;
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
        return $this->reflectionClass->hasMethod($sMethod)/* || $this->reflectionClass->hasMethod('__call')*/;
    }

    /**
     * Call the specified method of the registered callable object using the specified array of arguments
     *
     * @param string        $sMethod            The name of the method to call
     * @param array         $aArgs              The arguments to pass to the method
     *
     * @return void
     */
    public function call($sMethod, $aArgs)
    {
        if(!$this->hasMethod($sMethod))
        {
            return;
        }
        $reflectionMethod = $this->reflectionClass->getMethod($sMethod);
        $registeredObject = $this->getRegisteredObject();
        return $reflectionMethod->invokeArgs($registeredObject, $aArgs);
    }
}
