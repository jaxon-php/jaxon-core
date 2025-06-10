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
use Jaxon\Script\JxnClassCall;
use Pimple\Container as PimpleContainer;
use Closure;
use ReflectionClass;
use ReflectionException;

use function call_user_func;
use function str_replace;
use function trim;

class ComponentContainer
{
    use Traits\ComponentKeyTrait;
    use Traits\DiAutoTrait;

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

        // Register the pagination component, but do not export to js.
        $this->registerComponent(Pagination::class, [
            'excluded' => true,
            'namespace' => 'Jaxon\App\Component',
        ]);
    }

    /**
     * The container for parameters
     *
     * @return Container
     */
    protected function cn(): Container
    {
        return $this->di;
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
     * Get a component when one of its method needs to be called
     *
     * @template T
     * @param class-string<T> $sClassName the class name
     * @param Target $xTarget
     *
     * @return T|null
     */
    public function getTargetComponent(string $sClassName, Target $xTarget): mixed
    {
        // Set the target only when getting the object targetted by the ajax request.
        $this->xTarget = $xTarget;
        $xComponent = $this->get($sClassName);
        $this->xTarget = null;

        return $xComponent;
    }

    /**
     * Save a component options
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
            if(isset($aOptions['include']))
            {
                require_once $aOptions['include'];
            }
            $xReflectionClass = new ReflectionClass($sClassName);
            // Check if the class is registrable
            if(!$xReflectionClass->isInstantiable())
            {
                return;
            }

            $this->aComponents[$sClassName] = $aOptions;
            $this->val($this->getReflectionClassKey($sClassName), $xReflectionClass);
            // Register the user class, but only if the user didn't already.
            if(!$this->has($sClassName))
            {
                $this->set($sClassName, function() use($sClassName) {
                    return $this->make($this->get($this->getReflectionClassKey($sClassName)));
                });
            }
        }
        catch(ReflectionException $e)
        {
            throw new SetupException($this->xTranslator->trans('errors.class.invalid',
                ['name' => $sClassName]));
        }
    }

    /**
     * Find a component amongst the registered namespaces and directories.
     *
     * @param class-string $sClassName The class name
     *
     * @return void
     * @throws SetupException
     */
    private function discoverComponent(string $sClassName)
    {
        if(!isset($this->aComponents[$sClassName]))
        {
            $xRegistry = $this->di->g(ComponentRegistry::class);
            $xRegistry->updateHash(false); // Disable hash calculation.
            $aOptions = $xRegistry->getNamespaceComponentOptions($sClassName);
            if($aOptions !== null)
            {
                $this->registerComponent($sClassName, $aOptions);
            }
            else // if(!isset($this->aComponents[$sClassName]))
            {
                // The component was not found in a registered namespace. We need to parse all
                // the directories to be able to find a component registered without a namespace.
                $xRegistry->registerComponentsInDirectories();
            }
        }
        if(!isset($this->aComponents[$sClassName]))
        {
            throw new SetupException($this->xTranslator
                ->trans('errors.class.invalid', ['name' => $sClassName]));
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
            $aCallableObjects[$sClassName] = $this->makeCallableObject($sClassName);
        }
        return $aCallableObjects;
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

        $this->discoverComponent($sClassName);
        $aOptions = $this->aComponents[$sClassName];

        // Register the callable object
        $this->set($sComponentObject, function() use($sClassName, $aOptions) {
            $xReflectionClass = $this->get($this->getReflectionClassKey($sClassName));
            return new CallableObject($this, $this->di, $xReflectionClass, $aOptions);
        });

        // Initialize the user class instance
        $this->xContainer->extend($sClassName, function($xClassInstance) use($sClassName) {
            if($xClassInstance instanceof AbstractComponent)
            {
                $xHelper = $this->get($this->getCallableHelperKey($sClassName));
                $xHelper->xTarget = $this->xTarget;

                // Call the protected "_init()" method of the Component class.
                $cSetter = function($di, $xHelper) {;
                    $this->_init($di, $xHelper);
                };
                $cSetter = $cSetter->bindTo($xClassInstance, $xClassInstance);
                call_user_func($cSetter, $this->di, $xHelper);
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
     * Get the callable object for a given class
     *
     * @param class-string $sClassName
     *
     * @return CallableObject
     */
    public function getCallableObject(string $sClassName): CallableObject
    {
        return $this->get($this->getCallableObjectKey($sClassName));
    }

    /**
     * @param string $sClassName A class name, but possibly with dot or underscore as separator
     *
     * @return class-string
     * @throws SetupException
     */
    private function getClassName(string $sClassName): string
    {
        // Replace all separators ('.' or '_') with antislashes, and trim the class name.
        $sSeparator = !$this->bUsingUnderscore ? '.' : '_';
        return trim(str_replace($sSeparator, '\\', $sClassName), '\\');
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
        $sClassName = $this->getClassName($sClassName);
        $this->_registerComponent($sClassName);
        return $this->getCallableObject($sClassName);
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
        $sClassName = $this->getClassName($sClassName);
        $this->_registerComponent($sClassName);
        return $this->get($sClassName);
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

            $sPrefix = $this->di->g(ConfigManager::class)->getOption('core.prefix.class', '');
            return new JxnClassCall($this->di->g(DialogCommand::class),
                $sPrefix . $xCallable->getJsName());
        });
    }

    /**
     * Get a factory for a call to a registered function.
     *
     * @return JxnCall
     */
    public function getFunctionRequestFactory(): JxnCall
    {
        return $this->get($this->getRequestFactoryKey(JxnCall::class));
    }

    /**
     * Get a factory for a call to a registered component.
     *
     * @param class-string $sClassName
     *
     * @return JxnCall|null
     */
    public function getComponentRequestFactory(string $sClassName): ?JxnCall
    {
        $sClassName = trim($sClassName, " \t");
        if($sClassName === '')
        {
            return null;
        }

        $sFactoryKey = $this->getRequestFactoryKey($sClassName);
        if(!$this->has($sFactoryKey))
        {
            $this->registerRequestFactory($sClassName, $sFactoryKey);
        }
        return $this->get($sFactoryKey);
    }
}
