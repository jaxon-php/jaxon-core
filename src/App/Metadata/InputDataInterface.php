<?php

/**
 * InputDataInterface.php
 *
 * Input data to query the metadata reader.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Metadata;

use ReflectionClass;

interface InputDataInterface
{
    /**
     * Get the reflection class
     *
     * @return ReflectionClass
     */
    public function getReflectionClass(): ReflectionClass;

    /**
     * The methods to check for metadata
     *
     * @return array
     */
    public function getMethods(): array;

    /**
     * The properties to check for metadata
     *
     * @return array
     */
    public function getProperties(): array;
}
