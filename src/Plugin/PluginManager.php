<?php

/**
 * ResponseManager.php - Jaxon plugin manager
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

use Jaxon\Jaxon;
use Jaxon\Plugin\Code\CodeGenerator;
use Jaxon\Request\Plugin\CallableClass\CallableClassPlugin;
use Jaxon\Request\Plugin\CallableClass\CallableDirPlugin;
use Jaxon\Request\Plugin\CallableFunction\CallableFunctionPlugin;
use Jaxon\Response\Plugin\DataBag\DataBagPlugin;
use Jaxon\Response\Plugin\JQuery\JQueryPlugin;
use Jaxon\Response\Response;
use Jaxon\Ui\Dialogs\MessageInterface;
use Jaxon\Ui\Dialogs\QuestionInterface;
use Jaxon\Utils\Config\Config;
use Jaxon\Utils\Translation\Translator;
use Jaxon\Exception\SetupException;

use function class_implements;
use function in_array;
use function is_array;
use function is_integer;
use function is_string;
use function is_subclass_of;
use function trim;

class PluginManager
{
    /**
     * @var Jaxon
     */
    private $jaxon;

    /**
     * @var Config
     */
    protected $xConfig;

    /**
     * @var Translator
     */
    protected $xTranslator;

    /**
     * Request plugins, indexed by name
     *
     * @var array
     */
    private $aRegistryPlugins = [];

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
     * @param Jaxon $jaxon
     * @param Config $xConfig
     * @param Translator $xTranslator
     * @param CodeGenerator $xCodeGenerator
     */
    public function __construct(Jaxon $jaxon, Config $xConfig, Translator $xTranslator, CodeGenerator $xCodeGenerator)
    {
        $this->jaxon = $jaxon;
        $this->xConfig = $xConfig;
        $this->xTranslator = $xTranslator;
        $this->xCodeGenerator = $xCodeGenerator;
    }

    /**
     * Get the request plugins
     *
     * @return array
     */
    public function getRequestPlugins(): array
    {
        return $this->aRequestPlugins;
    }

    /**
     * Get a package instance
     *
     * @param string $sClassName    The package class name
     *
     * @return Package|null
     */
    public function getPackage(string $sClassName): ?Package
    {
        return $this->jaxon->di()->get(trim($sClassName, '\\ '));
    }

    /**
     * Register a plugin
     *
     * Below is a table for priorities and their description:
     * - 0 to 999: Plugins that are part of or extensions to the jaxon core
     * - 1000 to 8999: User created plugins, typically, these plugins don't care about order
     * - 9000 to 9999: Plugins that generally need to be last or near the end of the plugin list
     *
     * @param string $sClassName    The plugin class
     * @param string $sPluginName    The plugin name
     * @param integer $nPriority    The plugin priority, used to order the plugins
     *
     * @return void
     * @throws SetupException
     */
    public function registerPlugin(string $sClassName, string $sPluginName, int $nPriority = 1000)
    {
        $bIsUsed = false;
        $aInterfaces = class_implements($sClassName);
        if(in_array(CodeGeneratorInterface::class, $aInterfaces))
        {
            $this->xCodeGenerator->addGenerator($sClassName, $nPriority);
            $bIsUsed = true;
        }
        if(in_array(CallableRegistryInterface::class, $aInterfaces))
        {
            $this->aRegistryPlugins[$sPluginName] = $sClassName;
            $bIsUsed = true;
        }
        if(in_array(RequestHandlerInterface::class, $aInterfaces))
        {
            $this->aRequestPlugins[$sPluginName] = $sClassName;
            $bIsUsed = true;
        }
        if(in_array(ResponsePluginInterface::class, $aInterfaces))
        {
            $this->aResponsePlugins[$sPluginName] = $sClassName;
            $bIsUsed = true;
        }

        // This plugin implements the Message interface
        if(in_array(MessageInterface::class, $aInterfaces))
        {
            $this->jaxon->dialog()->setMessage($sClassName);
            $bIsUsed = true;
        }
        // This plugin implements the Question interface
        if(in_array(QuestionInterface::class, $aInterfaces))
        {
            $this->jaxon->dialog()->setQuestion($sClassName);
            $bIsUsed = true;
        }

        if(!$bIsUsed)
        {
            // The class is invalid.
            $sMessage = $this->xTranslator->trans('errors.register.invalid', ['name' => $sClassName]);
            throw new SetupException($sMessage);
        }

        // Register the plugin in the DI container, if necessary
        if(!$this->jaxon->di()->has($sClassName))
        {
            $this->jaxon->di()->auto($sClassName);
        }
    }

    /**
     * Register a package
     *
     * @param string $sClassName    The package class name
     *
     * @return Config
     * @throws SetupException
     */
    private function readPackageConfig(string $sClassName): Config
    {
        $sConfigFile = $sClassName::getConfigFile();
        $aPackageConfig = $this->jaxon->readConfig($sConfigFile);
        // Add the package name to the config
        $aPackageConfig['package'] = $sClassName;
        return $this->jaxon->di()->newConfig($aPackageConfig);
    }

    /**
     * Register a package
     *
     * @param string $sClassName    The package class name
     * @param array $aAppOptions    The package options defined in the app section of the config file
     *
     * @return void
     * @throws SetupException
     */
    public function registerPackage(string $sClassName, array $aAppOptions)
    {
        $sClassName = trim($sClassName, '\\ ');
        if(!is_subclass_of($sClassName, Package::class))
        {
            $sMessage = $this->xTranslator->trans('errors.register.invalid', ['name' => $sClassName]);
            throw new SetupException($sMessage);
        }
        $di = $this->jaxon->di();
        $xAppConfig = $di->registerPackage($sClassName, $aAppOptions);

        // Read the package config.
        $xPackageConfig = $this->readPackageConfig($sClassName);

        // Register the declarations in the package config.
        $this->_registerFromConfig($xPackageConfig);

        // Register the view namespaces
        $di->getViewManager()->addNamespaces($xPackageConfig, $xAppConfig);

        // Register the package as a code generator.
        $this->xCodeGenerator->addGenerator($sClassName, 500);
    }

    /**
     * Register a function or callable class
     *
     * Call the request plugin with the $sType defined as name.
     *
     * @param string $sType    The type of request handler being registered
     * @param string $sCallable    The callable entity being registered
     * @param array|string $xOptions    The associated options
     *
     * @return void
     * @throws SetupException
     */
    public function registerCallable(string $sType, string $sCallable, $xOptions = [])
    {
        if(isset($this->aRegistryPlugins[$sType]) &&
            ($xPlugin = $this->jaxon->di()->g($this->aRegistryPlugins[$sType])))
        {
            $xPlugin->register($sType, $sCallable, $xPlugin->checkOptions($sCallable, $xOptions));
            return;
        }
        throw new SetupException($this->xTranslator->trans('errors.register.plugin',
            ['name' => $sType, 'callable' => $sCallable]));
    }

    /**
     * Register callables from a section of the config
     *
     * @param Config $xAppConfig    The config options
     * @param string $sSection    The config section name
     * @param string $sCallableType    The type of callable to register
     *
     * @return void
     * @throws SetupException
     */
    private function registerCallablesFromConfig(Config $xAppConfig, string $sSection, string $sCallableType)
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
     * @param Config $xAppConfig    The config options
     *
     * @return void
     * @throws SetupException
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
        $di = $this->jaxon->di();
        $aContainerConfig = $xAppConfig->getOption('container', []);
        foreach($aContainerConfig as $sClassName => $xClosure)
        {
            $di->set($sClassName, $xClosure);
        }
    }

    /**
     * Read and set Jaxon options from a JSON config file
     *
     * @param Config $xAppConfig    The config options
     *
     * @return void
     * @throws SetupException
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
     * @param string $sName    The name of the plugin
     * @param Response|null $xResponse    The response to attach the plugin to
     *
     * @return ResponsePlugin|null
     */
    public function getResponsePlugin(string $sName, ?Response $xResponse = null): ?ResponsePlugin
    {
        if(!isset($this->aResponsePlugins[$sName]))
        {
            return null;
        }
        $xPlugin = $this->jaxon->di()->g($this->aResponsePlugins[$sName]);
        if(($xResponse))
        {
            $xPlugin->setResponse($xResponse);
        }
        return $xPlugin;
    }

    /**
     * Find the specified request plugin by name and return a reference to it if one exists
     *
     * @param string $sName    The name of the plugin
     *
     * @return RequestPlugin|null
     */
    public function getRequestPlugin(string $sName): ?RequestPlugin
    {
        if(!isset($this->aRequestPlugins[$sName]))
        {
            return null;
        }
        return $this->jaxon->di()->g($this->aRequestPlugins[$sName]);
    }

    /**
     * Register the Jaxon request plugins
     *
     * @return void
     * @throws SetupException
     */
    public function registerPlugins()
    {
        // Request plugins
        $this->registerPlugin(CallableClassPlugin::class, Jaxon::CALLABLE_CLASS, 101);
        $this->registerPlugin(CallableFunctionPlugin::class, Jaxon::CALLABLE_FUNCTION, 102);
        $this->registerPlugin(CallableDirPlugin::class, Jaxon::CALLABLE_DIR, 103);

        // Register the JQuery response plugin
        $this->registerPlugin(JQueryPlugin::class, 'jquery', 700);
        // Register the DataBag response plugin
        $this->registerPlugin(DataBagPlugin::class, 'bags', 700);
    }
}
