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
use Jaxon\Sentry\View\Renderer;

use Jaxon\Jaxon;
use Jaxon\Response\Response;
use Jaxon\Config\Config;
use Jaxon\Config\Reader as ConfigReader;
use Jaxon\Request\Support\CallableRepository;
use Jaxon\Request\Handler as RequestHandler;
use Jaxon\Response\Manager as ResponseManager;
use Jaxon\Plugin\Manager as PluginManager;
use Jaxon\Plugin\CodeGenerator;
use Jaxon\Factory\Request as RequestFactory;
use Jaxon\Factory\Parameter as ParameterFactory;
use Jaxon\Dialog\Dialog;
use Jaxon\Utils\Template\Minifier;
use Jaxon\Utils\Translation\Translator;
use Jaxon\Utils\Template\Template;
use Jaxon\Utils\Validation\Validator;
use Jaxon\Utils\Pagination\Paginator;
use Jaxon\Utils\Pagination\Renderer as PaginationRenderer;

class Container
{
    // The Dependency Injection Container
    private $coreContainer = null;

    // The Dependency Injection Container
    private $sentryContainer = null;

    // The only instance of the Container (Singleton)
    private static $xInstance = null;

    public static function getInstance()
    {
        if(!self::$xInstance)
        {
            self::$xInstance = new Container();
        }
        return self::$xInstance;
    }

    private function __construct()
    {
        $this->coreContainer = new \Pimple\Container();

        $sTranslationDir = realpath(__DIR__ . '/../../translations');
        $sTemplateDir = realpath(__DIR__ . '/../../templates');
        $this->init($sTranslationDir, $sTemplateDir);
    }

    /**
     * Get the container provided by the integrated framework
     *
     * @return ContainerInterface
     */
    public function getSentryContainer()
    {
        return $this->sentryContainer;
    }

    /**
     * Set the container provided by the integrated framework
     *
     * @param ContainerInterface  $container     The container implementation
     *
     * @return void
     */
    public function setSentryContainer(ContainerInterface $container)
    {
        $this->sentryContainer = $container;
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
        $this->coreContainer['jaxon.core.translation_dir'] = $sTranslationDir;
        // Template directory
        $this->coreContainer['jaxon.core.template_dir'] = $sTemplateDir;

        /*
         * Core library objects
         */
        // Jaxon Core
        $this->coreContainer[Jaxon::class] = function () {
            return new Jaxon();
        };
        // Global Response
        $this->coreContainer[Response::class] = function () {
            return new Response();
        };
        // Dialog
        $this->coreContainer[Dialog::class] = function () {
            return new Dialog();
        };

        /*
         * Managers
         */
        // Callable objects repository
        $this->coreContainer[CallableRepository::class] = function () {
            return new CallableRepository();
        };
        // Plugin Manager
        $this->coreContainer[PluginManager::class] = function () {
            return new PluginManager();
        };
        // Request Manager
        $this->coreContainer[RequestHandler::class] = function ($c) {
            return new RequestHandler($c[PluginManager::class]);
        };
        // Request Factory
        $this->coreContainer[RequestFactory::class] = function ($c) {
            return new RequestFactory($c[CallableRepository::class]);
        };
        // Parameter Factory
        $this->coreContainer[ParameterFactory::class] = function () {
            return new ParameterFactory();
        };
        // Response Manager
        $this->coreContainer[ResponseManager::class] = function () {
            return new ResponseManager();
        };
        // Code Generator
        $this->coreContainer[CodeGenerator::class] = function ($c) {
            return new CodeGenerator($c[PluginManager::class]);
        };

        /*
         * Config
         */
        $this->coreContainer[Config::class] = function () {
            return new Config();
        };
        $this->coreContainer[ConfigReader::class] = function () {
            return new ConfigReader();
        };

        /*
         * Services
         */
        // Minifier
        $this->coreContainer[Minifier::class] = function () {
            return new Minifier();
        };
        // Translator
        $this->coreContainer[Translator::class] = function ($c) {
            return new Translator($c['jaxon.core.translation_dir'], $c[Config::class]);
        };
        // Template engine
        $this->coreContainer[Template::class] = function ($c) {
            return new Template($c['jaxon.core.template_dir']);
        };
        // Validator
        $this->coreContainer[Validator::class] = function ($c) {
            return new Validator($c[Translator::class], $c[Config::class]);
        };
        // Pagination Renderer
        $this->coreContainer[PaginationRenderer::class] = function ($c) {
            return new PaginationRenderer($c[Template::class]);
        };
        // Pagination Paginator
        $this->coreContainer[Paginator::class] = function ($c) {
            return new Paginator($c[PaginationRenderer::class]);
        };
        // Event Dispatcher
        $this->coreContainer[EventDispatcher::class] = function () {
            return new EventDispatcher();
        };

        // View Renderer Facade
        // $this->coreContainer[\Jaxon\Sentry\View\Facade::class] = function ($c) {
        //     $aRenderers = $c['jaxon.view.data.renderers'];
        //     $sDefaultNamespace = $c['jaxon.view.data.namespace.default'];
        //     return new \Jaxon\Sentry\View\Facade($aRenderers, $sDefaultNamespace);
        // };
    }

    /**
     * Get a class instance
     *
     * @return object        The class instance
     */
    public function get($sClass)
    {
        if($this->sentryContainer != null && $this->sentryContainer->has($sClass))
        {
            return $this->sentryContainer->get($sClass);
        }
        return $this->coreContainer[$sClass];
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
        $this->coreContainer[$sClass] = $xClosure;
    }

    /**
     * Get the plugin manager
     *
     * @return Jaxon\Plugin\Manager
     */
    public function getPluginManager()
    {
        return $this->coreContainer[PluginManager::class];
    }

    /**
     * Get the request handler
     *
     * @return Jaxon\Request\Handler
     */
    public function getRequestHandler()
    {
        return $this->coreContainer[RequestHandler::class];
    }

    /**
     * Get the request factory
     *
     * @return Jaxon\Factory\Request
     */
    public function getRequestFactory()
    {
        return $this->coreContainer[RequestFactory::class];
    }

    /**
     * Get the parameter factory
     *
     * @return Jaxon\Factory\Parameter
     */
    public function getParameterFactory()
    {
        return $this->coreContainer[ParameterFactory::class];
    }

    /**
     * Get the response manager
     *
     * @return Jaxon\Response\Manager
     */
    public function getResponseManager()
    {
        return $this->coreContainer[ResponseManager::class];
    }

    /**
     * Get the code generator
     *
     * @return Jaxon\Code\Generator
     */
    public function getCodeGenerator()
    {
        return $this->coreContainer[CodeGenerator::class];
    }

    /**
     * Get the config manager
     *
     * @return Jaxon\Config\Config
     */
    public function getConfig()
    {
        return $this->coreContainer[Config::class];
    }

    /**
     * Create a new the config manager
     *
     * @return Jaxon\Config\Config            The config manager
     */
    public function newConfig()
    {
        return new \Jaxon\Config\Config();
    }

    /**
     * Get the dialog wrapper
     *
     * @return Jaxon\Dialog\Config
     */
    public function getDialog()
    {
        return $this->coreContainer[Dialog::class];
    }

    /**
     * Get the minifier
     *
     * @return Jaxon\Utils\Template\Minifier
     */
    public function getMinifier()
    {
        return $this->coreContainer[Minifier::class];
    }

    /**
     * Get the translator
     *
     * @return Jaxon\Utils\Translation\Translator
     */
    public function getTranslator()
    {
        return $this->coreContainer[Translator::class];
    }

    /**
     * Get the template engine
     *
     * @return Jaxon\Utils\Template\Template
     */
    public function getTemplate()
    {
        return $this->coreContainer[Template::class];
    }

    /**
     * Get the validator
     *
     * @return Jaxon\Utils\Validation\Validator
     */
    public function getValidator()
    {
        return $this->coreContainer[Validator::class];
    }

    /**
     * Get the paginator
     *
     * @return Jaxon\Utils\Pagination\Paginator
     */
    public function getPaginator()
    {
        return $this->coreContainer[Paginator::class];
    }

    /**
     * Set the pagination renderer
     *
     * @param Jaxon\Utils\Pagination\Renderer  $xRenderer    The pagination renderer
     *
     * @return void
     */
    public function setPaginationRenderer(PaginationRenderer $xRenderer)
    {
        $this->coreContainer[PaginationRenderer::class] = $xRenderer;
    }

    /**
     * Get the event dispatcher
     *
     * @return Lemon\Event\EventDispatcher
     */
    public function getEventDispatcher()
    {
        return $this->coreContainer[EventDispatcher::class];
    }

    /**
     * Get the global Response object
     *
     * @return Jaxon\Response\Response
     */
    public function getResponse()
    {
        return $this->coreContainer[Response::class];
    }

    /**
     * Create a new Jaxon response object
     *
     * @return Jaxon\Response\Response
     */
    public function newResponse()
    {
        return new Response();
    }

    /**
     * Get the main Jaxon object
     *
     * @return Jaxon\Jaxon
     */
    public function getJaxon()
    {
        return $this->coreContainer[Jaxon::class];
    }

    /**
     * Get the Jaxon library version number
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->getJaxon()->getVersion();
    }

    /**
     * Get the Sentry instance
     *
     * @return Jaxon\Sentry\Sentry
     */
    public function getSentry()
    {
        return $this->coreContainer['jaxon.sentry'];
    }

    /**
     * Set the Sentry instance
     *
     * @param Jaxon\Sentry\Sentry     $xSentry            The Sentry instance
     *
     * @return void
     */
    public function setSentry($xSentry)
    {
        $this->coreContainer['jaxon.sentry'] = $xSentry;
    }

    /**
     * Get the Armada instance
     *
     * @return Jaxon\Armada\Armada
     */
    public function getArmada()
    {
        return $this->coreContainer['jaxon.armada'];
    }

    /**
     * Set the Armada instance
     *
     * @param Jaxon\Armada\Armada     $xArmada            The Armada instance
     *
     * @return void
     */
    public function setArmada($xArmada)
    {
        $this->coreContainer['jaxon.armada'] = $xArmada;
    }

    /**
     * Set the view renderers data
     *
     * @param array                $aRenderers          Array of renderer names with namespace as key
     *
     * @return void
     */
    public function initViewRenderers($aRenderers)
    {
        $this->coreContainer['jaxon.view.data.renderers'] = $aRenderers;
    }

    /**
     * Set the view namespaces data
     *
     * @param array                $aNamespaces         Array of namespaces with renderer name as key
     *
     * @return void
     */
    public function initViewNamespaces($aNamespaces, $sDefaultNamespace)
    {
        $this->coreContainer['jaxon.view.data.namespaces'] = $aNamespaces;
        $this->coreContainer['jaxon.view.data.namespace.default'] = $sDefaultNamespace;
    }

    /**
     * Add a view renderer
     *
     * @param string                $sId                The unique identifier of the view renderer
     * @param Closure               $xClosure           A closure to create the view instance
     *
     * @return void
     */
    public function addViewRenderer($sId, $xClosure)
    {
        // Return the non-initialiazed view renderer
        $this->coreContainer['jaxon.sentry.view.base.' . $sId] = $xClosure;

        // Return the initialized view renderer
        $this->coreContainer['jaxon.sentry.view.' . $sId] = function ($c) use ($sId) {
            // Get the defined renderer
            $renderer = $c['jaxon.sentry.view.base.' . $sId];
            // Init the renderer with the template namespaces
            $aNamespaces = $this->coreContainer['jaxon.view.data.namespaces'];
            if(key_exists($sId, $aNamespaces))
            {
                foreach($aNamespaces[$sId] as $ns)
                {
                    $renderer->addNamespace($ns['namespace'], $ns['directory'], $ns['extension']);
                }
            }
            return $renderer;
        };
    }

    /**
     * Get the view renderer
     *
     * @param string                $sId                The unique identifier of the view renderer
     *
     * @return Jaxon\Sentry\Interfaces\View
     */
    public function getViewRenderer($sId = '')
    {
        if(!$sId)
        {
            // Return the view renderer facade
            return $this->coreContainer[\Jaxon\Sentry\View\Facade::class];
        }
        // Return the view renderer with the given id
        return $this->coreContainer['jaxon.sentry.view.' . $sId];
    }

    /**
     * Get the session object
     *
     * @return Jaxon\Sentry\Interfaces\Session
     */
    public function getSessionManager()
    {
        return $this->coreContainer['jaxon.armada.session'];
    }

    /**
     * Set the session
     *
     * @param Closure      $xClosure      A closure to create the session instance
     *
     * @return void
     */
    public function setSessionManager($xClosure)
    {
        $this->coreContainer['jaxon.armada.session'] = $xClosure;
    }
}
