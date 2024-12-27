<?php

/**
 * MetadataReaderInterface.php
 *
 * Read callable class metadata.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Metadata;

use ReflectionClass;

interface MetadataReaderInterface
{
    /**
     * Get the callable class metadata
     *
     * @param ReflectionClass|string $xReflectionClass
     * @param array $aMethods
     * @param array $aProperties
     *
     * @return MetadataInterface|null
     */
    public function getAttributes(ReflectionClass|string $xReflectionClass,
        array $aMethods = [], array $aProperties = []): ?MetadataInterface;
}
