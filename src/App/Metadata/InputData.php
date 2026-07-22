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

use Jaxon\App;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;

use function is_string;

class InputData
{
    /**
     * @var ReflectionClass
     */
    private $xReflectionClass;

    /**
     * @var array
     */
    private static $aJaxonClasses = [
        App\Component::class => true,
        App\NodeComponent::class => true,
        App\FuncComponent::class => true,
        App\PageComponent::class => true,
        App\CallableClass::class => true,
    ];

    /**
     * @param ReflectionClass|string $xClass
     * @param array $aMethods
     * @param array $aProperties
     */
    public function __construct(ReflectionClass|string $xClass,
        private array $aMethods = [], private array $aProperties = [])
    {
        $this->xReflectionClass = is_string($xClass) ?
            new ReflectionClass($xClass) : $xClass;
    }

    /**
     * Get the reflection class
     *
     * @return ReflectionClass
     */
    public function getReflectionClass(): ReflectionClass
    {
        return $this->xReflectionClass;
    }

    /**
     * The methods to check for metadata
     *
     * @return array
     */
    public function getMethods(): array
    {
        return $this->aMethods;
    }

    /**
     * The properties to check for metadata
     *
     * @param string $sClassName
     *
     * @return array
     */
    public function getProperties(string $sClassName): array
    {
        return $this->aProperties[$sClassName] ?? [];
    }

    /**
     * @param ReflectionClass $xClass
     *
     * @return bool
     */
    public static function isJaxonClass(ReflectionClass $xClass): bool
    {
        return isset(self::$aJaxonClasses[$xClass->getName()]);
    }

    /**
     * @param ReflectionClass $xClass
     *
     * @return ReflectionClass|null
     */
    public static function getParentClass(ReflectionClass $xClass): ReflectionClass|null
    {
        $xParent = $xClass->getParentClass();
        return $xParent === false || self::isJaxonClass($xParent) ? null : $xParent;
    }

    /**
     * @param ReflectionProperty|ReflectionParameter|null $xProperty
     *
     * @return bool
     */
    public static function isInjectable(ReflectionProperty|ReflectionParameter|null $xProperty): bool
    {
        $xType = $xProperty?->getType();
        return is_a($xType, ReflectionNamedType::class) && !$xType->isBuiltin();
    }
}
