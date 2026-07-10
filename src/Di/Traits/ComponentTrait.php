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

use Jaxon\App\ComponentDataTrait;
use Jaxon\App\Component\AbstractComponent;
use Jaxon\App\FuncComponent;
use Jaxon\App\I18n\Translator;
use Jaxon\App\Metadata\InputData;
use Jaxon\App\Metadata\Metadata;
use Jaxon\App\NodeComponent;
use Jaxon\App\PageComponent;
use Jaxon\App\RequestParam;
use Jaxon\Config\Config;
use Jaxon\Di\Container;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\Request\CallableComponent\ComponentProxy;
use Jaxon\Plugin\Request\CallableComponent\ComponentOptions;
use Jaxon\Plugin\Request\CallableComponent\ComponentRegistry;
use Jaxon\Request\Handler\CallbackManager;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use ReflectionNamedType;
use ReflectionParameter;

use function array_filter;
use function array_map;
use function array_slice;
use function call_user_func;
use function count;
use function in_array;
use function is_a;
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
    abstract protected function di(): Container;

    /**
     * @var int
     */
    private int $filter = ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED;

    /**
     * @param class-string $sClassName
     *
     * @return ComponentProxy|null
     * @throws SetupException
     */
    abstract public function getComponentProxy(string $sClassName): ComponentProxy|null;

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
    private function getComponentProxyKey(string $sClassName): string
    {
        return "{$sClassName}_ComponentProxy";
    }

    /**
     * @param class-string $sClassName The component name
     *
     * @return string
     */
    private function getComponentHelperKey(string $sClassName): string
    {
        return "{$sClassName}_ComponentHelper";
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
    private function getComponentFactoryKey(string $sClassName): string
    {
        return "{$sClassName}_ComponentFactory";
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
        $xRegistry = $this->di()->g(ComponentRegistry::class);
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

        throw new SetupException($this->di()->g(Translator::class)
            ->trans('errors.class.invalid', ['name' => $sClassName]));
    }

    /**
     * Get callable objects for known classes
     *
     * @return array<ComponentProxy>
     * @throws SetupException
     */
    public function getComponentProxies(): array
    {
        $aCallableObjects = [];
        foreach($this->aComponents as $sComponentId => $_)
        {
            $aCallableObjects[$sComponentId] = $this->getComponentProxy($sComponentId);
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

        $xDataTrait = new ReflectionClass(ComponentDataTrait::class);
        $aSharedMethods = $xDataTrait->getMethods(ReflectionMethod::IS_PUBLIC);
        $aSharedMethods = array_map(fn($method) => $method->getName(), $aSharedMethods);

        $xReflectionClass = new ReflectionClass($sClass);
        $aMethods = $xReflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);
        $this->aComponentPublicMethods[$sKey] = [
            array_map(fn($xMethod) => $xMethod->getName(), $aMethods),
            [...$aNeverExported, ...$aSharedMethods],
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
            $xReflectionClass->isSubclassOf(PageComponent::class) =>
                $this->aComponentPublicMethods['page'],
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
        $di = $this->di();
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

    /**
     * Convert the request arguments
     *
     * @param array $aArgs
     * @param array<ReflectionParameter> $aArgTypes
     *
     * @return array
     */
    public function convertArguments(array $aArgs, array $aArgTypes): array
    {
        // Ignore the extra argument types.
        $aArgTypes = array_slice($aArgTypes, 0, count($aArgs));
        return array_map(function($xArg, ReflectionParameter|null $xArgType) {
            if(!is_a($xArgType?->getType(), ReflectionNamedType::class))
            {
                return $xArg; // Parameter without a single type.
            }

            /** @var ReflectionNamedType */
            $xNamedType = $xArgType->getType();
            $sTypeName = $xNamedType->getName();
            if($xNamedType->isBuiltin() || !is_a($sTypeName, RequestParam::class, true))
            {
                return $xArg;
            }

            $xParam = $this->di->make($sTypeName);
            $xParam->set($xArg);
            return $xParam;
        }, $aArgs, $aArgTypes);
    }

    /**
     * @param mixed $xComponent
     * @param string $sClassName
     * @param array $aDiOptions
     *
     * @return void
     */
    private function injectAttributes($xComponent, string $sClassName, array $aDiOptions): void
    {
        // Set the protected attributes of the object
        $cSetter = function($sAttr, $xDiValue) {
            // $this here is related to the registered object instance.
            // Warning: dynamic properties will be deprecated in PHP8.2.
            $this->$sAttr = $xDiValue;
        };
        // Allow the setter to access private and protected attributes.
        $cSetter = $cSetter->bindTo($xComponent, $sClassName);

        foreach($aDiOptions as $sAttr => $sClass)
        {
            call_user_func($cSetter, $sAttr, $this->di->get($sClass));
        }
    }

    /**
     * @param string $sClassName
     * @param string $sProxyKey
     * @param string $sFactoryKey
     *
     * @return mixed
     */
    private function initComponent(string $sClassName, string $sProxyKey, string $sFactoryKey): mixed
    {
        $xComponent = $this->di()->g($sClassName);
        // Set attributes from the DI container.
        // The class level DI options are set on any component.
        // The method level DI options will be set only on the targetted component.
        /** @var ComponentProxy */
        $xComponentProxy = $this->get($sProxyKey);
        $this->injectAttributes($xComponent, $sClassName, $xComponentProxy->getClassOptions());

        if($xComponent instanceof AbstractComponent)
        {
            // Call the protected "initComponent()" method of the Component class.
            $cSetter = function($di, $xFactory) {
                // "$this" here refers to the AbstractComponent instance.
                $this->initComponent($di, $xFactory); 
            };
            $cSetter = $cSetter->bindTo($xComponent, $sClassName);
            $xFactory = $this->get($sFactoryKey);
            call_user_func($cSetter, $this->di, $xFactory);
        }

        // Run the callbacks for class initialisation
        $this->di()->g(CallbackManager::class)->onInit($xComponent);

        return $xComponent;
    }

    /**
     * @param ComponentProxy $xComponentProxy
     *
     * @return array
     */
    public function getCallParams(ComponentProxy $xComponentProxy): array
    {
        $sClassName = $xComponentProxy->getClassName();
        $xComponent = $this->get($sClassName);
        // Inject method-specific DI attributes.
        $aOptions = $xComponentProxy->getMethodOptions();
        $this->injectAttributes($xComponent, $sClassName, $aOptions);

        [$aArgs, $aArgTypes] = $xComponentProxy->getArguments();

        return [$xComponent, $this->convertArguments($aArgs, $aArgTypes)];
    }
}
