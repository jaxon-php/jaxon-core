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
use Jaxon\Di\Container;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\Code\CodeGenerator;
use Jaxon\Plugin\Package;
use Jaxon\Utils\Config\Config;

use function is_array;
use function is_integer;
use function is_string;
use function is_subclass_of;
use function trim;

class PackageManager
{
    /**
     * @var Container
     */
    protected $di;

    /**
     * @var PluginManager
     */
    protected $xPluginManager;

    /**
     * @var ConfigManager
     */
    protected $xConfigManager;

    /**
     * @var CodeGenerator
     */
    private $xCodeGenerator;

    /**
     * @var ViewRenderer
     */
    protected $xViewRenderer;

    /**
     * @var Translator
     */
    protected $xTranslator;

    /**
     * The constructor
     *
     * @param Container $di
     * @param PluginManager $xPluginManager
     * @param ConfigManager $xConfigManager
     * @param CodeGenerator $xCodeGenerator
     * @param ViewRenderer $xViewRenderer
     * @param Translator $xTranslator
     */
    public function __construct(Container $di, PluginManager $xPluginManager, ConfigManager $xConfigManager,
        CodeGenerator $xCodeGenerator, ViewRenderer $xViewRenderer, Translator $xTranslator)
    {
        $this->di = $di;
        $this->xPluginManager = $xPluginManager;
        $this->xConfigManager = $xConfigManager;
        $this->xCodeGenerator = $xCodeGenerator;
        $this->xViewRenderer = $xViewRenderer;
        $this->xTranslator = $xTranslator;
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
     * Register callables from a section of the config
     *
     * @param array $aOptions    The content of the config section
     * @param string $sCallableType    The type of callable to register
     *
     * @return void
     * @throws SetupException
     */
    private function registerCallables(array $aOptions, string $sCallableType)
    {
        foreach($aOptions as $xKey => $xValue)
        {
            if(is_integer($xKey) && is_string($xValue))
            {
                // Register a function without options
                $this->xPluginManager->registerCallable($sCallableType, $xValue);
            }
            elseif(is_string($xKey) && (is_array($xValue) || is_string($xValue)))
            {
                // Register a function with options
                $this->xPluginManager->registerCallable($sCallableType, $xKey, $xValue);
            }
        }
    }

    /**
     * Read and set Jaxon options from a JSON config file
     *
     * @param Config $xConfig The config options
     * @param Config|null $xUserConfig The user provided package options
     *
     * @return void
     * @throws SetupException
     */
    private function registerItemsFromConfig(Config $xConfig, ?Config $xUserConfig = null)
    {
        // Register functions, classes and directories
        $this->registerCallables($xConfig->getOption('functions', []), Jaxon::CALLABLE_FUNCTION);
        $this->registerCallables($xConfig->getOption('classes', []), Jaxon::CALLABLE_CLASS);
        $this->registerCallables($xConfig->getOption('directories', []), Jaxon::CALLABLE_DIR);
        // Register the view namespaces
        // Note: the $xUserConfig can provide a "template" option, which is used to customize
        // the user defined view namespaces. That's why it is needed here.
        $this->xViewRenderer->addNamespaces($xConfig, $xUserConfig);
        // Save items in the DI container
        $this->updateContainer($xConfig);
    }

    /**
     * Get the options provided by the package library
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
     * @param array $aUserOptions    The user provided package options
     *
     * @return void
     * @throws SetupException
     */
    public function registerPackage(string $sClassName, array $aUserOptions)
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
        $xAppConfig = $this->xConfigManager->newConfig($aLibOptions);
        $xUserConfig = $this->xConfigManager->newConfig($aUserOptions);
        $this->di->registerPackage($sClassName, $xUserConfig);
        // Register the declarations in the package config.
        $this->registerItemsFromConfig($xAppConfig, $xUserConfig);
        // Register the package as a code generator.
        $this->xCodeGenerator->addCodeGenerator($sClassName, 500);
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
     * Read and set Jaxon options from the config
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
}
