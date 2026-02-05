<?php

/**
 * CallableObject.php
 *
 * Jaxon callable object
 *
 * This class stores a reference to a component whose methods can be called from
 * the client via a Jaxon request
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

use Jaxon\Di\ComponentContainer;
use Jaxon\Di\Container;
use Jaxon\Exception\SetupException;
use Jaxon\Request\Target;
use Closure;
use ReflectionClass;
use ReflectionException;

use function array_merge;
use function call_user_func;
use function is_array;
use function is_string;
use function str_replace;

class CallableObject
{
    /**
     * The user registered component
     *
     * @var mixed
     */
    private $xComponent = null;

    /**
     * The target of the Jaxon call
     *
     * @var Target
     */
    private $xTarget;

    /**
     * The class constructor
     *
     * @param ComponentContainer $cdi
     * @param Container $di
     * @param ReflectionClass $xReflectionClass
     * @param ComponentOptions $xOptions
     */
    public function __construct(protected ComponentContainer $cdi,
        protected Container $di, private ReflectionClass $xReflectionClass,
        private ComponentOptions $xOptions)
    {}

    /**
     * @param string|null $sMethodName
     *
     * @return bool
     */
    public function excluded(string|null $sMethodName = null): bool
    {
        return $sMethodName === null ? $this->xOptions->excluded() :
            !$this->xOptions->isPublicMethod($sMethodName);
    }

    /**
     * Get the name of the registered PHP class
     *
     * @return class-string
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
     * Get the name of the javascript parameter in the ajax request
     *
     * @return string
     */
    public function getJsParam(): string
    {
        return str_replace('\\', '.', $this->getClassName());
    }

    /**
     * Get the js options of the component
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->xOptions->jsOptions();
    }

    /**
     * Return a list of methods of the component to export to javascript
     *
     * @return array
     */
    public function getCallableMethods(): array
    {
        return $this->xOptions->getCallableMethods();
    }

    /**
     * Check if the specified method name is one of the methods of the component
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
     * Call the specified method of the component using the specified array of arguments
     *
     * @param string $sMethod    The method name
     * @param array $aArgs    The method arguments
     * @param bool $bAccessible    If false, only calls to public method are allowed
     *
     * @return void
     * @throws ReflectionException
     */
    private function callMethod(string $sMethod, array $aArgs, bool $bAccessible): void
    {
        $reflectionMethod = $this->xReflectionClass->getMethod($sMethod);
        // Make it possible to call protected methods
        $reflectionMethod->setAccessible($bAccessible);
        $reflectionMethod->invokeArgs($this->xComponent, $aArgs);
    }

    /**
     * Call the specified method of the component using the specified array of arguments
     *
     * @param array $aHookMethods    The method config options
     *
     * @return void
     * @throws ReflectionException
     */
    private function callHookMethods(array $aHookMethods): void
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
     * @param mixed $xComponent
     * @param array $aDiOptions
     *
     * @return void
     */
    private function setDiAttributes($xComponent, array $aDiOptions): void
    {
        // Set the protected attributes of the object
        $cSetter = function($sAttr, $xDiValue) {
            // $this here is related to the registered object instance.
            // Warning: dynamic properties will be deprecated in PHP8.2.
            $this->$sAttr = $xDiValue;
        };
        foreach($aDiOptions as $sAttr => $sClass)
        {
            // Allow the setter to access protected attributes.
            $cSetter = $cSetter->bindTo($xComponent, $xComponent);
            call_user_func($cSetter, $sAttr, $this->di->get($sClass));
        }
    }

    /**
     * @param mixed $xComponent
     *
     * @return void
     */
    public function setDiClassAttributes($xComponent): void
    {
        $aDiOptions = $this->xOptions->diOptions();
        $this->setDiAttributes($xComponent, $aDiOptions['*'] ?? []);
    }

    /**
     * @param mixed $xComponent
     * @param string $sMethodName
     *
     * @return void
     */
    public function setDiMethodAttributes($xComponent, string $sMethodName): void
    {
        $aDiOptions = $this->xOptions->diOptions();
        $this->setDiAttributes($xComponent, $aDiOptions[$sMethodName] ?? []);
    }

    /**
     * Call the specified method of the component using the specified array of arguments
     *
     * @param Target $xTarget The target of the Jaxon call
     *
     * @return void
     * @throws ReflectionException
     * @throws SetupException
     */
    public function call(Target $xTarget): void
    {
        $this->xTarget = $xTarget;
        $this->xComponent = $this->cdi->getCalledComponent($this->getClassName(), $xTarget);

        // Methods to call before processing the request
        $this->callHookMethods($this->xOptions->beforeMethods());

        // Call the request method
        $this->callMethod($xTarget->getMethodName(), $xTarget->args(), false);

        // Methods to call after processing the request
        $this->callHookMethods($this->xOptions->afterMethods());
    }
}
