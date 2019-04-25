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
         * Managers
         */
        // Plugin Manager
        $this->coreContainer['jaxon.core.plugin_manager'] = function ($c) {
            return new \Jaxon\Plugin\Manager();
        };
        // Request Manager
        $this->coreContainer['jaxon.core.request_manager'] = function ($c) {
            return new \Jaxon\Request\Manager();
        };
        // Request Factory
        $this->coreContainer['jaxon.core.request_factory'] = function ($c) {
            return new \Jaxon\Factory\Request();
        };
        // Parameter Factory
        $this->coreContainer['jaxon.core.parameter_factory'] = function ($c) {
            return new \Jaxon\Factory\Parameter();
        };
        // Response Manager
        $this->coreContainer['jaxon.core.response_manager'] = function ($c) {
            return new \Jaxon\Response\Manager();
        };

        /*
         * Services
         */
        // Config manager
        $this->coreContainer['jaxon.core.config'] = function ($c) {
            return new \Jaxon\Utils\Config\Config();
        };
        // Minifier
        $this->coreContainer['jaxon.core.minifier'] = function ($c) {
            return new \Jaxon\Utils\Template\Minifier();
        };
        // Translator
        $this->coreContainer['jaxon.core.translator'] = function ($c) {
            return new \Jaxon\Utils\Translation\Translator($c['jaxon.core.translation_dir'], $c['jaxon.core.config']);
        };
        // Template engine
        $this->coreContainer['jaxon.core.template'] = function ($c) {
            return new \Jaxon\Utils\Template\Template($c['jaxon.core.template_dir']);
        };
        // Validator
        $this->coreContainer['jaxon.core.validator'] = function ($c) {
            return new \Jaxon\Utils\Validation\Validator($c['jaxon.core.translator'], $c['jaxon.core.config']);
        };
        // Pagination Renderer
        $this->coreContainer['jaxon.pagination.renderer'] = function ($c) {
            return new \Jaxon\Utils\Pagination\Renderer();
        };
        // Pagination Paginator
        $this->coreContainer['jaxon.pagination.paginator'] = function ($c) {
            return new \Jaxon\Utils\Pagination\Paginator($c['jaxon.pagination.renderer']);
        };
        // Event Dispatcher
        $this->coreContainer['jaxon.core.events'] = function ($c) {
            return new EventDispatcher();
        };

        /*
         * Core library objects
         */
        // Global Response
        $this->coreContainer['jaxon.core.response'] = function ($c) {
            return new \Jaxon\Response\Response();
        };
        // Jaxon Core
        $this->coreContainer['jaxon.core.jaxon'] = function ($c) {
            return new \Jaxon\Jaxon();
        };
        // View Renderer Facade
        $this->coreContainer['jaxon.sentry.view.renderer'] = function ($c) {
            $aRenderers = $c['jaxon.view.data.renderers'];
            $sDefaultNamespace = $c['jaxon.view.data.namespace.default'];
            return new \Jaxon\Sentry\View\Facade($aRenderers, $sDefaultNamespace);
        };
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
        return $this->coreContainer['jaxon.core.plugin_manager'];
    }

    /**
     * Get the request manager
     *
     * @return Jaxon\Request\Manager
     */
    public function getRequestManager()
    {
        return $this->coreContainer['jaxon.core.request_manager'];
    }

    /**
     * Get the request factory
     *
     * @return Jaxon\Factory\Request
     */
    public function getRequestFactory()
    {
        return $this->coreContainer['jaxon.core.request_factory'];
    }

    /**
     * Get the parameter factory
     *
     * @return Jaxon\Factory\Parameter
     */
    public function getParameterFactory()
    {
        return $this->coreContainer['jaxon.core.parameter_factory'];
    }

    /**
     * Get the response manager
     *
     * @return Jaxon\Response\Manager
     */
    public function getResponseManager()
    {
        return $this->coreContainer['jaxon.core.response_manager'];
    }

    /**
     * Get the config manager
     *
     * @return Jaxon\Utils\Config\Config
     */
    public function getConfig()
    {
        return $this->coreContainer['jaxon.core.config'];
    }

    /**
     * Create a new the config manager
     *
     * @return Jaxon\Utils\Config\Config            The config manager
     */
    public function newConfig()
    {
        return new \Jaxon\Utils\Config\Config();
    }

    /**
     * Get the minifier
     *
     * @return Jaxon\Utils\Template\Minifier
     */
    public function getMinifier()
    {
        return $this->coreContainer['jaxon.core.minifier'];
    }

    /**
     * Get the translator
     *
     * @return Jaxon\Utils\Translation\Translator
     */
    public function getTranslator()
    {
        return $this->coreContainer['jaxon.core.translator'];
    }

    /**
     * Get the template engine
     *
     * @return Jaxon\Utils\Template\Template
     */
    public function getTemplate()
    {
        return $this->coreContainer['jaxon.core.template'];
    }

    /**
     * Get the validator
     *
     * @return Jaxon\Utils\Validation\Validator
     */
    public function getValidator()
    {
        return $this->coreContainer['jaxon.core.validator'];
    }

    /**
     * Get the paginator
     *
     * @return Jaxon\Utils\Pagination\Paginator
     */
    public function getPaginator()
    {
        return $this->coreContainer['jaxon.pagination.paginator'];
    }

    /**
     * Set the pagination renderer
     *
     * @param Jaxon\Utils\Pagination\Renderer  $xRenderer    The pagination renderer
     *
     * @return void
     */
    public function setPaginationRenderer($xRenderer)
    {
        $this->coreContainer['jaxon.pagination.renderer'] = $xRenderer;
    }

    /**
     * Get the event dispatcher
     *
     * @return Lemon\Event\EventDispatcher
     */
    public function getEventDispatcher()
    {
        return $this->coreContainer['jaxon.core.events'];
    }

    /**
     * Get the global Response object
     *
     * @return Jaxon\Response\Response
     */
    public function getResponse()
    {
        return $this->coreContainer['jaxon.core.response'];
    }

    /**
     * Create a new Jaxon response object
     *
     * @return Jaxon\Response\Response
     */
    public function newResponse()
    {
        return new \Jaxon\Response\Response();
    }

    /**
     * Get the main Jaxon object
     *
     * @return Jaxon\Jaxon
     */
    public function getJaxon()
    {
        return $this->coreContainer['jaxon.core.jaxon'];
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
            return $this->coreContainer['jaxon.sentry.view.renderer'];
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
