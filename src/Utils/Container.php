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
    }

    /**
     * Set the parameters and create the objects in the dependency injection container
     *
     * @param string        $sTranslationDir    The translation directory
     * @param string        $sTemplateDir        The template directory
     *
     * @return void
     */
    public function init($sTranslationDir, $sTemplateDir)
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
        $this->di['plugin'] = function($c){
            return new \Jaxon\Plugin\Manager();
        };
        // Request Manager
        $this->di['request'] = function($c){
            return new \Jaxon\Request\Manager();
        };
        // Response Manager
        $this->di['response'] = function($c){
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
    }

    /**
     * Get the plugin manager
     *
     * @return object        The plugin manager
     */
    public function getPluginManager()
    {
        return $this->di['plugin'];
    }

    /**
     * Get the request manager
     *
     * @return object        The request manager
     */
    public function getRequestManager()
    {
        return $this->di['request'];
    }

    /**
     * Get the response manager
     *
     * @return object        The response manager
     */
    public function getResponseManager()
    {
        return $this->di['response'];
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
}
