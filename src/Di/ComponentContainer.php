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

use Jaxon\App\Component;
use Jaxon\App\Component\AbstractComponent;
use Jaxon\App\Component\ComponentFactory;
use Jaxon\App\Component\ComponentHelper;
use Jaxon\App\Component\Logger as LoggerComponent;
use Jaxon\App\Component\Pagination;
use Jaxon\App\Config\ConfigManager;
use Jaxon\App\FuncComponent;
use Jaxon\App\I18n\Translator;
use Jaxon\App\NodeComponent;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\Request\CallableClass\CallableObject;
use Jaxon\Request\Handler\CallbackManager;
use Jaxon\Request\Target;
use Jaxon\Script\Call\JxnCall;
use Jaxon\Script\Call\JxnClassCall;
use Jaxon\Script\JsExpr;
use Pimple\Container as PimpleContainer;
use Closure;
use ReflectionClass;
use ReflectionException;

use function call_user_func;
use function str_replace;
use function trim;

class ComponentContainer
{
    use Traits\DiAutoTrait;
    use Traits\ComponentTrait;

    /**
     * The Dependency Injection Container for registered classes
     *
     * @var PimpleContainer
     */
    private $xContainer;

    /**
     * @var string
     */
    private string $sCurrentClassName = '';

    /**
     * @var Target|null
     */
    private Target|null $xCurrentTarget = null;

    /**
     * The class constructor
     *
     * @param Container $di
     */
    public function __construct(private Container $di)
    {
        $this->xContainer = new PimpleContainer();
        $this->val(ComponentContainer::class, $this);

        // Register the call factory for registered functions
        $this->set($this->getRequestFactoryKey(JxnCall::class), fn() =>
            new JxnCall($this->di->g(ConfigManager::class)
                ->getOption('core.prefix.function', '')));

        // Register the pagination component, but do not export to js.
        $this->saveComponent(Pagination::class, [
            'excluded' => true,
            'separator' => '.',
            // The namespace has the same name as the Component class.
            'namespace' => Component::class,
        ]);

        // Register the logger component, and export to js.
        $this->di->callback()->boot(function() {
            if($this->di->config()->loggingEnabled())
            {
                $this->saveComponent(LoggerComponent::class, [
                    'separator' => '.',
                    // The namespace has the same name as the Component class.
                    'namespace' => Component::class,
                ]);
            }
        });

        JsExpr::setDialogCommand($di->getDialogCommand());

        $this->setComponentPublicMethods('node', NodeComponent::class, ['item', 'html']);
        $this->setComponentPublicMethods('func', FuncComponent::class, ['paginator']);
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
    public function set(string $sClass, Closure $xClosure): void
    {
        $this->xContainer->offsetSet($sClass, fn() => $xClosure($this->di));
    }

    /**
     * Save a value in the container
     *
     * @param string|class-string $sKey    The key
     * @param mixed $xValue    The value
     *
     * @return void
     */
    public function val(string $sKey, $xValue): void
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
    public function get(string $sClass): mixed
    {
        return $this->xContainer->offsetGet($sClass);
    }

    /**
     * Get the component called in the ajax request.
     *
     * @template T
     * @param class-string<T> $sClassName the class name
     * @param Target $xTarget
     *
     * @return T|null
     */
    public function getCalledComponent(string $sClassName, Target $xTarget): mixed
    {
        $this->sCurrentClassName = $sClassName;
        $this->xCurrentTarget = $xTarget;

        $xComponent = $this->get($sClassName);
        /** @var CallableObject */
        $xCallableObject = $this->get($this->getCallableObjectKey($sClassName));
        $xCallableObject->setDiMethodAttributes($xComponent, $xTarget->method());

        return $xComponent;
    }

    /**
     * Get the component target
     *
     * @param string $sClassName the class name
     *
     * @return Target|null
     */
    public function getComponentTarget(string $sClassName): Target|null
    {
        return $sClassName === $this->sCurrentClassName ? $this->xCurrentTarget : null;
    }

    /**
     * Get the component helper
     *
     * @param string $sClassName the class name
     *
     * @return ComponentHelper
     */
    public function getComponentHelper(string $sClassName): ComponentHelper
    {
        return $this->get($this->getCallableHelperKey($sClassName));
    }

    /**
     * Register a component and its options
     *
     * @param class-string $sClassName    The class name
     * @param array $aOptions    The class options
     *
     * @return void
     */
    public function saveComponent(string $sClassName, array $aOptions): void
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

            $this->_saveClassOptions($sClassName, $aOptions);

            $sClassKey = $this->getReflectionClassKey($sClassName);
            $this->val($sClassKey, $xReflectionClass);
            // Register the user class, but only if the user didn't already.
            if(!$this->has($sClassName))
            {
                $this->set($sClassName, fn() => $this->make($this->get($sClassKey)));
            }
        }
        catch(ReflectionException $e)
        {
            throw new SetupException($this->cn()->g(Translator::class)
                ->trans('errors.class.invalid', ['name' => $sClassName]));
        }
    }

    /**
     * Register a component
     *
     * @param string $sComponentId The component name
     *
     * @return string
     * @throws SetupException
     */
    private function _registerComponent(string $sComponentId): string
    {
        // Replace all separators ('.' or '_') with antislashes, and trim the class name.
        $sClassName = trim(str_replace(['.', '_'], '\\', $sComponentId), '\\');

        $sComponentObject = $this->getCallableObjectKey($sClassName);
        // Prevent duplication. It's important not to use the class name here.
        if($this->has($sComponentObject))
        {
            return $sClassName;
        }

        // Register the callable factory class
        $this->set($this->getCallableFactoryKey($sClassName),
            fn() => new ComponentFactory($this, $sClassName));

        // Register the callable helper class
        $this->set($this->getCallableHelperKey($sClassName),
            fn(Container $di) => new ComponentHelper($di->getViewRenderer(),
                $di->getLogger(), $di->getStash(), $di->getUploadHandler(),
                $di->getSessionManager(), $di->getPaginationRenderer()));

        $this->discoverComponent($sClassName);

        // Register the callable object
        $this->set($sComponentObject, function() use($sComponentId, $sClassName) {
            $aOptions = $this->_getClassOptions($sComponentId);
            $xReflectionClass = $this->get($this->getReflectionClassKey($sClassName));
            $xOptions = $this->getComponentOptions($xReflectionClass, $aOptions);
            return new CallableObject($this, $this->di, $xReflectionClass, $xOptions);
        });

        // Initialize the user class instance
        $this->xContainer->extend($sClassName, function($xClassInstance) use($sClassName) {
            if($xClassInstance instanceof AbstractComponent)
            {
                // Call the protected "initComponent()" method of the Component class.
                $cSetter = function($di, $xFactory) {
                    // "$this" here refers to the AbstractComponent instance.
                    $this->initComponent($di, $xFactory); 
                };
                $cSetter = $cSetter->bindTo($xClassInstance, $xClassInstance);
                $xFactory = $this->get($this->getCallableFactoryKey($sClassName));
                call_user_func($cSetter, $this->di, $xFactory);
            }

            // Run the callbacks for class initialisation
            $this->di->g(CallbackManager::class)->onInit($xClassInstance);

            // Set attributes from the DI container.
            // The class level DI options are set on any component.
            // The method level DI options will be set only on the targetted component.
            /** @var CallableObject */
            $xCallableObject = $this->get($this->getCallableObjectKey($sClassName));
            $xCallableObject->setDiClassAttributes($xClassInstance);

            return $xClassInstance;
        });

        return $sClassName;
    }

    /**
     * Get the callable object for a given class
     * The callable object is registered if it is not already in the DI.
     *
     * @param string $sComponentId
     *
     * @return CallableObject|null
     * @throws SetupException
     */
    public function makeCallableObject(string $sComponentId): ?CallableObject
    {
        $sClassName = $this->_registerComponent($sComponentId);
        return $this->get($this->getCallableObjectKey($sClassName));
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
        $sComponentId = str_replace('\\', '.', $sClassName);
        $sClassName = $this->_registerComponent($sComponentId);
        return $this->get($sClassName);
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
            $this->xContainer->offsetSet($sFactoryKey, function() use($sClassName) {
                $sComponentId = str_replace('\\', '.', $sClassName);
                if(!($xCallable = $this->makeCallableObject($sComponentId)))
                {
                    return null;
                }

                $xConfigManager = $this->di->g(ConfigManager::class);
                $sPrefix = $xConfigManager->getOption('core.prefix.class', '');
                return new JxnClassCall($sPrefix . $xCallable->getJsName());
            });
        }
        return $this->get($sFactoryKey);
    }
}
