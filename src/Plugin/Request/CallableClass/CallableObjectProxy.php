<?php

/**
 * CallableObjectProxy.php
 *
 * The proxy to a Jaxon callable object
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
use Jaxon\Exception\SetupException;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;

use function is_array;
use function is_string;
use function str_replace;

class CallableObjectProxy
{
    /**
     * @var CallableObject
     */
    private CallableObject $xAction;

    /**
     * The user registered component instance
     *
     * @var mixed|null
     */
    private $xComponent = null;

    /**
     * @param ComponentContainer $cdi
     * @param ReflectionClass $xReflectionClass
     * @param ComponentOptions $xOptions
     */
    public function __construct(private ComponentContainer $cdi,
        private ReflectionClass $xReflectionClass, private ComponentOptions $xOptions)
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
    public function getJsOptions(): array
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
     * @param string $sActionMethod    The method name
     *
     * @return void
     * @throws ReflectionException
     */
    private function callHookMethods(array $aHookMethods, string $sActionMethod): void
    {
        // The hooks defined at method level are merged with those defined at class level.
        $aMethods = [...($aHookMethods['*'] ?? []), ...($aHookMethods[$sActionMethod] ?? [])];
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
     * @return array
     */
    public function getClassOptions(): array
    {
        $aDiOptions = $this->xOptions->diOptions();
        return $aDiOptions['*'] ?? [];
    }

    /**
     * @return array
     */
    public function getMethodOptions(): array
    {
        $aDiOptions = $this->xOptions->diOptions();
        return $aDiOptions[$this->xAction->func()] ?? [];
    }

    /**
     * @return array<array|ReflectionParameter>
     */
    public function getArguments(): array
    {
        return [
            $this->xAction->args(),
            $this->xReflectionClass->getMethod($this->xAction->func())->getParameters(),
        ];
    }

    /**
     * Call the specified method of the component using the specified array of arguments
     *
     * @param CallableObject $xAction
     *
     * @return void
     * @throws ReflectionException
     * @throws SetupException
     */
    public function call(CallableObject $xAction): void
    {
        $this->xAction = $xAction;
        [$this->xComponent, $aArgs] = $this->cdi->getCallParams($this);

        $sMethod = $xAction->func();
        // Methods to call before processing the request
        $this->callHookMethods($this->xOptions->beforeMethods(), $sMethod);

        // Call the request method
        $this->callMethod($sMethod, $aArgs, false);

        // Methods to call after processing the request
        $this->callHookMethods($this->xOptions->afterMethods(), $sMethod);
    }
}
