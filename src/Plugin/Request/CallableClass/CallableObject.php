<?php

/**
 * CallableObject.php
 *
 * Jaxon callable object
 *
 * This class stores a reference to an object whose methods can be called from
 * the client via a Jaxon request
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

namespace Jaxon\Plugin\Request\CallableClass;

use Jaxon\Di\Container;
use Jaxon\Exception\SetupException;
use Jaxon\Request\Target;
use Jaxon\Response\ResponseInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

use function array_filter;
use function array_map;
use function array_merge;
use function array_unique;
use function in_array;
use function is_array;
use function is_string;
use function json_encode;
use function str_replace;
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
     * The callable object options
     *
     * @var array
     */
    private $aOptions = [];

    /**
     * The user registered callable object
     *
     * @var mixed
     */
    private $xRegisteredObject = null;

    /**
     * The target of the Jaxon call
     *
     * @var Target
     */
    private $xTarget;

    /**
     * The options of this callable object
     *
     * @var CallableObjectOptions
     */
    private $xOptions;

    /**
     * @var int
     */
    private $nPropertiesFilter = ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED;

    /**
     * @var int
     */
    private $nMethodsFilter = ReflectionMethod::IS_PUBLIC;

    /**
     * The class constructor
     *
     * @param Container  $di
     * @param ReflectionClass $xReflectionClass    The reflection class
     */
    public function __construct(Container $di, ReflectionClass $xReflectionClass)
    {
        $this->di = $di;
        $this->xReflectionClass = $xReflectionClass;
        $this->xOptions = new CallableObjectOptions();
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
     * Check if the js code for this object must be generated
     *
     * @return bool
     */
    public function excluded(): bool
    {
        return $this->xOptions->excluded();
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
     * Get the name of the registered PHP class
     *
     * @return string
     */
    public function getClassName(): string
    {
        return $this->xReflectionClass->getName();
    }

    /**
     * Get the name of the corresponding javascript class
     *
     * @return string
     */
    public function getJsName(): string
    {
        return str_replace('\\', $this->xOptions->separator(), $this->getClassName());
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
        $this->xOptions->addValue($sName, $xValue);
    }

    /**
     * @return array
     */
    public function getClassDiOptions(): array
    {
        $aDiOptions = $this->xOptions->diOptions();
        return $aDiOptions['*'] ?? [];
    }

    /**
     * @param string $sMethodName
     *
     * @return array
     */
    public function getMethodDiOptions(string $sMethodName): array
    {
        $aDiOptions = $this->xOptions->diOptions();
        return $aDiOptions[$sMethodName] ?? [];
    }

    /**
     * Get the public and protected attributes of the callable object
     *
     * @return array
     */
    public function getProperties(): array
    {
        return array_map(function($xProperty) {
            return $xProperty->getName();
        }, $this->xReflectionClass->getProperties($this->nPropertiesFilter));
    }

    /**
     * Get the public methods of the callable object
     *
     * @param array $aProtectedMethods    The protected methods
     *
     * @return array
     */
    public function getPublicMethods(array $aProtectedMethods = []): array
    {
        $aMethods = array_map(function($xMethod) {
            return $xMethod->getShortName();
        }, $this->xReflectionClass->getMethods($this->nMethodsFilter));

        return array_filter($aMethods, function($sMethodName) use($aProtectedMethods) {
            // Don't take magic __call, __construct, __destruct methods
            // Don't take protected methods
            return substr($sMethodName, 0, 2) !== '__' &&
                !in_array($sMethodName, $aProtectedMethods) &&
                !in_array($sMethodName, $this->xOptions->protectedMethods());
        });
    }

    /**
     * @param array $aCommonOptions
     * @param array $aMethodOptions
     * @param string $sMethodName
     *
     * @return mixed
     */
    private function getOptionValue(array $aCommonOptions, array $aMethodOptions, string $sOptionName)
    {
        if(!isset($aCommonOptions[$sOptionName]))
        {
            return $aMethodOptions[$sOptionName];
        }
        if(!isset($aMethodOptions[$sOptionName]))
        {
            return $aCommonOptions[$sOptionName];
        }
        // If both are not arrays, return the latest.
        if(!is_array($aCommonOptions[$sOptionName]) && !is_array($aMethodOptions[$sOptionName]))
        {
            return $aMethodOptions[$sOptionName];
        }

        // Merge the options.
        $_aCommonOptions = is_array($aCommonOptions[$sOptionName]) ?
            $aCommonOptions[$sOptionName] : [$aCommonOptions[$sOptionName]];
        $_aMethodOptions = is_array($aMethodOptions[$sOptionName]) ?
            $aMethodOptions[$sOptionName] : [$aMethodOptions[$sOptionName]];
        return array_merge($_aCommonOptions, $_aMethodOptions);
    }

    /**
     * @param string $sMethodName
     *
     * @return array
     */
    private function getMethodOptions(string $sMethodName): array
    {
        $aCommonOptions = isset($this->aOptions['*']) ? $this->aOptions['*'] : [];
        $aMethodOptions = isset($this->aOptions[$sMethodName]) ? $this->aOptions[$sMethodName] : [];
        $aOptionNames = array_unique(array_merge(array_keys($aCommonOptions), array_keys($aMethodOptions)));
        $aOptions = [];
        foreach($aOptionNames as $sOptionName)
        {
            $aOptions[$sOptionName] = $this->getOptionValue($aCommonOptions, $aMethodOptions, $sOptionName);
        }
        return $aOptions;
    }

    /**
     * Return a list of methods of the callable object to export to javascript
     *
     * @param array $aProtectedMethods    The protected methods
     *
     * @return array
     */
    public function getCallableMethods(array $aProtectedMethods): array
    {
        // Convert an option to a string to be displayed in the js script template.
        $fConvertOption = function($xOption) {
            return is_array($xOption) ? json_encode($xOption) : $xOption;
        };

        return array_map(function($sMethodName) use($fConvertOption) {
            return [
                'name' => $sMethodName,
                'config' => array_map($fConvertOption, $this->getMethodOptions($sMethodName)),
            ];
        }, $this->getPublicMethods($aProtectedMethods));
    }

    /**
     * Get the registered callable object
     *
     * @return null|object
     */
    public function getRegisteredObject()
    {
        return $this->di->g($this->xReflectionClass->getName());
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
        return $reflectionMethod->invokeArgs($this->xRegisteredObject, $aArgs);
    }

    /**
     * Call the specified method of the registered callable object using the specified array of arguments
     *
     * @param array $aHookMethods    The method config options
     *
     * @return void
     * @throws ReflectionException
     */
    private function callHookMethods(array $aHookMethods)
    {
        $sMethod = $this->xTarget->getMethodName();
        // The hooks defined at method level are merged with those defined at class level.
        $aMethods = array_merge($aHookMethods['*'] ?? [], $aHookMethods[$sMethod] ?? []);
        foreach($aMethods as $xKey => $xValue)
        {
            $sHookName = $xValue;
            $aHookArgs = [];
            if(is_string($xKey))
            {
                $sHookName = $xKey;
                $aHookArgs = is_array($xValue) ? $xValue : [$xValue];
            }
            $this->callMethod($sHookName, $aHookArgs, true);
        }
    }

    /**
     * Call the specified method of the registered callable object using the specified array of arguments
     *
     * @param Target $xTarget The target of the Jaxon call
     *
     * @return null|ResponseInterface
     * @throws ReflectionException
     * @throws SetupException
     */
    public function call(Target $xTarget): ?ResponseInterface
    {
        $this->xTarget = $xTarget;
        $this->xRegisteredObject = $this->di->getRegisteredObject($this, $xTarget);

        // Methods to call before processing the request
        $this->callHookMethods($this->xOptions->beforeMethods());

        // Call the request method
        $sMethod = $xTarget->getMethodName();
        $xResponse = $this->callMethod($sMethod, $this->xTarget->getMethodArgs(), false);

        // Methods to call after processing the request
        $this->callHookMethods($this->xOptions->afterMethods());
        return $xResponse;
    }
}
