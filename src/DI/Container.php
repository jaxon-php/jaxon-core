<?php

/**
 * Container.php - Jaxon data container
 *
 * Provide container service for Jaxon utils class instances.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\DI;

use Lemon\Event\EventDispatcher;

use Jaxon\Response\Response;
use Jaxon\Config\Config;
use Jaxon\Config\Reader as ConfigReader;
use Jaxon\Request\Support\CallableRepository;
use Jaxon\Request\Handler as RequestHandler;
use Jaxon\Request\Factory as RequestFactory;
use Jaxon\Response\Manager as ResponseManager;
use Jaxon\Plugin\Manager as PluginManager;
use Jaxon\Plugin\CodeGenerator;
use Jaxon\App\View\Manager as ViewManager;
use Jaxon\App\View\Facade as ViewFacade;
use Jaxon\App\Dialogs\Dialog;
use Jaxon\App\View\Renderer;
use Jaxon\Utils\Template\Minifier;
use Jaxon\Utils\Translation\Translator;
use Jaxon\Utils\Template\Template;
use Jaxon\Utils\Validation\Validator;
use Jaxon\Utils\Pagination\Paginator;
use Jaxon\Utils\Pagination\Renderer as PaginationRenderer;
use Jaxon\Contracts\App\Session as SessionContract;
use Jaxon\Contracts\Container as ContainerContract;

class Container
{
    /**
     * The Dependency Injection Container
     *
     * @var \Pimple\Container
     */
    private $libContainer = null;

    /**
     * The Dependency Injection Container
     *
     * @var \Jaxon\Contracts\Container
     */
    private $appContainer = null;

    /**
     * The class constructor
     */
    public function __construct()
    {
        $this->libContainer = new \Pimple\Container();

        $sTranslationDir = realpath(__DIR__ . '/../../translations');
        $sTemplateDir = realpath(__DIR__ . '/../../templates');
        $this->init($sTranslationDir, $sTemplateDir);
    }

    /**
     * Get the container provided by the integrated framework
     *
     * @return ContainerContract
     */
    public function getAppContainer()
    {
        return $this->appContainer;
    }

    /**
     * Set the container provided by the integrated framework
     *
     * @param ContainerContract  $container     The container implementation
     *
     * @return void
     */
    public function setAppContainer(ContainerContract $container)
    {
        $this->appContainer = $container;
    }

    /**
     * Set the parameters and create the objects in the dependency injection container
     *
     * @param string        $sTranslationDir     The translation directory
     * @param string        $sTemplateDir        The template directory
     *
     * @return void
     */
    private function init($sTranslationDir, $sTemplateDir)
    {
        /*
         * Parameters
         */
        // Translation directory
        $this->libContainer['jaxon.core.translation_dir'] = $sTranslationDir;
        // Template directory
        $this->libContainer['jaxon.core.template_dir'] = $sTemplateDir;

        /*
         * Core library objects
         */
        // Global Response
        $this->libContainer[Response::class] = function () {
            return new Response();
        };
        // Dialog
        $this->libContainer[Dialog::class] = function () {
            return new Dialog();
        };
        // Jaxon App
        $this->libContainer[\Jaxon\App\App::class] = function () {
            return new \Jaxon\App\App();
        };

        /*
         * Managers
         */
        // Callable objects repository
        $this->libContainer[CallableRepository::class] = function () {
            return new CallableRepository();
        };
        // Plugin Manager
        $this->libContainer[PluginManager::class] = function () {
            return new PluginManager();
        };
        // Request Handler
        $this->libContainer[RequestHandler::class] = function ($c) {
            return new RequestHandler($c[PluginManager::class], $c[ResponseManager::class]);
        };
        // Request Factory
        $this->libContainer[RequestFactory::class] = function ($c) {
            return new RequestFactory($c[CallableRepository::class]);
        };
        // Response Manager
        $this->libContainer[ResponseManager::class] = function () {
            return new ResponseManager();
        };
        // Code Generator
        $this->libContainer[CodeGenerator::class] = function ($c) {
            return new CodeGenerator($c[PluginManager::class]);
        };
        // View Manager
        $this->libContainer[ViewManager::class] = function () {
            return new ViewManager();
        };
        // View Renderer Facade
        $this->libContainer[ViewFacade::class] = function ($c) {
            return new ViewFacade($c[ViewManager::class]);
        };

        /*
         * Config
         */
        $this->libContainer[Config::class] = function () {
            return new Config();
        };
        $this->libContainer[ConfigReader::class] = function () {
            return new ConfigReader();
        };

        /*
         * Services
         */
        // Minifier
        $this->libContainer[Minifier::class] = function () {
            return new Minifier();
        };
        // Translator
        $this->libContainer[Translator::class] = function ($c) {
            return new Translator($c['jaxon.core.translation_dir'], $c[Config::class]);
        };
        // Template engine
        $this->libContainer[Template::class] = function ($c) {
            return new Template($c['jaxon.core.template_dir']);
        };
        // Validator
        $this->libContainer[Validator::class] = function ($c) {
            return new Validator($c[Translator::class], $c[Config::class]);
        };
        // Pagination Paginator
        $this->libContainer[Paginator::class] = function ($c) {
            return new Paginator($c[PaginationRenderer::class]);
        };
        // Pagination Renderer
        $this->libContainer[PaginationRenderer::class] = function ($c) {
            return new PaginationRenderer($c[Template::class]);
        };
        // Event Dispatcher
        $this->libContainer[EventDispatcher::class] = function () {
            return new EventDispatcher();
        };
    }

    /**
     * Get a class instance
     *
     * @return object        The class instance
     */
    public function get($sClass)
    {
        if($this->appContainer != null && $this->appContainer->has($sClass))
        {
            return $this->appContainer->get($sClass);
        }
        return $this->libContainer[$sClass];
    }

    /**
     * Set a DI closure
     *
     * @param string                $sClass             The full class name
     * @param Closure               $xClosure           The closure
     *
     * @return void
     */
    public function set($sClass, $xClosure)
    {
        $this->libContainer[$sClass] = $xClosure;
    }

    /**
     * Get the plugin manager
     *
     * @return \Jaxon\Plugin\Manager
     */
    public function getPluginManager()
    {
        return $this->libContainer[PluginManager::class];
    }

    /**
     * Get the request handler
     *
     * @return \Jaxon\Request\Handler
     */
    public function getRequestHandler()
    {
        return $this->libContainer[RequestHandler::class];
    }

    /**
     * Get the request factory
     *
     * @return \Jaxon\Factory\Request
     */
    public function getRequestFactory()
    {
        return $this->libContainer[RequestFactory::class];
    }

    /**
     * Get the response manager
     *
     * @return \Jaxon\Response\Manager
     */
    public function getResponseManager()
    {
        return $this->libContainer[ResponseManager::class];
    }

    /**
     * Get the code generator
     *
     * @return \Jaxon\Code\Generator
     */
    public function getCodeGenerator()
    {
        return $this->libContainer[CodeGenerator::class];
    }

    /**
     * Get the config manager
     *
     * @return \Jaxon\Config\Config
     */
    public function getConfig()
    {
        return $this->libContainer[Config::class];
    }

    /**
     * Create a new the config manager
     *
     * @return \Jaxon\Config\Config            The config manager
     */
    public function newConfig()
    {
        return new \Jaxon\Config\Config();
    }

    /**
     * Get the dialog wrapper
     *
     * @return \Jaxon\App\Dialogs\Dialog
     */
    public function getDialog()
    {
        return $this->libContainer[Dialog::class];
    }

    /**
     * Get the minifier
     *
     * @return \Jaxon\Utils\Template\Minifier
     */
    public function getMinifier()
    {
        return $this->libContainer[Minifier::class];
    }

    /**
     * Get the translator
     *
     * @return \Jaxon\Utils\Translation\Translator
     */
    public function getTranslator()
    {
        return $this->libContainer[Translator::class];
    }

    /**
     * Get the template engine
     *
     * @return \Jaxon\Utils\Template\Template
     */
    public function getTemplate()
    {
        return $this->libContainer[Template::class];
    }

    /**
     * Get the validator
     *
     * @return \Jaxon\Utils\Validation\Validator
     */
    public function getValidator()
    {
        return $this->libContainer[Validator::class];
    }

    /**
     * Get the paginator
     *
     * @return \Jaxon\Utils\Pagination\Paginator
     */
    public function getPaginator()
    {
        return $this->libContainer[Paginator::class];
    }

    /**
     * Get the event dispatcher
     *
     * @return Lemon\Event\EventDispatcher
     */
    public function getEventDispatcher()
    {
        return $this->libContainer[EventDispatcher::class];
    }

    /**
     * Get the global Response object
     *
     * @return \Jaxon\Response\Response
     */
    public function getResponse()
    {
        return $this->libContainer[Response::class];
    }

    /**
     * Create a new Jaxon response object
     *
     * @return \Jaxon\Response\Response
     */
    public function newResponse()
    {
        return new Response();
    }

    /**
     * Get the App instance
     *
     * @return \Jaxon\App\App
     */
    public function getApp()
    {
        return $this->libContainer[\Jaxon\App\App::class];
    }

    /**
     * Get the view manager
     *
     * @return ViewManager
     */
    public function getViewManager()
    {
        return $this->libContainer[ViewManager::class];
    }

    /**
     * Get the view facade
     *
     * @return ViewFacade
     */
    public function getViewFacade()
    {
        return $this->libContainer[ViewFacade::class];
    }

    /**
     * Get the session manager
     *
     * @return SessionContract
     */
    public function getSessionManager()
    {
        return $this->libContainer[SessionContract::class];
    }

    /**
     * Set the session manager
     *
     * @param Closure      $xClosure      A closure to create the session manager instance
     *
     * @return void
     */
    public function setSessionManager($xClosure)
    {
        $this->libContainer[SessionContract::class] = $xClosure;
    }
}
