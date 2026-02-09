<?php

/**
 * PackageManager.php - Jaxon package manager
 *
 * Register Jaxon plugins, packages and callables from a config file.
 *
 * @package jaxon-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2022 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link https://github.com/jaxon-php/jaxon-core
 */

namespace Jaxon\Plugin\Manager;

use Jaxon\Jaxon;
use Jaxon\App\Config\ConfigManager;
use Jaxon\App\I18n\Translator;
use Jaxon\App\View\ViewRenderer;
use Jaxon\Config\Config;
use Jaxon\Di\Container;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\AbstractPackage;
use Jaxon\Plugin\Request\CallableClass\ComponentRegistry;
use Jaxon\Request\Handler\CallbackManager;

use function is_array;
use function is_integer;
use function is_string;
use function is_subclass_of;
use function trim;

class PackageManager
{
    /**
     * The constructor
     *
     * @param Container $di
     * @param Translator $xTranslator
     * @param PluginManager $xPluginManager
     * @param ConfigManager $xConfigManager
     * @param ViewRenderer $xViewRenderer
     * @param CallbackManager $xCallbackManager
     * @param ComponentRegistry $xRegistry
     */
    public function __construct(private Container $di, private Translator $xTranslator,
        private PluginManager $xPluginManager, private ConfigManager $xConfigManager,
        private ViewRenderer $xViewRenderer, private CallbackManager $xCallbackManager,
        private ComponentRegistry $xRegistry)
    {}

    /**
     * Save items in the DI container
     *
     * @param Config $xConfig
     *
     * @return void
     */
    public function updateContainer(Config $xConfig): void
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
        $aOptions = $xConfig->getOption('container.extend', []);
        foreach($aOptions as $sClass => $xClosure)
        {
            // The service extensions are postponed until the DI is already setup.
            $this->xCallbackManager->addExtendedService($sClass, $xClosure);
        }
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
    private function registerCallables(array $aOptions, string $sCallableType): void
    {
        foreach($aOptions as $xKey => $xValue)
        {
            if(is_integer($xKey) && is_string($xValue))
            {
                // Register a function without options
                $this->xPluginManager->registerCallable($sCallableType, $xValue);
                continue;
            }

            if(is_string($xKey) && (is_array($xValue) || is_string($xValue)))
            {
                // Register a function with options
                $this->xPluginManager->registerCallable($sCallableType, $xKey, $xValue);
            }
        }
    }

    /**
     * Register exceptions handlers
     *
     * @param Config $xConfig
     *
     * @return void
     */
    private function registerExceptionHandlers(Config $xConfig): void
    {
        foreach($xConfig->getOption('exceptions', []) as $sExClass => $xExHandler)
        {
            $this->xCallbackManager->error($xExHandler, is_string($sExClass) ? $sExClass : '');
        }
    }

    /**
     * Get a callable list from config
     *
     * @param Config $xConfig
     * @param string $sOptionName
     * @param string $sOptionKey
     * @param string $sCallableType
     *
     * @return void
     */
    private function registerCallablesFromConfig(Config $xConfig,
        string $sOptionName, string $sOptionKey, string $sCallableType): void
    {
        // The callable (directory path, class or function name) can be used as the
        // key of the array item, a string as the value of an entry without a key,
        // or set with the key $sOptionKey in an array entry without a key.
        $aCallables = [];
        foreach($xConfig->getOption($sOptionName, []) as $xKey => $xValue)
        {
            if(is_string($xKey))
            {
                $aCallables[$xKey] = $xValue;
                continue;
            }
            if(is_string($xValue))
            {
                $aCallables[] = $xValue;
                continue;
            }

            if(is_array($xValue) && isset($xValue[$sOptionKey]))
            {
                $aCallables[$xValue[$sOptionKey]] = $xValue;
            }
            // Invalid values are ignored.
        }
        $this->registerCallables($aCallables, $sCallableType);
    }

    /**
     * Read and set Jaxon options from a JSON config file
     *
     * @param Config $xConfig The config options
     *
     * @return void
     * @throws SetupException
     */
    private function registerItemsFromConfig(Config $xConfig): void
    {
        // Set the config for the registered callables.
        $this->xRegistry->setPackageConfig($xConfig);

        // Register functions, classes and directories
        $this->registerCallablesFromConfig($xConfig,
            'functions', 'name', Jaxon::CALLABLE_FUNCTION);
        $this->registerCallablesFromConfig($xConfig,
            'classes', 'name', Jaxon::CALLABLE_CLASS);
        $this->registerCallablesFromConfig($xConfig,
            'directories', 'path', Jaxon::CALLABLE_DIR);

        // Unset the current config.
        $this->xRegistry->unsetPackageConfig();

        // Register the view namespaces
        // Note: the $xUserConfig can provide a "template" option, which is used to customize
        // the user defined view namespaces. That's why it is needed here.
        $this->xViewRenderer->addNamespaces($xConfig);
        // Save items in the DI container
        $this->updateContainer($xConfig);
        // Register the exception handlers
        $this->registerExceptionHandlers($xConfig);
    }

    /**
     * Get the options provided by the package library
     *
     * @param class-string $sClassName    The package class
     *
     * @return Config
     * @throws SetupException
     */
    private function getPackageLibConfig(string $sClassName): Config
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
        // Add the package name to the config
        $aLibOptions['package'] = $sClassName;
        return $this->xConfigManager->newConfig($aLibOptions);
    }

    /**
     * Register a package
     *
     * @param class-string $sClassName    The package class
     * @param array $aUserOptions    The user provided package options
     *
     * @return void
     * @throws SetupException
     */
    public function registerPackage(string $sClassName, array $aUserOptions = []): void
    {
        $sClassName = trim($sClassName, '\\ ');
        if(!is_subclass_of($sClassName, AbstractPackage::class))
        {
            $sMessage = $this->xTranslator->trans('errors.register.invalid', ['name' => $sClassName]);
            throw new SetupException($sMessage);
        }

        // Register the declarations in the package config.
        $xAppConfig = $this->getPackageLibConfig($sClassName);
        $this->registerItemsFromConfig($xAppConfig);

        // Register the package and its options in the DI
        $this->di->registerPackage($sClassName, $aUserOptions);

        // Register the package as a code generator.
        $this->xPluginManager->registerCodeGenerator($sClassName, 500);
    }

    /**
     * Get a package instance
     *
     * @template T of AbstractPackage
     * @param class-string<T> $sClassName    The package class name
     *
     * @return T|null
     */
    public function getPackage(string $sClassName): ?AbstractPackage
    {
        $sClassName = trim($sClassName, '\\ ');
        return $this->di->h($sClassName) ? $this->di->g($sClassName) : null;
    }

    /**
     * Read and set Jaxon options from the config
     *
     * @return void
     * @throws SetupException
     */
    public function registerFromConfig(): void
    {
        $xAppConfig = $this->xConfigManager->getAppConfig();
        $this->registerItemsFromConfig($xAppConfig);

        // Register packages
        $aPackageConfig = $xAppConfig->getOption('packages', []);
        foreach($aPackageConfig as $xKey => $xValue)
        {
            if(is_integer($xKey) && is_string($xValue))
            {
                // Register a package without options
                $sClassName = $xValue;
                $this->registerPackage($sClassName);
                continue;
            }

            if(is_string($xKey) && is_array($xValue))
            {
                // Register a package with options
                $sClassName = $xKey;
                $aPkgOptions = $xValue;
                $this->registerPackage($sClassName, $aPkgOptions);
            }
        }
    }
}
