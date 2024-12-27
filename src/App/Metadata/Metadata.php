<?php

/**
 * Metadata.php
 *
 * Callable class metadata.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\App\Metadata;

class Metadata implements MetadataInterface
{
    /**
     * @param bool $bIsExcluded
     * @param array $aProperties
     * @param array $aProtectedMethods
     */
    public function __construct(private bool $bIsExcluded,
        private array $aProperties, private array $aProtectedMethods)
    {}

    /**
     * @inheritDoc
     */
    public function isExcluded(): bool
    {
        return $this->bIsExcluded;
    }

    /**
     * @inheritDoc
     */
    public function getProperties(): array
    {
        return $this->aProperties;
    }

    /**
     * @inheritDoc
     */
    public function getProtectedMethods(): array
    {
        return $this->aProtectedMethods;
    }
}
