<?php

/**
 * DiAutoTrait.php
 *
 * Di auto wiring.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2025 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Di\Traits;

use Jaxon\Di\Container;
use Jaxon\Exception\SetupException;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;

use function array_map;
use function is_string;

trait DiAutoTrait
{
    /**
     * The container for parameters
     *
     * @return Container
     */
    abstract protected function cn(): Container;

    /**
     * @param ReflectionClass $xClass
     * @param ReflectionParameter $xParameter
     *
     * @return mixed
     * @throws SetupException
     */
    private function getParameter(ReflectionClass $xClass, ReflectionParameter $xParameter)
    {
        $xType = $xParameter->getType();
        $sParameterName = '$' . $xParameter->getName();
        // Check the parameter class first.
        if($xType instanceof ReflectionNamedType)
        {
            $sParameterType = $xType->getName();
            // The class + the name
            if($this->cn()->has("$sParameterType $sParameterName"))
            {
                return $this->cn()->get("$sParameterType $sParameterName");
            }
            // The class only
            if($this->cn()->has($sParameterType))
            {
                return $this->cn()->get($sParameterType);
            }
        }
        // Check the name only
        return $this->cn()->get($sParameterName);
    }

    /**
     * Create an instance of a class, getting the constructor parameters from the DI container
     *
     * @param class-string|ReflectionClass $xClass The class name or the reflection class
     *
     * @return object|null
     * @throws ReflectionException
     * @throws SetupException
     */
    public function make(string|ReflectionClass $xClass): mixed
    {
        if(is_string($xClass))
        {
            // Create the reflection class instance
            $xClass = new ReflectionClass($xClass);
        }
        // Use the Reflection class to get the parameters of the constructor
        if(($constructor = $xClass->getConstructor()) === null)
        {
            return $xClass->newInstance();
        }

        $aParameters = array_map(fn($xParameter) =>
            $this->getParameter($xClass, $xParameter), $constructor->getParameters());
        return $xClass->newInstanceArgs($aParameters);
    }

    /**
     * Create an instance of a class by automatically fetching the dependencies in the constructor.
     *
     * @param class-string $sClass    The class name
     *
     * @return void
     */
    public function auto(string $sClass): void
    {
        $this->set($sClass, fn() => $this->make($sClass));
    }
}
