<?php

/**
 * InputData.php
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

use function is_string;

class InputData implements InputDataInterface
{
    /**
     * @var ReflectionClass
     */
    private $xReflectionClass;

    /**
     * @param ReflectionClass|string $xClass
     * @param array $aMethods
     * @param array $aProperties
     */
    public function __construct(ReflectionClass|string $xClass,
        private array $aMethods = [], private array $aProperties = [])
    {
        $this->xReflectionClass = is_string($xClass) ? new ReflectionClass($xClass) : $xClass;
    }

    /**
     * @inheritDoc
     */
    public function getReflectionClass(): ReflectionClass
    {
        return $this->xReflectionClass;
    }

    /**
     * @inheritDoc
     */
    public function getMethods(): array
    {
        return $this->aMethods;
    }

    /**
     * @inheritDoc
     */
    public function getProperties(): array
    {
        return $this->aProperties;
    }
}
