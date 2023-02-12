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

use Jaxon\App\CallableClass;
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
use function call_user_func;
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
     * Check if the js code for this object must be generated
     *
     * @var bool
     */
    private $bExcluded = false;

    /**
     * The user registered callable object
     *
     * @var object
     */
    private $xRegisteredObject = null;

    /**
     * The attributes to inject in the user registered callable object
     *
     * @var array
     */
    private $aAttributes = [];

    /**
     * The target of the Jaxon call
     *
     * @var Target
     */
    private $xTarget;

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
        return $this->bExcluded;
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
            break;
        // Set the methods to call before processing the request
        case '__before':
            $this->setHookMethods($this->aBeforeMethods, $xValue);
            break;
        // Set the methods to call after processing the request
        case '__after':
            $this->setHookMethods($this->aAfterMethods, $xValue);
            break;
        // Set the attributes to inject in the callable object
        case '__di':
            $this->aAttributes = array_merge($this->aAttributes, $xValue);
            break;
        case 'excluded':
            $this->bExcluded = (bool)$xValue;
            break;
        default:
            break;
        }
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
        }, $this->xReflectionClass->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED));
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
        }, $this->xReflectionClass->getMethods(ReflectionMethod::IS_PUBLIC));

        return array_filter($aMethods, function($sMethodName) use($aProtectedMethods) {
            // Don't take magic __call, __construct, __destruct methods
            // Don't take protected methods
            return substr($sMethodName, 0, 2) !== '__' &&
                !in_array($sMethodName, $aProtectedMethods) &&
                !in_array($sMethodName, $this->aProtectedMethods);
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
        $aArgs = $this->xTarget->getMethodArgs();
        // The hooks defined at method level override those defined at class level.
        // $aMethods = $aHookMethods[$sMethod] ?? $aHookMethods['*'] ?? [];
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
                // The name and arguments of the method can be passed to the hooks.
                /*$aHookArgs = array_map(function($xHookArg) use($sMethod, $aArgs) {
                    switch($xHookArg)
                    {
                    case '__method__':
                        return $sMethod;
                    case '__args__':
                        return $aArgs;
                    default:
                        return $xHookArg;
                    }
                }, $aHookArgs);*/
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
        $this->xRegisteredObject = $this->getRegisteredObject();
        $this->xTarget = $xTarget;
        $sMethod = $xTarget->getMethodName();

        // Set attributes from the DI container
        // The attributes defined at method level override those defined at class level.
        // $aAttributes = $this->aAttributes[$sMethod] ?? $this->aAttributes['*'] ?? [];
        // The attributes defined at method level are merged with those defined at class level.
        $aAttributes = array_merge($this->aAttributes['*'] ?? [], $this->aAttributes[$sMethod] ?? []);
        foreach($aAttributes as $sName => $sClass)
        {
            // Set the protected attributes of the object
            $cSetter = function($c) use($sName, $sClass) {
                // Warning: dynamic properties will be deprecated in PHP8.2.
                $this->$sName = $c->get($sClass);
            };
            // Can now access protected attributes
            call_user_func($cSetter->bindTo($this->xRegisteredObject, $this->xRegisteredObject), $this->di);
        }

        // Set the Jaxon request target in the helper
        if($this->xRegisteredObject instanceof CallableClass)
        {
            // Set the protected attributes of the object
            $cSetter = function() use($xTarget) {
                $this->xCallableClassHelper->xTarget = $xTarget;
            };
            // Can now access protected attributes
            call_user_func($cSetter->bindTo($this->xRegisteredObject, $this->xRegisteredObject));
        }

        // Methods to call before processing the request
        $this->callHookMethods($this->aBeforeMethods);

        // Call the request method
        $xResponse = $this->callMethod($sMethod, $this->xTarget->getMethodArgs(), false);

        // Methods to call after processing the request
        $this->callHookMethods($this->aAfterMethods);
        return $xResponse;
    }
}
