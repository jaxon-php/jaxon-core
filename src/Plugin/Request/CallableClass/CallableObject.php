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
use Jaxon\Di\ClassContainer;
use Jaxon\Di\Container;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\AnnotationReaderInterface;
use Jaxon\Request\Target;
use Jaxon\Response\AbstractResponse;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

use function array_fill_keys;
use function array_filter;
use function array_map;
use function array_merge;
use function call_user_func;
use function is_array;
use function is_string;
use function json_encode;
use function str_replace;
use function substr;

class CallableObject
{
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
     * @var CallableObjectOptions|null
     */
    private $xOptions = null;

    /**
     * @var int
     */
    private $nPropertiesFilter = ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED;

    /**
     * @var int
     */
    private $nMethodsFilter = ReflectionMethod::IS_PUBLIC;

    /**
     * @var array
     */
    private $aProtectedMethods;

    /**
     * The class constructor
     *
     * @param ClassContainer $cls
     * @param Container $di
     * @param ReflectionClass $xReflectionClass
     * @param AnnotationReaderInterface $xAnnotationReader
     * @param array $aOptions
     * @param array $aProtectedMethods
     */
    public function __construct(protected ClassContainer $cls, protected Container $di,
        private ReflectionClass $xReflectionClass, AnnotationReaderInterface $xAnnotationReader,
        array $aOptions, array $aProtectedMethods)
    {
        $this->aProtectedMethods = array_fill_keys($aProtectedMethods, true);

        $aAnnotations = $xAnnotationReader->getAttributes($xReflectionClass,
            $this->getPublicMethods(true), $this->getProperties());
        $this->xOptions = new CallableObjectOptions($aOptions, $aAnnotations);
    }

    /**
     * Get the public and protected attributes of the callable object
     *
     * @return array
     */
    private function getProperties(): array
    {
        return array_map(function($xProperty) {
            return $xProperty->getName();
        }, $this->xReflectionClass->getProperties($this->nPropertiesFilter));
    }

    /**
     * @param string $sMethodName
     * @param bool $bTakeAll
     *
     * @return bool
     */
    private function isProtectedMethod(string $sMethodName, bool $bTakeAll): bool
    {
        // Don't take magic __call, __construct, __destruct methods
        // Don't take protected methods
        return substr($sMethodName, 0, 2) === '__' ||
            isset($this->aProtectedMethods[$sMethodName]) ||
            (!$bTakeAll && $this->xOptions !== null &&
            $this->xOptions->isProtectedMethod($sMethodName));
    }

    /**
     * Get the public methods of the callable object
     *
     * @param bool $bTakeAll
     *
     * @return array
     */
    public function getPublicMethods(bool $bTakeAll): array
    {
        $aMethods = array_map(function($xMethod) {
            return $xMethod->getShortName();
        }, $this->xReflectionClass->getMethods($this->nMethodsFilter));

        return array_filter($aMethods, function($sMethodName) use($bTakeAll) {
            return !$this->isProtectedMethod($sMethodName, $bTakeAll);
        });
    }

    /**
     * @return bool
     */
    public function excluded(): bool
    {
        return $this->xReflectionClass->isAbstract() || $this->xOptions->excluded();
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
     * Get the js options of the callable class
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->xOptions->jsOptions();
    }

    /**
     * Return a list of methods of the callable object to export to javascript
     *
     * @return array
     */
    public function getCallableMethods(): array
    {
        // Get the method options, and convert each of them to
        // a string to be displayed in the js script template.
        $fGetOption = function($sMethodName) {
            return array_map(function($xOption) {
                return is_array($xOption) ? json_encode($xOption) : $xOption;
            }, $this->xOptions->getMethodOptions($sMethodName));
        };

        return array_map(function($sMethodName) use($fGetOption) {
            return [
                'name' => $sMethodName,
                'config' => $fGetOption($sMethodName),
            ];
        }, $this->getPublicMethods(false));
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
     * @param mixed $xRegisteredObject
     * @param array $aDiOptions
     *
     * @return void
     */
    private function setDiAttributes($xRegisteredObject, array $aDiOptions)
    {
        // Set the protected attributes of the object
        $cSetter = function($sName, $xInjectedObject) {
            // Warning: dynamic properties will be deprecated in PHP8.2.
            $this->$sName = $xInjectedObject;
        };
        foreach($aDiOptions as $sName => $sClass)
        {
            // The setter has access to protected attributes
            call_user_func($cSetter->bindTo($xRegisteredObject, $xRegisteredObject),
                $sName, $this->di->get($sClass));
        }
    }

    /**
     * @param mixed $xRegisteredObject
     *
     * @return void
     */
    public function setDiClassAttributes($xRegisteredObject)
    {
        $aDiOptions = $this->xOptions->diOptions();
        $this->setDiAttributes($xRegisteredObject, $aDiOptions['*'] ?? []);
    }

    /**
     * @param mixed $xRegisteredObject
     * @param string $sMethodName
     *
     * @return void
     */
    private function setDiMethodAttributes($xRegisteredObject, string $sMethodName)
    {
        $aDiOptions = $this->xOptions->diOptions();
        $this->setDiAttributes($xRegisteredObject, $aDiOptions[$sMethodName] ?? []);
    }

    /**
     * Get a callable object when one of its method needs to be called
     *
     * @param Target|null $xTarget
     *
     * @return mixed
     */
    public function getRegisteredObject(?Target $xTarget = null)
    {
        $xRegisteredObject = $this->cls->get($this->getClassName());
        if(!$xRegisteredObject || !$xTarget)
        {
            return $xRegisteredObject;
        }

        // Set attributes from the DI container.
        // The class level DI options were set when creating the object instance.
        // We now need to set the method level DI options.
        $this->setDiMethodAttributes($xRegisteredObject, $xTarget->getMethodName());
        // Set the Jaxon request target in the helper
        if($xRegisteredObject instanceof CallableClass)
        {
            // Set the protected attributes of the object
            $sAttr = 'xCallableClassHelper';
            $cSetter = function() use($xTarget, $sAttr) {
                $this->$sAttr->xTarget = $xTarget;
            };
            // Can now access protected attributes
            call_user_func($cSetter->bindTo($xRegisteredObject, $xRegisteredObject));
        }
        return $xRegisteredObject;
    }

    /**
     * Call the specified method of the registered callable object using the specified array of arguments
     *
     * @param Target $xTarget The target of the Jaxon call
     *
     * @return null|AbstractResponse
     * @throws ReflectionException
     * @throws SetupException
     */
    public function call(Target $xTarget): ?AbstractResponse
    {
        $this->xTarget = $xTarget;
        $this->xRegisteredObject = $this->getRegisteredObject($xTarget);

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
