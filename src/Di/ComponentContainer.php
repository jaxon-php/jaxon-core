<?php

/**
 * ComponentContainer.php
 *
 * Jaxon DI container. Provide container service for the registered classes.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2024 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Di;

use Jaxon\App\Component\AbstractComponent;
use Jaxon\App\Component\Pagination;
use Jaxon\App\Config\ConfigManager;
use Jaxon\App\Dialog\Manager\DialogCommand;
use Jaxon\App\I18n\Translator;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\Request\CallableClass\CallableObject;
use Jaxon\Plugin\Request\CallableClass\ComponentHelper;
use Jaxon\Plugin\Request\CallableClass\ComponentRegistry;
use Jaxon\Request\Handler\CallbackManager;
use Jaxon\Request\Target;
use Jaxon\Script\JxnCall;
use Jaxon\Script\JxnClass;
use Pimple\Container as PimpleContainer;
use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;

use function array_map;
use function str_replace;
use function trim;

class ComponentContainer
{
    /**
     * If the underscore is used as separator in js class names.
     *
     * @var bool
     */
    private $bUsingUnderscore = false;

    /**
     * The Dependency Injection Container for registered classes
     *
     * @var PimpleContainer
     */
    private $xContainer;

    /**
     * The classes
     *
     * These are all the classes, both registered and found in registered directories.
     *
     * @var array
     */
    protected $aComponents = [];

    /**
     * This will be set only when getting the object targetted by the ajax request.
     *
     * @var Target
     */
    private $xTarget = null;

    /**
     * The class constructor
     *
     * @param Container $di
     * @param Translator $xTranslator
     */
    public function __construct(private Container $di, private Translator $xTranslator)
    {
        $this->xContainer = new PimpleContainer();
        $this->val(ComponentContainer::class, $this);

        // Register the call factory for registered functions
        $this->set($this->getRequestFactoryKey(JxnCall::class), function() {
            return new JxnCall($this->di->g(DialogCommand::class),
                $this->di->g(ConfigManager::class)->getOption('core.prefix.function', ''));
        });
    }

    /**
     * @return void
     */
    public function useUnderscore()
    {
        $this->bUsingUnderscore = true;
    }

    /**
     * Check if a class is defined in the container
     *
     * @param class-string $sClass    The full class name
     *
     * @return bool
     */
    public function has(string $sClass): bool
    {
        return $this->xContainer->offsetExists($sClass);
    }

    /**
     * Get a class instance
     *
     * @template T
     * @param class-string<T> $sClass The full class name
     *
     * @return T
     */
    public function get(string $sClass)
    {
        return $this->xContainer->offsetGet($sClass);
    }

    /**
     * Save a closure in the container
     *
     * @param class-string $sClass    The full class name
     * @param Closure $xClosure    The closure
     *
     * @return void
     */
    public function set(string $sClass, Closure $xClosure)
    {
       $this->xContainer->offsetSet($sClass, function() use($xClosure) {
            return $xClosure($this);
        });
    }

    /**
     * Save a value in the container
     *
     * @param string|class-string $sKey    The key
     * @param mixed $xValue    The value
     *
     * @return void
     */
    public function val(string $sKey, $xValue)
    {
       $this->xContainer->offsetSet($sKey, $xValue);
    }

    /**
     *
     * @param class-string $sClassName    The class name
     * @param array $aOptions    The class options
     *
     * @return void
     */
    public function registerComponent(string $sClassName, array $aOptions = [])
    {
        try
        {
            // Make sure the registered class exists
            isset($aOptions['include']) && require_once($aOptions['include']);
            $xReflectionClass = new ReflectionClass($sClassName);
            // Check if the class is registrable
            if($xReflectionClass->isInstantiable() &&
                !$xReflectionClass->isSubclassOf(Pagination::class))
            {
                $this->aComponents[$sClassName] = $aOptions;
                $this->val($this->getReflectionClassKey($sClassName), $xReflectionClass);
            }
        }
        catch(ReflectionException $e)
        {
            throw new SetupException($this->xTranslator->trans('errors.class.invalid',
                ['name' => $sClassName]));
        }
    }

    /**
     * Find the options associated with a registered class name
     *
     * @param class-string $sClassName The class name
     *
     * @return void
     * @throws SetupException
     */
    private function registerComponentOptions(string $sClassName)
    {
        if(!isset($this->aComponents[$sClassName]))
        {
            // Find options for a class registered with namespace.
            /** @var ComponentRegistry */
            $xRegistry = $this->di->g(ComponentRegistry::class);
            $xRegistry->registerClassFromNamespace($sClassName);
            if(!isset($this->aComponents[$sClassName]))
            {
                // Find options for a class registered without namespace.
                // We need to parse all the classes to be able to find one.
                $xRegistry->parseDirectories();
            }
        }
        if(!isset($this->aComponents[$sClassName]))
        {
            throw new SetupException($this->xTranslator->trans('errors.class.invalid',
                ['name' => $sClassName]));
        }
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
        foreach($this->aComponents as $sClassName => $_)
        {
            $this->_registerComponent($sClassName);
            $aCallableObjects[$sClassName] = $this->getCallableObject($sClassName);
        }
        return $aCallableObjects;
    }

    /**
     * @param ReflectionClass $xClass
     * @param ReflectionParameter $xParameter
     *
     * @return mixed
     * @throws SetupException
     */
    protected function getParameter(ReflectionClass $xClass, ReflectionParameter $xParameter)
    {
        $xType = $xParameter->getType();
        // Check the parameter class first.
        if($xType instanceof ReflectionNamedType)
        {
            // Check the class + the name
            if($this->di->has($xType->getName() . ' $' . $xParameter->getName()))
            {
                return $this->di->get($xType->getName() . ' $' . $xParameter->getName());
            }
            // Check the class only
            if($this->di->has($xType->getName()))
            {
                return $this->di->get($xType->getName());
            }
        }
        // Check the name only
        return $this->di->get('$' . $xParameter->getName());
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
    public function make($xClass): mixed
    {
        if(is_string($xClass))
        {
            $xClass = new ReflectionClass($xClass); // Create the reflection class instance
        }
        if(!($xClass instanceof ReflectionClass))
        {
            return null;
        }
        // Use the Reflection class to get the parameters of the constructor
        if(($constructor = $xClass->getConstructor()) === null)
        {
            return $xClass->newInstance();
        }
        $aParameterInstances = array_map(function($xParameter) use($xClass) {
            return $this->getParameter($xClass, $xParameter);
        }, $constructor->getParameters());

        return $xClass->newInstanceArgs($aParameterInstances);
    }

    /**
     * Create an instance of a class by automatically fetching the dependencies in the constructor.
     *
     * @param class-string $sClass    The class name
     *
     * @return void
     */
    public function auto(string $sClass)
    {
        $this->set($sClass, function() use ($sClass) {
            return $this->make($sClass);
        });
    }

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
     * Register a component
     *
     * @param class-string $sClassName The component name
     *
     * @return void
     * @throws SetupException
     */
    private function _registerComponent(string $sClassName)
    {
        $sComponentObject = $this->getCallableObjectKey($sClassName);
        // Prevent duplication. It's important not to use the class name here.
        if($this->has($sComponentObject))
        {
            return;
        }

        // Register the helper class
        $this->set($this->getCallableHelperKey($sClassName), function() use($sClassName) {
            $xFactory = $this->di->getCallFactory();
            return new ComponentHelper($this, $xFactory->rq($sClassName),
                $xFactory, $this->di->getViewRenderer(),
                $this->di->getLogger(), $this->di->getSessionManager(),
                $this->di->getStash(), $this->di->getUploadHandler());
        });

        $this->registerComponentOptions($sClassName);
        $aOptions = $this->aComponents[$sClassName];

        // Register the callable object
        $this->set($sComponentObject, function() use($sClassName, $aOptions) {
            $xReflectionClass = $this->get($this->getReflectionClassKey($sClassName));
            return new CallableObject($this, $this->di, $xReflectionClass, $aOptions);
        });

        // Register the user class, but only if the user didn't already.
        if(!$this->has($sClassName))
        {
            $this->set($sClassName, function() use($sClassName) {
                return $this->make($this->get($this->getReflectionClassKey($sClassName)));
            });
        }
        // Initialize the user class instance
        $this->xContainer->extend($sClassName, function($xClassInstance) use($sClassName) {
            if($xClassInstance instanceof AbstractComponent)
            {
                $xHelper = $this->get($this->getCallableHelperKey($sClassName));
                $xHelper->xTarget = $this->xTarget;
                $xClassInstance->_initComponent($this->di, $xHelper);
            }

            // Run the callbacks for class initialisation
            $this->di->g(CallbackManager::class)->onInit($xClassInstance);

            // Set attributes from the DI container.
            // The class level DI options are set on any component.
            // The method level DI options are set only on the targetted component.
            /** @var CallableObject */
            $xCallableObject = $this->get($this->getCallableObjectKey($sClassName));
            $xCallableObject->setDiClassAttributes($xClassInstance);
            if($this->xTarget !== null)
            {
                $sMethodName = $this->xTarget->getMethodName();
                $xCallableObject->setDiMethodAttributes($xClassInstance, $sMethodName);
            }

            return $xClassInstance;
        });
    }

    /**
     * Get a component when one of its method needs to be called
     *
     * @template T
     * @param class-string<T> $sClassName the class name
     * @param Target $xTarget
     *
     * @return T|null
     */
    public function getComponent(string $sClassName, Target $xTarget): mixed
    {
        // Set the target only when getting the object targetted by the ajax request.
        $this->xTarget = $xTarget;
        $xComponent = $this->get($sClassName);
        $this->xTarget = null;

        return $xComponent;
    }

    /**
     * Get the callable object for a given class
     *
     * @param class-string $sClassName
     *
     * @return CallableObject
     */
    public function getCallableObject(string $sClassName): CallableObject
    {
        return $this->get($sClassName . '_CallableObject');
    }

    /**
     * Check if a callable object is already in the DI, and register if not
     *
     * @param class-string $sClassName The class name of the callable object
     *
     * @return class-string
     * @throws SetupException
     */
    private function checkCallableObject(string $sClassName): string
    {
        // Replace all separators ('.' and '_') with antislashes, and remove the antislashes
        // at the beginning and the end of the class name.
        $sClassName = trim(str_replace('.', '\\', $sClassName), '\\');
        if($this->bUsingUnderscore)
        {
            $sClassName = trim(str_replace('_', '\\', $sClassName), '\\');
        }

        // Register the class.
        $this->_registerComponent($sClassName);
        return $sClassName;
    }

    /**
     * Get the callable object for a given class
     * The callable object is registered if it is not already in the DI.
     *
     * @param class-string $sClassName The class name of the callable object
     *
     * @return CallableObject|null
     * @throws SetupException
     */
    public function makeCallableObject(string $sClassName): ?CallableObject
    {
        return $this->getCallableObject($this->checkCallableObject($sClassName));
    }

    /**
     * Get an instance of a component by name
     *
     * @template T
     * @param class-string<T> $sClassName the class name
     *
     * @return T|null
     * @throws SetupException
     */
    public function makeComponent(string $sClassName): mixed
    {
        return $this->get($this->checkCallableObject($sClassName));
    }

    /**
     * Get the component registry
     *
     * @return ComponentRegistry
     */
    public function getComponentRegistry(): ComponentRegistry
    {
        return $this->di->g(ComponentRegistry::class);
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

    /**
     * @param class-string $sClassName
     * @param string $sFactoryKey
     *
     * @return void
     */
    private function registerRequestFactory(string $sClassName, string $sFactoryKey)
    {
        $this->xContainer->offsetSet($sFactoryKey, function() use($sClassName) {
            if(!($xCallable = $this->makeCallableObject($sClassName)))
            {
                return null;
            }
            $xConfigManager = $this->di->g(ConfigManager::class);
            $sJsObject = $xConfigManager->getOption('core.prefix.class', '') . $xCallable->getJsName();
            return new JxnClass($this->di->g(DialogCommand::class), $sJsObject);
        });
    }

    /**
     * Get a factory for a js function call.
     *
     * @param class-string $sClassName
     *
     * @return JxnCall|null
     */
    public function getRequestFactory(string $sClassName = ''): ?JxnCall
    {
        // If a class name is provided, get the factory for a registered class.
        // Otherwise, get the unique factory for registered functions.
        $sClassName = trim($sClassName, " \t") ?: JxnCall::class;
        $sFactoryKey = $this->getRequestFactoryKey($sClassName);
        if(!$this->has($sFactoryKey))
        {
            $this->registerRequestFactory($sClassName, $sFactoryKey);
        }
        return $this->get($sFactoryKey);
    }
}
