<?php

/**
 * Container.php - Jaxon data container
 *
 * Provide container service for Jaxon utils class instances.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-2-Clause BSD 2-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Utils;

use Lemon\Event\EventDispatcher;
use Jaxon\Sentry\View\Renderer;

class Container
{
    // The Dependency Injection Container
    private $di = null;

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
        $this->di = new \Pimple\Container();

        $sTranslationDir = realpath(__DIR__ . '/../../translations');
        $sTemplateDir = realpath(__DIR__ . '/../../templates');
        $this->init($sTranslationDir, $sTemplateDir);
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
        $this->di['translation_dir'] = $sTranslationDir;
        // Template directory
        $this->di['template_dir'] = $sTemplateDir;

        /*
         * Managers
         */
        // Plugin Manager
        $this->di['plugin_manager'] = function($c){
            return new \Jaxon\Plugin\Manager();
        };
        // Request Manager
        $this->di['request_manager'] = function($c){
            return new \Jaxon\Request\Manager();
        };
        // Request Factory
        $this->di['request_factory'] = function($c){
            return new \Jaxon\Request\Factory();
        };
        // Response Manager
        $this->di['response_manager'] = function($c){
            return new \Jaxon\Response\Manager();
        };

        /*
         * Services
         */
        // Config manager
        $this->di['config'] = function($c){
            return new Config();
        };
        // Minifier
        $this->di['minifier'] = function($c){
            return new Minifier();
        };
        // Translator
        $this->di['translator'] = function($c){
            return new Translator($c['translation_dir'], $c['config']);
        };
        // Template engine
        $this->di['template'] = function($c){
            return new Template($c['template_dir']);
        };
        // Validator
        $this->di['validator'] = function($c){
            return new Validator();
        };
        // Paginator
        $this->di['paginator'] = function($c){
            return new Paginator(0, 1, 1, null);
        };
        // Event Dispatcher
        $this->di['events'] = function($c){
            return new EventDispatcher();
        };

        /*
         * Core library objects
         */
        // Global Response
        $this->di['response'] = function($c){
            return new \Jaxon\Response\Response();
        };
        // Jaxon Core
        $this->di['jaxon'] = function($c){
            return new \Jaxon\Jaxon();
        };
        // Jaxon Sentry
        $this->di['sentry'] = function($c){
            // This class is not defined in this package
            $sClass = '\\Jaxon\\Sentry\\Sentry';
            return new $sClass;
        };
        // Armada
        $this->di['armada'] = function($c){
            // This class is not defined in this package
            $sClass = '\\Jaxon\\Armada\\Armada';
            return new $sClass;
        };
        // View Renderer Facade
        $this->di['armada.view.renderer'] = function($c){
            $aRenderers = $c['view.data.renderers'];
            $sDefaultNamespace = $c['view.data.namespace.default'];
            return new \Jaxon\Sentry\View\Facade($aRenderers, $sDefaultNamespace);
        };
    }

    /**
     * Get the plugin manager
     *
     * @return object        The plugin manager
     */
    public function getPluginManager()
    {
        return $this->di['plugin_manager'];
    }

    /**
     * Get the request manager
     *
     * @return object        The request manager
     */
    public function getRequestManager()
    {
        return $this->di['request_manager'];
    }

    /**
     * Get the request factory
     *
     * @return object        The request factory
     */
    public function getRequestFactory()
    {
        return $this->di['request_factory'];
    }

    /**
     * Get the response manager
     *
     * @return object        The response manager
     */
    public function getResponseManager()
    {
        return $this->di['response_manager'];
    }

    /**
     * Get the config manager
     *
     * @return object        The config manager
     */
    public function getConfig()
    {
        return $this->di['config'];
    }

    /**
     * Get the minifier
     *
     * @return object        The minifier
     */
    public function getMinifier()
    {
        return $this->di['minifier'];
    }

    /**
     * Get the translator
     *
     * @return object        The translator
     */
    public function getTranslator()
    {
        return $this->di['translator'];
    }

    /**
     * Get the template engine
     *
     * @return object        The template engine
     */
    public function getTemplate()
    {
        return $this->di['template'];
    }

    /**
     * Get the validator
     *
     * @return object        The validator
     */
    public function getValidator()
    {
        return $this->di['validator'];
    }

    /**
     * Get the paginator
     *
     * @return object        The paginator
     */
    public function getPaginator()
    {
        return $this->di['paginator'];
    }

    /**
     * Get the event dispatcher
     *
     * @return object        The event dispatcher
     */
    public function getEventDispatcher()
    {
        return $this->di['events'];
    }

    /**
     * Get the Global Response object
     *
     * @return object        The Global Response object
     */
    public function getResponse()
    {
        return $this->di['response'];
    }

    /**
     * Create a new Jaxon response object
     *
     * @return \Jaxon\Response\Response        The new Jaxon response object
     */
    public function newResponse()
    {
        return new \Jaxon\Response\Response();
    }

    /**
     * Get the main Jaxon object
     *
     * @return object        The Jaxon object
     */
    public function getJaxon()
    {
        return $this->di['jaxon'];
    }

    /**
     * Get the Sentry instance
     *
     * @return object        The Sentry instance
     */
    public function getSentry()
    {
        return $this->di['sentry'];
    }

    /**
     * Get the Armada object
     *
     * @return object        The Armada object
     */
    public function getArmada()
    {
        return $this->di['armada'];
    }

    /**
     * Set the armada
     *
     * @param object                $xArmada            The new armada
     *
     * @return void
     */
    public function setArmada($xArmada)
    {
        $this->di['armada'] = $xArmada;
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
        $this->di['view.data.renderers'] = $aRenderers;
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
        $this->di['view.data.namespaces'] = $aNamespaces;
        $this->di['view.data.namespace.default'] = $sDefaultNamespace;
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
        $this->di['armada.view.base.' . $sId] = $xClosure;

        // Return the initialized view renderer
        $this->di['armada.view.' . $sId] = function($c) use($sId) {
            // Get the defined renderer
            $renderer = $c['armada.view.base.' . $sId];
            // Init the renderer with the template namespaces
            $aNamespaces = $this->di['view.data.namespaces'];
            if(key_exists($sId, $aNamespaces))
            {
                foreach($aNamespaces[$sId] as $namespace)
                {
                    $renderer->addNamespace($namespace['namespace'],
                        $namespace['directory'], $namespace['extension']);
                }
            }
            return $renderer;
        };
    }

    /**
     * Get the view object
     *
     * @param string                $sId                The unique identifier of the view renderer
     *
     * @return object        The view object
     */
    public function getViewRenderer($sId = '')
    {
        if(!$sId)
        {
            // Return the view renderer facade
            return $this->di['armada.view.renderer'];
        }
        // Return the view renderer with the given id
        return $this->di['armada.view.' . $sId];
    }

    /**
     * Get the session object
     *
     * @return object        The session object
     */
    public function getSessionManager()
    {
        return $this->di['armada.session'];
    }

    /**
     * Set the session
     *
     * @param Closure               $xClosure           A closure to create the session instance
     *
     * @return void
     */
    public function setSessionManager($xClosure)
    {
        $this->di['armada.session'] = $xClosure;
    }
}
