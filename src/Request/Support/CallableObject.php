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

use Jaxon\Container\Container;
use Jaxon\Request\Request;
use Jaxon\Response\Response;

use ReflectionClass;
use ReflectionException;

class CallableObject
{
    /**
     * The DI container
     *
     * @var Container
     */
    protected $di;

    /**
     * The reflection class of the user registered callable object
     *
     * @var ReflectionClass
     */
    private $xReflectionClass;

    /**
     * A list of methods of the user registered callable object the library must not export to javascript
     *
     * @var array
     */
    private $aProtectedMethods = [];

    /**
     * A list of methods to call before processing the request
     *
     * @var array
     */
    private $aBeforeMethods = [];

    /**
     * A list of methods to call after processing the request
     *
     * @var array
     */
    private $aAfterMethods = [];

    /**
     * The callable object options
     *
     * @var array
     */
    private $aOptions = [];

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
     * @param Container  $di
     * @param ReflectionClass $xReflectionClass    The reflection class
     *
     */
    public function __construct(Container $di, ReflectionClass $xReflectionClass)
    {
        $this->di = $di;
        $this->xReflectionClass = $xReflectionClass;
    }

    /**
     * Set callable object options
     *
     * @param array  $aOptions
     *
     * @return void
     */
    public function setOptions(array $aOptions)
    {
        $this->aOptions = $aOptions;
    }

    /**
     * Get callable object options
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->aOptions;
    }

    /**
     * Get the reflection class
     *
     * @return ReflectionClass
     */
    public function getReflectionClass(): ReflectionClass
    {
        return $this->xReflectionClass;
    }

    /**
     * Get the class name of this callable object, without the namespace if any
     *
     * @return string
     */
    public function getClassName(): string
    {
        // Get the class name without the namespace.
        return $this->xReflectionClass->getShortName();
    }

    /**
     * Get the name of this callable object
     *
     * @return string
     */
    public function getName(): string
    {
        // Get the class name with the namespace.
        return $this->xReflectionClass->getName();
    }

    /**
     * Get the name of the corresponding javascript class
     *
     * @return string
     */
    public function getJsName(): string
    {
        return str_replace('\\', $this->sSeparator, $this->getName());
    }

    /**
     * Get the namespace of this callable object
     *
     * @return string
     */
    public function getNamespace(): string
    {
        // The namespace of the registered class.
        return $this->xReflectionClass->getNamespaceName();
    }

    /**
     * Get the namespace the callable class was registered from
     *
     * @return string
     */
    public function getRootNamespace(): string
    {
        // The namespace the callable class was registered from.
        return $this->sNamespace;
    }

    /**
     * Set hook methods
     *
     * @param array $aHookMethods    The array of hook methods
     * @param string|array $xValue    The value of the configuration option
     *
     * @return void
     */
    public function setHookMethods(array &$aHookMethods, $xValue)
    {
        foreach($xValue as $sCalledMethod => $xMethodToCall)
        {
            if(is_array($xMethodToCall))
            {
                $aHookMethods[$sCalledMethod] = $xMethodToCall;
            }
            elseif(is_string($xMethodToCall))
            {
                $aHookMethods[$sCalledMethod] = [$xMethodToCall];
            }
        }
    }

    /**
     * Set configuration options / call options for each method
     *
     * @param string $sName    The name of the configuration option
     * @param string|array $xValue    The value of the configuration option
     *
     * @return void
     */
    public function configure(string $sName, $xValue)
    {
        switch($sName)
        {
        // Set the separator
        case 'separator':
            if($xValue == '_' || $xValue == '.')
            {
                $this->sSeparator = $xValue;
            }
            break;
        // Set the namespace
        case 'namespace':
            if(is_string($xValue))
            {
                $this->sNamespace = $xValue;
            }
            break;
        // Set the protected methods
        case 'protected':
            if(is_array($xValue))
            {
                $this->aProtectedMethods = array_merge($this->aProtectedMethods, $xValue);
            }
            elseif(is_string($xValue))
            {
                $this->aProtectedMethods[] = $xValue;
            }
            break;
        // Set the methods to call before processing the request
        case '__before':
            $this->setHookMethods($this->aBeforeMethods, $xValue);
            break;
        // Set the methods to call after processing the request
        case '__after':
            $this->setHookMethods($this->aAfterMethods, $xValue);
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
    public function getMethods(): array
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
     * Get the registered callable object
     *
     * @return null|object
     */
    public function getRegisteredObject()
    {
        return $this->di->get($this->xReflectionClass->getName());
    }

    /**
     * Check if the specified method name is one of the methods of the registered callable object
     *
     * @param string $sMethod    The name of the method to check
     *
     * @return bool
     */
    public function hasMethod(string $sMethod): bool
    {
        return $this->xReflectionClass->hasMethod($sMethod);
    }

    /**
     * Call the specified method of the registered callable object using the specified array of arguments
     *
     * @param array $aClassMethods    The methods config options
     * @param string $sMethod    The method called by the request
     *
     * @return void
     * @throws ReflectionException
     */
    private function callHookMethods(array $aClassMethods, string $sMethod)
    {
        $aMethods = [];
        if(isset($aClassMethods[$sMethod]))
        {
            $aMethods = $aClassMethods[$sMethod];
        }
        elseif(isset($aClassMethods['*']))
        {
            $aMethods = $aClassMethods['*'];
        }
        foreach($aMethods as $xKey => $xValue)
        {
            $sMethodName = $xValue;
            $aMethodArgs = [];
            if(is_string($xKey))
            {
                $sMethodName = $xKey;
                $aMethodArgs = is_array($xValue) ? $xValue : [$xValue];
            }
            if(!$this->xReflectionClass->hasMethod($sMethodName))
            {
                continue;
            }
            $reflectionMethod = $this->xReflectionClass->getMethod($sMethodName);
            $reflectionMethod->setAccessible(true); // Make it possible to call protected methods
            $reflectionMethod->invokeArgs($this->getRegisteredObject(), $aMethodArgs);
        }
    }

    /**
     * Call the specified method of the registered callable object using the specified array of arguments
     *
     * @param string $sMethod    The name of the method to call
     * @param array $aArgs    The arguments to pass to the method
     *
     * @return null|Response
     * @throws ReflectionException
     */
    public function call(string $sMethod, array $aArgs): ?Response
    {
        if(!$this->hasMethod($sMethod))
        {
            return null;
        }

        // Methods to call before processing the request
        $this->callHookMethods($this->aBeforeMethods, $sMethod);

        $reflectionMethod = $this->xReflectionClass->getMethod($sMethod);
        $xResponse = $reflectionMethod->invokeArgs($this->getRegisteredObject(), $aArgs);

        // Methods to call after processing the request
        $this->callHookMethods($this->aAfterMethods, $sMethod);

        return $xResponse;
    }
}
