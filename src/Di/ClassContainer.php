<?php

/**
 * ClassContainer.php
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

use Jaxon\App\AbstractCallable;
use Jaxon\App\Config\ConfigManager;
use Jaxon\App\Dialog\DialogManager;
use Jaxon\App\I18n\Translator;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\AnnotationReaderInterface;
use Jaxon\Plugin\Request\CallableClass\CallableClassHelper;
use Jaxon\Plugin\Request\CallableClass\CallableObject;
use Jaxon\Plugin\Request\CallableClass\CallableRegistry;
use Jaxon\Plugin\Request\CallableClass\CallableRepository;
use Jaxon\Request\Handler\CallbackManager;
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

class ClassContainer
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
     * The class constructor
     */
    public function __construct(private Container $di)
    {
        $this->xContainer = new PimpleContainer();
        $this->val(ClassContainer::class, $this);

        // Register the call factory for registered functions
        $this->set($this->getRequestFactoryKey(JxnCall::class), function() {
            return new JxnCall($this->di->g(DialogManager::class),
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
     * @param string $sClass    The full class name
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
     * @param string $sClass    The full class name
     *
     * @return mixed
     */
    public function get(string $sClass)
    {
        return $this->xContainer->offsetGet($sClass);
    }

    /**
     * Save a closure in the container
     *
     * @param string $sClass    The full class name
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
     * @param string $sKey    The key
     * @param mixed $xValue    The value
     *
     * @return void
     */
    public function val(string $sKey, $xValue)
    {
       $this->xContainer->offsetSet($sKey, $xValue);
    }

    /**
     * Set an alias in the container
     *
     * @param string $sAlias    The alias name
     * @param string $sClass    The class name
     *
     * @return void
     */
    public function alias(string $sAlias, string $sClass)
    {
        $this->set($sAlias, function($di) use ($sClass) {
            return $di->get($sClass);
        });
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
     * @param string|ReflectionClass $xClass The class name or the reflection class
     *
     * @return object|null
     * @throws ReflectionException
     * @throws SetupException
     */
    public function make($xClass)
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
     * @param string $sClass    The class name
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
     * @param string $sClassName The callable class name
     *
     * @return string
     */
    private function getCallableObjectKey(string $sClassName): string
    {
        return $sClassName . '_CallableObject';
    }

    /**
     * @param string $sClassName The callable class name
     *
     * @return string
     */
    private function getCallableHelperKey(string $sClassName): string
    {
        return $sClassName . '_CallableHelper';
    }

    /**
     * @param string $sClassName The callable class name
     *
     * @return string
     */
    private function getReflectionClassKey(string $sClassName): string
    {
        return $sClassName . '_ReflectionClass';
    }

    /**
     * Register a callable class
     *
     * @param string $sClassName The callable class name
     * @param array $aOptions The callable object options
     *
     * @return void
     * @throws SetupException
     */
    public function registerCallableClass(string $sClassName, array $aOptions)
    {
        $sCallableObject = $this->getCallableObjectKey($sClassName);
        // Prevent duplication. It's important not to use the class name here.
        if($this->has($sCallableObject))
        {
            return;
        }

        // Register the helper class
        $this->set($this->getCallableHelperKey($sClassName), function() use($sClassName) {
            $xFactory = $this->di->getCallFactory();
            return new CallableClassHelper($this, $xFactory->rq($sClassName), $xFactory,
                $this->di->getViewRenderer(), $this->di->getLogger(),
                $this->di->getSessionManager(), $this->di->getUploadHandler());
        });

        // Register the reflection class
        try
        {
            // Make sure the registered class exists
            isset($aOptions['include']) && require_once($aOptions['include']);
            $this->val($this->getReflectionClassKey($sClassName), new ReflectionClass($sClassName));
        }
        catch(ReflectionException $e)
        {
            $xTranslator = $this->di->g(Translator::class);
            $sMessage = $xTranslator->trans('errors.class.invalid', ['name' => $sClassName]);
            throw new SetupException($sMessage);
        }

        // Register the callable object
        $this->set($sCallableObject, function() use($sClassName, $aOptions) {
            $xReflectionClass = $this->get($this->getReflectionClassKey($sClassName));
            $xRepository = $this->di->g(CallableRepository::class);
            $aProtectedMethods = $xRepository->getProtectedMethods($sClassName);

            return new CallableObject($this, $this->di, $xReflectionClass,
                $this->di->g(AnnotationReaderInterface::class), $aOptions, $aProtectedMethods);
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
            if($xClassInstance instanceof AbstractCallable)
            {
                $xClassInstance->_initCallable($this->di, $this->get($this->getCallableHelperKey($sClassName)));
            }

            // Run the callbacks for class initialisation
            $this->di->g(CallbackManager::class)->onInit($xClassInstance);

            // Set attributes from the DI container.
            // The class level DI options are set when creating the object instance.
            // The method level DI options are set only when calling the method in the ajax request.
            /** @var CallableObject */
            $xCallableObject = $this->get($this->getCallableObjectKey($sClassName));
            $xCallableObject->setDiClassAttributes($xClassInstance);

            return $xClassInstance;
        });
    }

    /**
     * Get the callable object for a given class
     *
     * @param string $sClassName
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
     * @param string $sClassName The class name of the callable object
     *
     * @return string
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
        $xRepository = $this->di->g(CallableRepository::class);
        $this->registerCallableClass($sClassName, $xRepository->getClassOptions($sClassName));
        return $sClassName;
    }

    /**
     * Get the callable object for a given class
     * The callable object is registered if it is not already in the DI.
     *
     * @param string $sClassName The class name of the callable object
     *
     * @return CallableObject|null
     * @throws SetupException
     */
    public function makeCallableObject(string $sClassName): ?CallableObject
    {
        return $this->getCallableObject($this->checkCallableObject($sClassName));
    }

    /**
     * Get an instance of a Jaxon class by name
     *
     * @param string $sClassName the class name
     *
     * @return mixed
     * @throws SetupException
     */
    public function makeRegisteredObject(string $sClassName)
    {
        $xCallableObject = $this->makeCallableObject($sClassName);
        return !$xCallableObject ? null : $xCallableObject->getRegisteredObject();
    }

    /**
     * Get the callable registry
     *
     * @return CallableRegistry
     */
    public function getCallableRegistry(): CallableRegistry
    {
        return $this->di->g(CallableRegistry::class);
    }

    /**
     * @param string $sClassName The callable class name
     *
     * @return string
     */
    private function getRequestFactoryKey(string $sClassName): string
    {
        return $sClassName . '_RequestFactory';
    }

    /**
     * @param string $sClassName
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
            return new JxnClass($this->di->g(DialogManager::class), $sJsObject);
        });
    }

    /**
     * Get a factory for a js function call.
     *
     * @param string $sClassName
     *
     * @return JxnCall|null
     */
    public function getRequestFactory(string $sClassName = ''): ?JxnCall
    {
        $sClassName = trim($sClassName, " \t") ?: JxnCall::class;
        $sFactoryKey = $this->getRequestFactoryKey($sClassName);
        if(!$this->has($sFactoryKey))
        {
            $this->registerRequestFactory($sClassName, $sFactoryKey);
        }
        return $this->get($sFactoryKey);
    }
}
