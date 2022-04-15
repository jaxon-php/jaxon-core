<?php

/**
 * Container.php - Jaxon DI container
 *
 * Provide container service for Jaxon classes.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Di;

use Jaxon\App\Ajax;
use Jaxon\App\Session\SessionInterface;
use Pimple\Container as PimpleContainer;
use Pimple\Exception\UnknownIdentifierException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

use Closure;
use ReflectionClass;
use ReflectionException;

use function realpath;

class Container extends PimpleContainer implements LoggerAwareInterface
{
    use LoggerAwareTrait;

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
     * The Dependency Injection Container
     *
     * @var ContainerInterface
     */
    private $xContainer = null;

    /**
     * The class constructor
     */
    public function __construct(Ajax $jaxon)
    {
        parent::__construct();

        // Set the default logger
        $this->setLogger(new NullLogger());

        // Save the Ajax and Container instances
        $this->val(Ajax::class, $jaxon);
        $this->val(Container::class, $this);
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
        $this->registerAnnotations();
    }

    /**
     * Get the logger
     *
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
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
        $this->xContainer = $xContainer;
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
        return $this->offsetExists($sClass);
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
        if($this->xContainer != null && $this->xContainer->has($sClass))
        {
            return true;
        }
        return $this->offsetExists($sClass);
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
        return $this->offsetGet($sClass);
    }

    /**
     * Get a class instance
     *
     * @param string $sClass    The full class name
     *
     * @return mixed
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     * @throws UnknownIdentifierException If the identifier is not defined
     */
    public function get(string $sClass)
    {
        if($this->xContainer != null && $this->xContainer->has($sClass))
        {
            return $this->xContainer->get($sClass);
        }
        return $this->offsetGet($sClass);
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
        $this->offsetSet($sClass, $xClosure);
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
        $this->offsetSet($sKey, $xValue);
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
        $this->set($sAlias, function($c) use ($sClass) {
            return $c->get($sClass);
        });
    }

    /**
     * Create an instance of a class, getting the constructor parameters from the DI container
     *
     * @param string|ReflectionClass $xClass    The class name or the reflection class
     *
     * @return object|null
     * @throws ReflectionException
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws UnknownIdentifierException
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
        $parameters = $constructor->getParameters();
        $parameterInstances = [];
        foreach($parameters as $parameter)
        {
            // Get the parameter instance from the DI
            $parameterInstances[] = $this->get($parameter->getClass()->getName());
        }
        return $xClass->newInstanceArgs($parameterInstances);
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
