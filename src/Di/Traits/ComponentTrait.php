<?php

/**
 * ComponentTrait.php
 *
 * Functions for the component container.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Di\Traits;

use Jaxon\Di\Container;
use Jaxon\App\FuncComponent;
use Jaxon\App\NodeComponent;
use Jaxon\App\I18n\Translator;
use Jaxon\App\Metadata\InputData;
use Jaxon\App\Metadata\Metadata;
use Jaxon\Config\Config;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\Request\CallableClass\CallableObject;
use Jaxon\Plugin\Request\CallableClass\ComponentOptions;
use Jaxon\Plugin\Request\CallableClass\ComponentRegistry;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

use function array_filter;
use function array_map;
use function in_array;
use function str_replace;
use function substr;

trait ComponentTrait
{
    /**
     * The classes, both registered and found in registered directories.
     *
     * @var array
     */
    protected $aComponents = [];

    /**
     * The classes, both registered and found in registered directories.
     *
     * @var array
     */
    protected $aComponentPublicMethods = [];

    /**
     * The container for parameters
     *
     * @return Container
     */
    abstract protected function cn(): Container;

    /**
     * @var int
     */
    private int $filter = ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED;

    /**
     * @param class-string $sClassName
     *
     * @return CallableObject|null
     * @throws SetupException
     */
    abstract public function makeCallableObject(string $sClassName): ?CallableObject;

    /**
     * @param class-string $sClassName    The class name
     * @param array $aOptions    The class options
     *
     * @return void
     */
    abstract public function saveComponent(string $sClassName, array $aOptions): void;

    /**
     * @param class-string $sClassName The component name
     *
     * @return string
     */
    private function getCallableObjectKey(string $sClassName): string
    {
        return "{$sClassName}_CallableObject";
    }

    /**
     * @param class-string $sClassName The component name
     *
     * @return string
     */
    private function getCallableHelperKey(string $sClassName): string
    {
        return "{$sClassName}_CallableHelper";
    }

    /**
     * @param class-string $sClassName The component name
     *
     * @return string
     */
    private function getReflectionClassKey(string $sClassName): string
    {
        return "{$sClassName}_ReflectionClass";
    }

    /**
     * @param class-string $sClassName The component name
     *
     * @return string
     */
    private function getRequestFactoryKey(string $sClassName): string
    {
        return "{$sClassName}_RequestFactory";
    }

    /**
     * @param class-string $sClassName The component name
     *
     * @return string
     */
    private function getCallableFactoryKey(string $sClassName): string
    {
        return "{$sClassName}_CallableFactory";
    }

    /**
     * @param string $sClassName
     * @param array $aOptions
     *
     * @return void
     */
    private function _saveClassOptions(string $sClassName, array $aOptions): void
    {
        $sOptionsId = str_replace('\\', $aOptions['separator'], $sClassName);
        $this->aComponents[$sOptionsId] = $aOptions;
    }

    /**
     * @param string $sClassName
     *
     * @return array
     */
    private function _getClassOptions(string $sClassName): array
    {
        return $this->aComponents[str_replace('\\', '.', $sClassName)] ??
            $this->aComponents[str_replace('\\', '_', $sClassName)];
    }

    /**
     * Find a component amongst the registered namespaces and directories.
     *
     * @param class-string $sClassName The class name
     *
     * @return void
     * @throws SetupException
     */
    private function discoverComponent(string $sClassName): void
    {
        $xRegistry = $this->cn()->g(ComponentRegistry::class);
        $xRegistry->updateHash(false); // Disable hash calculation.

        $sComponentId = str_replace('\\', '.', $sClassName);
        if(!isset($this->aComponents[$sComponentId]))
        {
            $aOptions = $xRegistry->getNamespaceComponentOptions($sClassName);
            if($aOptions !== null)
            {
                $this->saveComponent($sClassName, $aOptions);
            }
        }
        if(isset($this->aComponents[$sComponentId]))
        {
            return; // The component is found.
        }

        // The component was not found in a registered namespace. We need to parse all
        // the directories to be able to find a component registered without a namespace.
        $sComponentId = str_replace('\\', '_', $sClassName);
        if(!isset($this->aComponents[$sComponentId]))
        {
            $xRegistry->registerComponentsInDirectories();
        }
        if(isset($this->aComponents[$sComponentId]))
        {
            return; // The component is found.
        }

        throw new SetupException($this->cn()->g(Translator::class)
            ->trans('errors.class.invalid', ['name' => $sClassName]));
    }

    /**
     * Get callable objects for known classes
     *
     * @return array
     * @throws SetupException
     */
    public function getCallableObjects(): array
    {
        $aCallableObjects = [];
        foreach($this->aComponents as $sComponentId => $_)
        {
            $aCallableObjects[$sComponentId] = $this->makeCallableObject($sComponentId);
        }
        return $aCallableObjects;
    }

    /**
     * @param string $sKey
     * @param string $sClass
     * @param array $aNeverExported
     *
     * @return void
     */
    private function setComponentPublicMethods(string $sKey, string $sClass,
        array $aNeverExported): void
    {
        if(isset($this->aComponentPublicMethods[$sKey]))
        {
            return;
        }

        $xReflectionClass = new ReflectionClass($sClass);
        $aMethods = $xReflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);
        $this->aComponentPublicMethods[$sKey] = [
            array_map(fn($xMethod) => $xMethod->getName(), $aMethods),
            $aNeverExported,
        ];
    }

    /**
     * Get the public methods of the callable object
     *
     * @param ReflectionClass $xReflectionClass
     *
     * @return array
     */
    private function getPublicMethods(ReflectionClass $xReflectionClass): array
    {
        $aMethods = array_map(fn($xMethod) => $xMethod->getShortName(),
            $xReflectionClass->getMethods(ReflectionMethod::IS_PUBLIC));
        // Don't take the magic __call, __construct, __destruct methods.
        $aMethods = array_filter($aMethods, fn($sMethodName) =>
            substr($sMethodName, 0, 2) !== '__');

        // Don't take the public methods of the Component base classes.
        // And also return the methods that must never be exported.
        $aBaseMethods = match(true) {
            $xReflectionClass->isSubclassOf(NodeComponent::class) =>
                $this->aComponentPublicMethods['node'],
            $xReflectionClass->isSubclassOf(FuncComponent::class) =>
                $this->aComponentPublicMethods['func'],
            default => [[], []],
        };

        return [$aMethods, ...$aBaseMethods];
    }

    /**
     * @param ReflectionClass $xReflectionClass
     * @param array $aMethods
     * @param array $aOptions
     *
     * @return Metadata|null
     */
    private function getComponentMetadata(ReflectionClass $xReflectionClass,
        array $aMethods, array $aOptions): Metadata|null
    {
        /** @var Config|null */
        $xPackageConfig = $aOptions['config'] ?? null;
        if($xPackageConfig === null || (bool)($aOptions['excluded'] ?? false))
        {
            return null;
        }
        $sMetadataFormat = $xPackageConfig->getOption('metadata.format');
        if(!in_array($sMetadataFormat, ['attributes', 'annotations']))
        {
            return null;
        }

        // Try to get the class metadata from the cache.
        $di = $this->cn();
        $xMetadata = null;
        $xMetadataCache = null;
        $xConfig = $di->config();
        if($xConfig->getAppOption('metadata.cache.enabled', false))
        {
            if(!$di->h('jaxon_metadata_cache_dir'))
            {
                $sCacheDir = $xConfig->getAppOption('metadata.cache.dir');
                $di->val('jaxon_metadata_cache_dir', $sCacheDir);
            }
            $xMetadataCache = $di->getMetadataCache();
            $xMetadata = $xMetadataCache->read($xReflectionClass->getName());
            if($xMetadata !== null)
            {
                return $xMetadata;
            }
        }

        $aProperties = array_map(fn($xProperty) => $xProperty->getName(),
            $xReflectionClass->getProperties($this->filter));

        $xMetadataReader = $di->getMetadataReader($sMetadataFormat);
        $xInput = new InputData($xReflectionClass, $aMethods, $aProperties);
        $xMetadata = $xMetadataReader->getAttributes($xInput);

        // Try to save the metadata in the cache
        if($xMetadataCache !== null)
        {
            $xMetadataCache->save($xReflectionClass->getName(), $xMetadata);
        }

        return $xMetadata;
    }

    /**
     * @param ReflectionClass $xReflectionClass
     * @param array $aOptions
     *
     * @return ComponentOptions
     */
    public function getComponentOptions(ReflectionClass $xReflectionClass,
        array $aOptions): ComponentOptions
    {
        $aMethods = $this->getPublicMethods($xReflectionClass);
        $xMetadata = $this->getComponentMetadata($xReflectionClass, $aMethods[0], $aOptions);

        return new ComponentOptions($aMethods, $aOptions, $xMetadata);
    }
}
