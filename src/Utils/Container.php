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
        // Module
        $this->di['module'] = function($c){
            return new \Jaxon\Module\Module();
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
     * Get the Module object
     *
     * @return object        The Module object
     */
    public function getModule()
    {
        return $this->di['module'];
    }

    /**
     * Set the module
     *
     * @param object                $xModule            The new module
     *
     * @return void
     */
    public function setModule($xModule)
    {
        $this->di['module'] = $xModule;
    }

    /**
     * Get the view object
     *
     * @return object        The view object
     */
    public function getView()
    {
        return $this->di['module.view'];
    }

    /**
     * Set the view
     *
     * @param Closure               $xClosure           A closure to create the view instance
     *
     * @return void
     */
    public function setView($xClosure)
    {
        $this->di['module.view'] = $xClosure;
    }

    /**
     * Get the session object
     *
     * @return object        The session object
     */
    public function getSession()
    {
        return $this->di['module.session'];
    }

    /**
     * Set the session
     *
     * @param Closure               $xClosure           A closure to create the session instance
     *
     * @return void
     */
    public function setSession($xClosure)
    {
        $this->di['module.session'] = $xClosure;
    }
}
