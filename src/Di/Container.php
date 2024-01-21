<?php

/**
 * Container.php
 *
 * Jaxon DI container. Provide container service for Jaxon classes.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Di;

use Jaxon\App\Ajax;
use Jaxon\App\I18n\Translator;
use Jaxon\App\Session\SessionInterface;
use Jaxon\Exception\SetupException;
use Pimple\Container as PimpleContainer;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

use Closure;
use Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;
use Throwable;

use function array_map;
use function realpath;

class Container
{
    use Traits\AppTrait;
    use Traits\PsrTrait;
    use Traits\RequestTrait;
    use Traits\ResponseTrait;
    use Traits\PluginTrait;
    use Traits\CallableTrait;
    use Traits\RegisterTrait;
    use Traits\ViewTrait;
    use Traits\UtilTrait;

    /**
     * The library Dependency Injection Container
     *
     * @var PimpleContainer
     */
    private $xLibContainer;

    /**
     * The application or framework Dependency Injection Container
     *
     * @var ContainerInterface
     */
    private $xAppContainer = null;

    /**
     * The class constructor
     */
    public function __construct(Ajax $jaxon)
    {
        $this->xLibContainer = new PimpleContainer();

        // Save the Ajax and Container instances
        $this->val(Ajax::class, $jaxon);
        $this->val(Container::class, $this);

        // Register the null logger by default
        $this->setLogger(new NullLogger());

        // Template directory
        $sTemplateDir = realpath(__DIR__ . '/../../templates');
        $this->val('jaxon.core.dir.template', $sTemplateDir);

        // Translation directory
        $sTranslationDir = realpath(__DIR__ . '/../../translations');
        $this->val('jaxon.core.dir.translation', $sTranslationDir);

        $this->registerAll();
    }

    /**
     * Register the values into the container
     *
     * @return void
     */
    private function registerAll()
    {
        $this->registerApp();
        $this->registerPsr();
        $this->registerRequests();
        $this->registerResponses();
        $this->registerPlugins();
        $this->registerCallables();
        $this->registerViews();
        $this->registerUtils();
    }

    /**
     * Set the logger
     *
     * @param LoggerInterface $xLogger
     *
     * @return void
     */
    public function setLogger(LoggerInterface $xLogger)
    {
        $this->val(LoggerInterface::class, $xLogger);
    }

    /**
     * Get the logger
     *
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->get(LoggerInterface::class);
    }

    /**
     * Set the container provided by the integrated framework
     *
     * @param ContainerInterface $xContainer    The container implementation
     *
     * @return void
     */
    public function setContainer(ContainerInterface $xContainer)
    {
        $this->xAppContainer = $xContainer;
    }

    /**
     * Check if a class is defined in the container
     *
     * @param string $sClass    The full class name
     *
     * @return bool
     */
    public function h(string $sClass): bool
    {
        return $this->xLibContainer->offsetExists($sClass);
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
        if($this->xAppContainer != null && $this->xAppContainer->has($sClass))
        {
            return true;
        }
        return $this->xLibContainer->offsetExists($sClass);
    }

    /**
     * Get a class instance
     *
     * @param string $sClass    The full class name
     *
     * @return mixed
     */
    public function g(string $sClass)
    {
        return $this->xLibContainer->offsetGet($sClass);
    }

    /**
     * Get a class instance
     *
     * @param string $sClass The full class name
     *
     * @return mixed
     * @throws SetupException
     */
    public function get(string $sClass)
    {
        try
        {
            if($this->xAppContainer != null && $this->xAppContainer->has($sClass))
            {
                return $this->xAppContainer->get($sClass);
            }
            return $this->xLibContainer->offsetGet($sClass);
        }
        catch(Exception|Throwable $e)
        {
            $xLogger = $this->g(LoggerInterface::class);
            $xTranslator = $this->g(Translator::class);
            $sMessage = $e->getMessage() . ': ' . $xTranslator->trans('errors.class.container', ['name' => $sClass]);
            $xLogger->error($e->getMessage(), ['message' => $sMessage]);
            throw new SetupException($sMessage);
        }
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
       $this->xLibContainer->offsetSet($sClass, function() use($xClosure) {
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
       $this->xLibContainer->offsetSet($sKey, $xValue);
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
            if($this->has($xType->getName() . ' $' . $xParameter->getName()))
            {
                return $this->get($xType->getName() . ' $' . $xParameter->getName());
            }
            // Check the class only
            if($this->get($xType->getName()))
            {
                return $this->get($xType->getName());
            }
        }
        // Check the name only
        return $this->get('$' . $xParameter->getName());
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
     * Get the session manager
     *
     * @return SessionInterface|null
     */
    public function getSessionManager(): ?SessionInterface
    {
        return $this->h(SessionInterface::class) ? $this->g(SessionInterface::class) : null;
    }

    /**
     * Set the session manager
     *
     * @param Closure $xClosure    A closure to create the session manager instance
     *
     * @return void
     */
    public function setSessionManager(Closure $xClosure)
    {
        $this->set(SessionInterface::class, $xClosure);
    }
}
