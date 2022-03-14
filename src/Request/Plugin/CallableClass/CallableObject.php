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

namespace Jaxon\Request\Plugin\CallableClass;

use Jaxon\Container\Container;
use Jaxon\Response\Response;

use ReflectionClass;
use ReflectionException;

use function array_map;
use function array_filter;
use function array_merge;
use function in_array;
use function is_array;
use function is_string;
use function substr;

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
     * Get the name of the corresponding javascript class
     *
     * @return string
     */
    public function getJsName(): string
    {
        return str_replace('\\', $this->sSeparator, $this->xReflectionClass->getName());
    }

    /**
     * Set hook methods
     *
     * @param array $aHookMethods    The array of hook methods
     * @param string|array $xValue    The value of the configuration option
     *
     * @return void
     */
    private function setHookMethods(array &$aHookMethods, $xValue)
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
            if($xValue === '_' || $xValue === '.')
            {
                $this->sSeparator = $xValue;
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
     * Return a list of methods of the callable object
     *
     * @param array $aProtectedMethods    The protected methods
     *
     * @return array
     */
    private function _getMethods(array $aProtectedMethods): array
    {
        $aMethods = array_map(function($xMethod) {
            return $xMethod->getShortName();
        }, $this->xReflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC));
        return array_filter($aMethods, function($sMethodName) use($aProtectedMethods) {
            // Don't take magic __call, __construct, __destruct methods
            // Don't take protected methods
            return !(substr($sMethodName, 0, 2) === '__' ||
                in_array($sMethodName, $aProtectedMethods) ||
                in_array($sMethodName, $this->aProtectedMethods));
        });
    }

    /**
     * Return a list of methods of the callable object to export to javascript
     *
     * @param array $aProtectedMethods    The protected methods
     *
     * @return array
     */
    public function getMethods(array $aProtectedMethods): array
    {
        // Convert an option to a string to be displayed in the js script template.
        $fConvertOption = function($xOption) {
            return is_array($xOption) ? json_encode($xOption) : $xOption;
        };
        $aCommonConfig = isset($this->aOptions['*']) ? array_map($fConvertOption, $this->aOptions['*']) : [];

        return array_map(function($sMethodName) use($fConvertOption, $aCommonConfig) {
            // Specific options for this method
            $aMethodConfig = isset($this->aOptions[$sMethodName]) ?
                array_map($fConvertOption, $this->aOptions[$sMethodName]) : [];
            return [
                'name' => $sMethodName,
                'config' => array_merge($aCommonConfig, $aMethodConfig),
            ];
        }, $this->_getMethods($aProtectedMethods));
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
     * @param string $sMethod    The method name
     * @param array $aArgs    The method arguments
     * @param bool $bAccessible    If false, only calls to public method are allowed
     *
     * @return mixed
     * @throws ReflectionException
     */
    private function callMethod(string $sMethod, array $aArgs, bool $bAccessible)
    {
        $reflectionMethod = $this->xReflectionClass->getMethod($sMethod);
        $reflectionMethod->setAccessible($bAccessible); // Make it possible to call protected methods
        return $reflectionMethod->invokeArgs($this->getRegisteredObject(), $aArgs);
    }

    /**
     * Call the specified method of the registered callable object using the specified array of arguments
     *
     * @param array $aClassMethods    The method config options
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
            $this->callMethod($sMethodName, $aMethodArgs, true);
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
        // Methods to call before processing the request
        $this->callHookMethods($this->aBeforeMethods, $sMethod);
        // Call the request method
        $xResponse = $this->callMethod($sMethod, $aArgs, false);
        // Methods to call after processing the request
        $this->callHookMethods($this->aAfterMethods, $sMethod);
        return $xResponse;
    }
}
