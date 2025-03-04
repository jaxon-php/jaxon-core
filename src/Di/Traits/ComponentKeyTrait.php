<?php

/**
 * ComponentKeyTrait.php
 *
 * Defines keys for component classes in the DI.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2025 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Di\Traits;

trait ComponentKeyTrait
{
    /**
     * @param class-string $sClassName The component name
     *
     * @return string
     */
    private function getCallableObjectKey(string $sClassName): string
    {
        return $sClassName . '_CallableObject';
    }

    /**
     * @param class-string $sClassName The component name
     *
     * @return string
     */
    private function getCallableHelperKey(string $sClassName): string
    {
        return $sClassName . '_CallableHelper';
    }

    /**
     * @param class-string $sClassName The component name
     *
     * @return string
     */
    private function getReflectionClassKey(string $sClassName): string
    {
        return $sClassName . '_ReflectionClass';
    }

    /**
     * @param class-string $sClassName The component name
     *
     * @return string
     */
    private function getRequestFactoryKey(string $sClassName): string
    {
        return $sClassName . '_RequestFactory';
    }
}
