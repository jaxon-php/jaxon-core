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

namespace Jaxon\Plugin\Manager;

use Jaxon\Jaxon;
use Jaxon\Config\ConfigManager;
use Jaxon\Di\Container;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\Code\CodeGenerator;
use Jaxon\Plugin\Contract\CallableRegistryInterface;
use Jaxon\Plugin\Contract\CodeGeneratorInterface;
use Jaxon\Plugin\Contract\RequestHandlerInterface;
use Jaxon\Plugin\Contract\ResponsePluginInterface;
use Jaxon\Plugin\Package;
use Jaxon\Plugin\RequestPlugin;
use Jaxon\Plugin\ResponsePlugin;
use Jaxon\Request\Plugin\CallableClass\CallableClassPlugin;
use Jaxon\Request\Plugin\CallableClass\CallableDirPlugin;
use Jaxon\Request\Plugin\CallableFunction\CallableFunctionPlugin;
use Jaxon\Response\Plugin\DataBag\DataBagPlugin;
use Jaxon\Response\Plugin\JQuery\JQueryPlugin;
use Jaxon\Response\Response;
use Jaxon\Ui\Dialogs\MessageInterface;
use Jaxon\Ui\Dialogs\QuestionInterface;
use Jaxon\Ui\View\ViewManager;
use Jaxon\Utils\Config\Config;
use Jaxon\Utils\Translation\Translator;

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
     * @var Container
     */
    protected $di;

    /**
     * @var Translator
     */
    protected $xTranslator;

    /**
     * @var ConfigManager
     */
    protected $xConfigManager;

    /**
     * @var ViewManager
     */
    protected $xViewManager;

    /**
     * The code generator
     *
     * @var CodeGenerator
     */
    private $xCodeGenerator;

    /**
     * Request plugins, indexed by name
     *
     * @var array<CallableRegistryInterface>
     */
    private $aRegistryPlugins = [];

    /**
     * Request handlers, indexed by name
     *
     * @var array<RequestHandlerInterface>
     */
    private $aRequestHandlers = [];

    /**
     * Response plugins, indexed by name
     *
     * @var array<ResponsePluginInterface>
     */
    private $aResponsePlugins = [];

    /**
     * The constructor
     *
     * @param Container $di
     * @param ConfigManager $xConfigManager
     * @param ViewManager $xViewManager
     * @param CodeGenerator $xCodeGenerator
     * @param Translator $xTranslator
     */
    public function __construct(Container $di, ConfigManager $xConfigManager,
        ViewManager $xViewManager, CodeGenerator $xCodeGenerator, Translator $xTranslator)
    {
        $this->di = $di;
        $this->xConfigManager = $xConfigManager;
        $this->xViewManager = $xViewManager;
        $this->xCodeGenerator = $xCodeGenerator;
        $this->xTranslator = $xTranslator;
    }

    /**
     * Get the request plugins
     *
     * @return array<RequestPlugin>
     */
    public function getRequestHandlers(): array
    {
        return $this->aRequestHandlers;
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
            $this->aRequestHandlers[$sPluginName] = $sClassName;
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
            $this->di->getDialog()->setMessage($sClassName);
            $bIsUsed = true;
        }
        // This plugin implements the Question interface
        if(in_array(QuestionInterface::class, $aInterfaces))
        {
            $this->di->getDialog()->setQuestion($sClassName);
            $bIsUsed = true;
        }

        if(!$bIsUsed)
        {
            // The class is invalid.
            $sMessage = $this->xTranslator->trans('errors.register.invalid', ['name' => $sClassName]);
            throw new SetupException($sMessage);
        }

        // Register the plugin in the DI container, if necessary
        if(!$this->di->has($sClassName))
        {
            $this->di->auto($sClassName);
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
        $xPlugin = $this->di->g($this->aResponsePlugins[$sName]);
        if(($xResponse))
        {
            $xPlugin->setResponse($xResponse);
        }
        return $xPlugin;
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
            ($xPlugin = $this->di->g($this->aRegistryPlugins[$sType])))
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
     * @param array $aOptions    The content of the config section
     * @param string $sCallableType    The type of callable to register
     *
     * @return void
     * @throws SetupException
     */
    private function registerCallablesFromOptions(array $aOptions, string $sCallableType)
    {
        foreach($aOptions as $xKey => $xValue)
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
        }
    }

    /**
     * Save items in the DI container
     *
     * @param Config $xConfig
     *
     * @return void
     */
    private function updateContainer(Config $xConfig)
    {
        $aOptions = $xConfig->getOption('container.set', []);
        foreach($aOptions as $xKey => $xValue)
        {
            // The key is the class name. It must be a string.
            $this->di->set((string)$xKey, $xValue);
        }
        $aOptions = $xConfig->getOption('container.val', []);
        foreach($aOptions as $xKey => $xValue)
        {
            // The key is the class name. It must be a string.
            $this->di->val((string)$xKey, $xValue);
        }
        $aOptions = $xConfig->getOption('container.auto', []);
        foreach($aOptions as $xValue)
        {
            // The key is the class name. It must be a string.
            $this->di->auto((string)$xValue);
        }
        $aOptions = $xConfig->getOption('container.alias', []);
        foreach($aOptions as $xKey => $xValue)
        {
            // The key is the class name. It must be a string.
            $this->di->alias((string)$xKey, (string)$xValue);
        }
    }

    /**
     * Read and set Jaxon options from a JSON config file
     *
     * @param Config $xConfig The config options
     * @param Config|null $xPkgConfig The user provided package options
     *
     * @return void
     * @throws SetupException
     */
    private function registerItemsFromConfig(Config $xConfig, ?Config $xPkgConfig = null)
    {
        $aSections = [
            'functions' => Jaxon::CALLABLE_FUNCTION,
            'classes' => Jaxon::CALLABLE_CLASS,
            'directories' => Jaxon::CALLABLE_DIR,
        ];
        // Register functions, classes and directories
        foreach($aSections as $sSection => $sCallableType)
        {
            $this->registerCallablesFromOptions($xConfig->getOption($sSection, []), $sCallableType);
        }
        // Register the view namespaces
        // Note: the $xPkgConfig can provide a "template" option, which is used to customize
        // the user defined view namespaces. That's why it is needed here.
        $this->xViewManager->addNamespaces($xConfig, $xPkgConfig);
        // Save items in the DI container
        $this->updateContainer($xConfig);
    }

    /**
     * Get package options
     *
     * @param string $sClassName    The package class
     *
     * @return array
     * @throws SetupException
     */
    private function getPackageOptions(string $sClassName): array
    {
        // $this->aPackages contains packages config file paths.
        $aLibOptions = $sClassName::config();
        if(is_string($aLibOptions))
        {
            // A string is supposed to be the path to a config file.
            $aLibOptions = $this->xConfigManager->read($aLibOptions);
        }
        elseif(!is_array($aLibOptions))
        {
            // Otherwise, anything else than an array is not accepted.
            $sMessage = $this->xTranslator->trans('errors.register.invalid', ['name' => $sClassName]);
            throw new SetupException($sMessage);
        }
        return $aLibOptions;
    }

    /**
     * Register a package
     *
     * @param string $sClassName    The package class
     * @param array $aPkgOptions    The user provided package options
     *
     * @return void
     * @throws SetupException
     */
    public function registerPackage(string $sClassName, array $aPkgOptions)
    {
        $sClassName = trim($sClassName, '\\ ');
        if(!is_subclass_of($sClassName, Package::class))
        {
            $sMessage = $this->xTranslator->trans('errors.register.invalid', ['name' => $sClassName]);
            throw new SetupException($sMessage);
        }
        $aLibOptions = $this->getPackageOptions($sClassName);
        // Add the package name to the config
        $aLibOptions['package'] = $sClassName;
        $xLibConfig = $this->xConfigManager->newConfig($aLibOptions);
        $xPkgConfig = $this->xConfigManager->newConfig($aPkgOptions);
        $this->di->registerPackage($sClassName, $xPkgConfig);
        // Register the declarations in the package config.
        $this->registerItemsFromConfig($xLibConfig, $xPkgConfig);
        // Register the package as a code generator.
        $this->xCodeGenerator->addGenerator($sClassName, 500);
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
        $sClassName = trim($sClassName, '\\ ');
        return $this->di->h($sClassName) ? $this->di->g($sClassName) : null;
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
        $this->registerItemsFromConfig($xAppConfig);

        // Register packages
        $aPackageConfig = $xAppConfig->getOption('packages', []);
        foreach($aPackageConfig as $sClassName => $aPkgOptions)
        {
            $this->registerPackage($sClassName, $aPkgOptions);
        }
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
        $this->registerPlugin(JQueryPlugin::class, JQueryPlugin::NAME, 700);
        // Register the DataBag response plugin
        $this->registerPlugin(DataBagPlugin::class, DataBagPlugin::NAME, 700);
    }
}
