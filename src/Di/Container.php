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

use Jaxon\App\I18n\Translator;
use Jaxon\App\Session\SessionInterface;
use Jaxon\Exception\SetupException;
use Lagdo\Facades\ContainerWrapper;
use Pimple\Container as PimpleContainer;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Closure;
use Throwable;

use function dirname;
use function is_a;

class Container implements ContainerInterface
{
    use Traits\AppTrait;
    use Traits\PsrTrait;
    use Traits\RequestTrait;
    use Traits\ResponseTrait;
    use Traits\PluginTrait;
    use Traits\CallableTrait;
    use Traits\ViewTrait;
    use Traits\UtilTrait;
    use Traits\MetadataTrait;
    use Traits\DiAutoTrait;

    /**
     * The application or framework Dependency Injection Container
     *
     * @var ContainerInterface
     */
    private $xAppContainer = null;

    /**
     * The class constructor
     */
    public function __construct()
    {
        $this->xLibContainer = new PimpleContainer();

        // The container instance is saved in the container.
        $this->val(Container::class, $this);

        // Register the null logger by default
        $this->setLogger(new NullLogger());

        // Setup the logger facade. Don't overwrite if it's already set.
        ContainerWrapper::setContainer($this, false);

        $sBaseDir = dirname(__DIR__, 2);
        // Template directory
        $this->val('jaxon.core.dir.template', "$sBaseDir/templates");

        // Translation directory
        $this->val('jaxon.core.dir.translation', "$sBaseDir/translations");

        $this->registerAll();
        $this->setEventHandlers();
    }

    /**
     * The container for parameters
     *
     * @return Container
     */
    protected function cn(): Container
    {
        return $this;
    }

    /**
     * Register the values into the container
     *
     * @return void
     */
    private function registerAll(): void
    {
        $this->registerApp();
        $this->registerPsr();
        $this->registerRequests();
        $this->registerResponses();
        $this->registerPlugins();
        $this->registerCallables();
        $this->registerViews();
        $this->registerUtils();
        $this->registerMetadataReader();
    }

    /**
     * Set the logger
     *
     * @param LoggerInterface|Closure $xLogger
     *
     * @return void
     */
    public function setLogger(LoggerInterface|Closure $xLogger): void
    {
        is_a($xLogger, LoggerInterface::class) ?
            $this->val(LoggerInterface::class, $xLogger) :
            $this->set(LoggerInterface::class, $xLogger);
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
    public function setContainer(ContainerInterface $xContainer): void
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
        return $this->xAppContainer != null && $this->xAppContainer->has($sClass) ?
            true : $this->xLibContainer->offsetExists($sClass);
    }

    /**
     * Get a class instance
     *
     * @template T
     * @param class-string<T> $sClass The full class name
     *
     * @return T
     */
    public function g(string $sClass): mixed
    {
        return $this->xLibContainer->offsetGet($sClass);
    }

    /**
     * Get a class instance
     *
     * @template T
     * @param class-string<T> $sClass The full class name
     *
     * @return T
     * @throws SetupException
     */
    public function get(string $sClass): mixed
    {
        try
        {
            return $this->xAppContainer != null && $this->xAppContainer->has($sClass) ?
                $this->xAppContainer->get($sClass) : $this->xLibContainer->offsetGet($sClass);
        }
        catch(Throwable $e)
        {
            $xLogger = $this->g(LoggerInterface::class);
            $xTranslator = $this->g(Translator::class);
            $sMessage = $e->getMessage() . ': ' .
                $xTranslator->trans('errors.class.container', ['name' => $sClass]);
            $xLogger->error($e->getMessage(), ['message' => $sMessage]);
            throw new SetupException($sMessage);
        }
    }

    /**
     * Save a closure in the container
     *
     * @param string|class-string $sClass    The full class name
     * @param Closure $xClosure    The closure
     * @param bool $bIsSingleton
     *
     * @return void
     */
    public function set(string $sClass, Closure $xClosure, bool $bIsSingleton = true): void
    {
        // Wrap the user closure into a new closure, so it can take this container as a parameter.
        $xClosure = fn() => $xClosure($this);
        $this->xLibContainer->offsetSet($sClass, $bIsSingleton ?
            $xClosure : $this->xLibContainer->factory($xClosure));
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
       $this->xLibContainer->offsetSet($sKey, $xValue);
    }

    /**
     * Set an alias in the container
     *
     * @param string|class-string $sAlias    The alias name
     * @param string|class-string $sClass    The class name
     *
     * @return void
     */
    public function alias(string $sAlias, string $sClass): void
    {
        $this->set($sAlias, fn($di) => $di->get($sClass));
    }

    /**
     * Save a closure in the container
     *
     * @param string|class-string $sClass    The full class name
     * @param Closure $xClosure    The closure
     *
     * @return void
     */
    public function extend(string $sClass, Closure $xClosure): void
    {
        $this->xLibContainer->extend($sClass, $xClosure);
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
    public function setSessionManager(Closure $xClosure): void
    {
        $this->set(SessionInterface::class, $xClosure);
    }
}
