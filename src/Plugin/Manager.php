<?php

/**
 * Manager.php - Jaxon plugin manager
 *
 * Register Jaxon plugins, generate corresponding code, handle request
 * and redirect them to the right plugin.
 *
 * @package jaxon-core
 * @author Jared White
 * @author J. Max Wilson
 * @author Joseph Woolley
 * @author Steffen Konerow
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
 * @copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Plugin;

use Jaxon\Exception\Error;
use Jaxon\Jaxon;
use Jaxon\Plugin\Code\Generator as CodeGenerator;
use Jaxon\Request\Plugin\CallableClass;
use Jaxon\Request\Plugin\CallableDir;
use Jaxon\Request\Plugin\CallableFunction;
use Jaxon\Request\Plugin\FileUpload;
use Jaxon\Response\Plugin\JQuery as JQueryPlugin;
use Jaxon\Response\Plugin\DataBag;
use Jaxon\Utils\Config\Config;

use Closure;
use Jaxon\Utils\Config\Exception\File;
use Jaxon\Utils\Config\Exception\Yaml;

class Manager
{
    use \Jaxon\Features\Manager;
    use \Jaxon\Features\Config;
    use \Jaxon\Features\Event;
    use \Jaxon\Features\Translator;

    /**
     * Request plugins, indexed by name
     *
     * @var array
     */
    private $aRequestPlugins = [];

    /**
     * Response plugins, indexed by name
     *
     * @var array
     */
    private $aResponsePlugins = [];

    /**
     * The code generator
     *
     * @var CodeGenerator
     */
    private $xCodeGenerator;

    /**
     * The constructor
     *
     * @param CodeGenerator     $xCodeGenerator
     */
    public function __construct(CodeGenerator $xCodeGenerator)
    {
        $this->xCodeGenerator = $xCodeGenerator;
    }

    /**
     * Get the request plugins
     *
     * @return array
     */
    public function getRequestPlugins()
    {
        return $this->aRequestPlugins;
    }

    /**
     * Get the response plugins
     *
     * @return array
     */
    public function getResponsePlugins()
    {
        return $this->aResponsePlugins;
    }

    /**
     * Get a package instance
     *
     * @param string        $sClassName           The package class name
     *
     * @return Package
     */
    public function getPackage($sClassName)
    {
        $sClassName = trim($sClassName, '\\ ');
        return jaxon()->di()->get($sClassName);
    }

    /**
     * Register a plugin
     *
     * Below is a table for priorities and their description:
     * - 0 thru 999: Plugins that are part of or extensions to the jaxon core
     * - 1000 thru 8999: User created plugins, typically, these plugins don't care about order
     * - 9000 thru 9999: Plugins that generally need to be last or near the end of the plugin list
     *
     * @param Plugin $xPlugin An instance of a plugin
     * @param integer $nPriority The plugin priority, used to order the plugins
     *
     * @return void
     * @throws Error
     */
    public function registerPlugin(Plugin $xPlugin, $nPriority = 1000)
    {
        $bIsUsed = false;
        if($xPlugin instanceof Request)
        {
            // The name of a request plugin is used as key in the plugin table
            $this->aRequestPlugins[$xPlugin->getName()] = $xPlugin;
            $this->xCodeGenerator->addGenerator($xPlugin, $nPriority);
            $bIsUsed = true;
        }
        elseif($xPlugin instanceof Response)
        {
            // The name of a response plugin is used as key in the plugin table
            $this->aResponsePlugins[$xPlugin->getName()] = $xPlugin;
            $this->xCodeGenerator->addGenerator($xPlugin, $nPriority);
            $bIsUsed = true;
        }

        // This plugin implements the Message interface
        if($xPlugin instanceof \Jaxon\Contracts\Dialogs\Message)
        {
            jaxon()->dialog()->setMessage($xPlugin);
            $bIsUsed = true;
        }
        // This plugin implements the Question interface
        if($xPlugin instanceof \Jaxon\Contracts\Dialogs\Question)
        {
            jaxon()->dialog()->setQuestion($xPlugin);
            $bIsUsed = true;
        }
        // Register the plugin as an event listener
        if($xPlugin instanceof \Jaxon\Contracts\Event\Listener)
        {
            $this->addEventListener($xPlugin);
            $bIsUsed = true;
        }

        if(!$bIsUsed)
        {
            $sErrorMessage = $this->trans('errors.register.invalid', ['name' => get_class($xPlugin)]);
            throw new Error($sErrorMessage);
        }
    }

    /**
     * Register a package
     *
     * @param string $sClassName The package class name
     * @param array $aAppOptions The package options defined in the app section of the config file
     *
     * @return void
     * @throws Error
     * @throws File
     * @throws Yaml
     */
    public function registerPackage($sClassName, array $aAppOptions)
    {
        $sClassName = trim($sClassName, '\\ ');
        $jaxon = jaxon();
        $di = $jaxon->di();

        $xAppConfig = $di->newConfig($aAppOptions);
        $di->set($sClassName, function($di) use($sClassName, $aAppOptions, $xAppConfig) {
            $xPackage = $di->make($sClassName);
            // Set the package options
            $cSetter = function($aOptions, $xConfig) {
                $this->aOptions = $aOptions;
                $this->xConfig = $xConfig;
            };
            // Can now access protected attributes
            \call_user_func($cSetter->bindTo($xPackage, $xPackage), $aAppOptions, $xAppConfig);
            return $xPackage;
        });

        // Read and apply the package config.
        $aPackageConfig = $jaxon->config()->read($sClassName::getConfigFile());
        // Add the package name to the config
        $aPackageConfig['package'] = $sClassName;

        $xPackageConfig = $di->newConfig($aPackageConfig);
        $this->_registerFromConfig($xPackageConfig);
        // Register the view namespaces
        $di->getViewManager()->addNamespaces($xPackageConfig, $xAppConfig);
        // Register the package as a code generator.
        $xPackage = $this->getPackage($sClassName);
        $this->xCodeGenerator->addGenerator($xPackage, 500);
    }

    /**
     * Register a function or callable class
     *
     * Call the request plugin with the $sType defined as name.
     *
     * @param string $sType The type of request handler being registered
     * @param string $sCallable The callable entity being registered
     * @param array|string $aOptions The associated options
     *
     * @return void
     * @throws Error
     */
    public function registerCallable($sType, $sCallable, $aOptions = [])
    {
        if(!key_exists($sType, $this->aRequestPlugins))
        {
            throw new Error($this->trans('errors.register.plugin', ['name' => $sType]));
        }

        $xPlugin = $this->aRequestPlugins[$sType];
        $xPlugin->register($sType, $sCallable, $aOptions);
    }

    /**
     * Register callables from a section of the config
     *
     * @param Config $xAppConfig The config options
     * @param string $sSection The config section name
     * @param string $sCallableType The type of callable to register
     *
     * @return void
     * @throws Error
     */
    private function registerCallablesFromConfig(Config $xAppConfig, $sSection, $sCallableType)
    {
        $aConfig = $xAppConfig->getOption($sSection, []);
        foreach($aConfig as $xKey => $xValue)
        {
            if(is_integer($xKey) && is_string($xValue))
            {
                // Register a function without options
                $this->registerCallable($sCallableType, $xValue);
            }
            elseif(is_string($xKey) && (is_array($xValue) || is_string($xValue)))
            {
                // Register a function with options
                $this->registerCallable($sCallableType, $xKey, $xValue);
            }
            else
            {
                continue;
                // Todo: throw an exception
            }
        }
    }

    /**
     * Read and set Jaxon options from a JSON config file
     *
     * @param Config $xAppConfig The config options
     *
     * @return void
     * @throws Error
     */
    private function _registerFromConfig(Config $xAppConfig)
    {
        // Register functions
        $this->registerCallablesFromConfig($xAppConfig, 'functions', Jaxon::CALLABLE_FUNCTION);

        // Register classes
        $this->registerCallablesFromConfig($xAppConfig, 'classes', Jaxon::CALLABLE_CLASS);

        // Register directories
        $this->registerCallablesFromConfig($xAppConfig, 'directories', Jaxon::CALLABLE_DIR);

        // Register classes in DI container
        $di = jaxon()->di();
        $aContainerConfig = $xAppConfig->getOption('container', []);
        foreach($aContainerConfig as $sClassName => $xClosure)
        {
            $di->set($sClassName, $xClosure);
        }
    }

    /**
     * Read and set Jaxon options from a JSON config file
     *
     * @param Config $xAppConfig The config options
     *
     * @return void
     * @throws Error
     */
    public function registerFromConfig(Config $xAppConfig)
    {
        $this->_registerFromConfig($xAppConfig);

        // Register packages
        $aPackageConfig = $xAppConfig->getOption('packages', []);
        foreach($aPackageConfig as $sClassName => $aOptions)
        {
            $this->registerPackage($sClassName, $aOptions);
        }
    }


    /**
     * Find the specified response plugin by name and return a reference to it if one exists
     *
     * @param string        $sName                The name of the plugin
     *
     * @return Response
     */
    public function getResponsePlugin($sName)
    {
        if(array_key_exists($sName, $this->aResponsePlugins))
        {
            return $this->aResponsePlugins[$sName];
        }
        return null;
    }

    /**
     * Find the specified request plugin by name and return a reference to it if one exists
     *
     * @param string        $sName                The name of the plugin
     *
     * @return Request
     */
    public function getRequestPlugin($sName)
    {
        if(array_key_exists($sName, $this->aRequestPlugins))
        {
            return $this->aRequestPlugins[$sName];
        }
        return null;
    }

    /**
     * Register the Jaxon request plugins
     *
     * @return void
     * @throws Error
     */
    public function registerRequestPlugins()
    {
        $di = jaxon()->di();
        $this->registerPlugin($di->get(CallableClass::class), 101);
        $this->registerPlugin($di->get(CallableDir::class), 102);
        $this->registerPlugin($di->get(CallableFunction::class), 103);
        $this->registerPlugin($di->get(FileUpload::class), 104);
    }

    /**
     * Register the Jaxon response plugins
     *
     * @return void
     * @throws Error
     */
    public function registerResponsePlugins()
    {
        $di = jaxon()->di();
        // Register an instance of the JQuery plugin
        $this->registerPlugin($di->get(JQueryPlugin::class), 700);
        // Register an instance of the DataBag plugin
        $this->registerPlugin($di->get(DataBag::class), 700);
    }
}
