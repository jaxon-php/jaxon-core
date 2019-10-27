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

namespace Jaxon\Utils;

use Lemon\Event\EventDispatcher;

use Jaxon\App\App;
use Jaxon\Response\Response;
use Jaxon\Config\Config;
use Jaxon\Config\Reader as ConfigReader;
use Jaxon\Request\Support\CallableRepository;
use Jaxon\Request\Plugin\CallableClass;
use Jaxon\Request\Plugin\CallableDir;
use Jaxon\Request\Plugin\CallableFunction;
use Jaxon\Request\Plugin\FileUpload;
use Jaxon\Request\Handler as RequestHandler;
use Jaxon\Request\Factory as RequestFactory;
use Jaxon\Request\Factory\CallableClass\Request as CallableClassRequestFactory;
use Jaxon\Request\Factory\CallableClass\Paginator as CallableClassPaginatorFactory;
use Jaxon\Request\Support\CallableObject;
use Jaxon\Response\Manager as ResponseManager;
use Jaxon\Response\Plugin\JQuery as JQueryPlugin;
use Jaxon\Plugin\Manager as PluginManager;
use Jaxon\Plugin\CodeGenerator;

use Jaxon\App\Bootstrap;
use Jaxon\Utils\View\Manager as ViewManager;
use Jaxon\Utils\View\Facade as ViewFacade;
use Jaxon\Utils\View\Renderer;
use Jaxon\Utils\Dialogs\Dialog;
use Jaxon\Utils\Template\Minifier;
use Jaxon\Utils\Template\Engine as TemplateEngine;
use Jaxon\Utils\Pagination\Paginator;
use Jaxon\Utils\Pagination\Renderer as PaginationRenderer;
use Jaxon\Utils\Validation\Validator;
use Jaxon\Utils\Translation\Translator;
use Jaxon\Contracts\Template\Renderer as TemplateRenderer;
use Jaxon\Contracts\Session as SessionContract;
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
        $this->libContainer[App::class] = function () {
            return new App();
        };
        // Jaxon App bootstrap
        $this->libContainer[Bootstrap::class] = function () {
            return new Bootstrap();
        };

        /*
         * Plugins
         */
        // Callable objects repository
        $this->libContainer[CallableRepository::class] = function () {
            return new CallableRepository();
        };
        // Callable class plugin
        $this->libContainer[CallableClass::class] = function ($c) {
            return new CallableClass($c[CallableRepository::class]);
        };
        // Callable dir plugin
        $this->libContainer[CallableDir::class] = function ($c) {
            return new CallableDir($c[CallableRepository::class]);
        };
        // Callable function plugin
        $this->libContainer[CallableFunction::class] = function () {
            return new CallableFunction();
        };
        // File upload plugin
        $this->libContainer[FileUpload::class] = function () {
            return new FileUpload();
        };
        // JQuery response plugin
        $this->libContainer[JQueryPlugin::class] = function () {
            return new JQueryPlugin();
        };

        /*
         * Managers
         */
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
            return new CodeGenerator($c[PluginManager::class], $c[TemplateEngine::class]);
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
        $this->libContainer[TemplateEngine::class] = function ($c) {
            return new TemplateEngine($c['jaxon.core.template_dir']);
        };
        // Template Renderer
        $this->libContainer[TemplateRenderer::class] = function ($c) {
            return $c[TemplateEngine::class];
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
            return new PaginationRenderer($c[TemplateRenderer::class]);
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
     * Set an alias
     *
     * @param string                $sClass             The class name
     * @param string                $sAlias             The alias name
     *
     * @return void
     */
    public function alias($sClass, $sAlias)
    {
        $this->libContainer[$sClass] = function ($c) use ($sAlias) {
            return $c[$sAlias];
        };
    }

    /**
     * Get the plugin manager
     *
     * @return PluginManager
     */
    public function getPluginManager()
    {
        return $this->libContainer[PluginManager::class];
    }

    /**
     * Get the request handler
     *
     * @return RequestHandler
     */
    public function getRequestHandler()
    {
        return $this->libContainer[RequestHandler::class];
    }

    /**
     * Get the request factory
     *
     * @return RequestFactory
     */
    public function getRequestFactory()
    {
        return $this->libContainer[RequestFactory::class];
    }

    /**
     * Get the response manager
     *
     * @return ResponseManager
     */
    public function getResponseManager()
    {
        return $this->libContainer[ResponseManager::class];
    }

    /**
     * Get the code generator
     *
     * @return CodeGenerator
     */
    public function getCodeGenerator()
    {
        return $this->libContainer[CodeGenerator::class];
    }

    /**
     * Get the callable repository
     *
     * @return CallableRepository
     */
    public function getCallableRepository()
    {
        return $this->libContainer[CallableRepository::class];
    }

    /**
     * Get the config manager
     *
     * @return Config
     */
    public function getConfig()
    {
        return $this->libContainer[Config::class];
    }

    /**
     * Create a new the config manager
     *
     * @param array             $aOptions           The options array
     * @param string            $sKeys              The keys of the options in the array
     *
     * @return Config            The config manager
     */
    public function newConfig(array $aOptions = [], $sKeys = '')
    {
        return new Config($aOptions, $sKeys);
    }

    /**
     * Get the dialog wrapper
     *
     * @return Dialog
     */
    public function getDialog()
    {
        return $this->libContainer[Dialog::class];
    }

    /**
     * Get the minifier
     *
     * @return Minifier
     */
    public function getMinifier()
    {
        return $this->libContainer[Minifier::class];
    }

    /**
     * Get the translator
     *
     * @return Translator
     */
    public function getTranslator()
    {
        return $this->libContainer[Translator::class];
    }

    /**
     * Get the template engine
     *
     * @return Engine
     */
    public function getTemplateEngine()
    {
        return $this->libContainer[TemplateEngine::class];
    }

    /**
     * Get the template renderer
     *
     * @return TemplateRenderer
     */
    public function getTemplateRenderer()
    {
        return $this->libContainer[TemplateRenderer::class];
    }

    /**
     * Get the validator
     *
     * @return Validator
     */
    public function getValidator()
    {
        return $this->libContainer[Validator::class];
    }

    /**
     * Get the paginator
     *
     * @return Paginator
     */
    public function getPaginator()
    {
        return $this->libContainer[Paginator::class];
    }

    /**
     * Get the event dispatcher
     *
     * @return EventDispatcher
     */
    public function getEventDispatcher()
    {
        return $this->libContainer[EventDispatcher::class];
    }

    /**
     * Get the global Response object
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->libContainer[Response::class];
    }

    /**
     * Create a new Jaxon response object
     *
     * @return Response
     */
    public function newResponse()
    {
        return new Response();
    }

    /**
     * Get the App instance
     *
     * @return App
     */
    public function getApp()
    {
        return $this->libContainer[App::class];
    }

    /**
     * Get the App bootstrap
     *
     * @return Bootstrap
     */
    public function getBootstrap()
    {
        return $this->libContainer[Bootstrap::class];
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
    public function getViewRenderer()
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

    /**
     * Set the callable class request factory
     *
     * @param string            $sClassName         The callable class name
     * @param CallableObject    $xCallableObject    The corresponding callable object
     *
     * @return void
     */
    public function setCallableClassRequestFactory($sClassName, CallableObject $xCallableObject)
    {
        $this->libContainer[$sClassName . '_RequestFactory'] = function () use ($xCallableObject) {
            // $xCallableObject = $c[CallableRepository::class]->getCallableObject($sClassName);
            return new CallableClassRequestFactory($xCallableObject);
        };
    }

    /**
     * Get the callable class request factory
     *
     * @param string        $sClassName             The callable class name
     *
     * @return CallableClassRequestFactory
     */
    public function getCallableClassRequestFactory($sClassName)
    {
        return $this->libContainer[$sClassName . '_RequestFactory'];
    }

    /**
     * Set the callable class paginator factory
     *
     * @param string            $sClassName         The callable class name
     * @param CallableObject    $xCallableObject    The corresponding callable object
     *
     * @return void
     */
    public function setCallableClassPaginatorFactory($sClassName, CallableObject $xCallableObject)
    {
        $this->libContainer[$sClassName . '_PaginatorFactory'] = function () use ($xCallableObject) {
            // $xCallableObject = $c[CallableRepository::class]->getCallableObject($sClassName);
            return new CallableClassPaginatorFactory($xCallableObject);
        };
    }

    /**
     * Get the callable class paginator factory
     *
     * @param string        $sClassName             The callable class name
     *
     * @return CallableClassPaginatorFactory
     */
    public function getCallableClassPaginatorFactory($sClassName)
    {
        return $this->libContainer[$sClassName . '_PaginatorFactory'];
    }
}
