<?php

/**
 * MetadataInterface.php
 *
 * Interface for callable class attributes.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Metadata;

interface MetadataInterface
{
    /**
     * True if the class is excluded
     *
     * @return bool
     */
    public function isExcluded(): bool;

    /**
     * Get the properties of the class methods
     *
     * @return array
     */
    public function getProperties(): array;

    /**
     * Get the protected methods
     *
     * @return array
     */
    public function getProtectedMethods(): array;
}
