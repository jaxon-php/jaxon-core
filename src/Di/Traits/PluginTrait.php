<?php

namespace Jaxon\Di\Traits;

use Jaxon\Jaxon;
use Jaxon\App\Config\ConfigManager;
use Jaxon\App\Dialog\DialogManager;
use Jaxon\App\Dialog\Library\DialogLibraryManager;
use Jaxon\App\I18n\Translator;
use Jaxon\App\View\ViewRenderer;
use Jaxon\Di\Container;
use Jaxon\Exception\SetupException;
use Jaxon\Script\Factory\CallFactory;
use Jaxon\Plugin\Code\AssetManager;
use Jaxon\Plugin\Code\CodeGenerator;
use Jaxon\Plugin\Code\MinifierInterface;
use Jaxon\Plugin\Manager\PackageManager;
use Jaxon\Plugin\Manager\PluginManager;
use Jaxon\Plugin\Request\CallableClass\CallableRegistry;
use Jaxon\Plugin\Response\DataBag\DataBagPlugin;
use Jaxon\Plugin\Response\Dialog\DialogPlugin;
use Jaxon\Plugin\Response\Script\ScriptPlugin;
use Jaxon\Request\Handler\CallbackManager;
use Jaxon\Request\Handler\ParameterReader;
use Jaxon\Utils\Config\Config;
use Jaxon\Utils\File\FileMinifier;
use Jaxon\Utils\Template\TemplateEngine;

use function call_user_func;

trait PluginTrait
{
    /**
     * Register the values into the container
     *
     * @return void
     */
    private function registerPlugins()
    {
        // Plugin manager
        $this->set(PluginManager::class, function($di) {
            return new PluginManager($di->g(Container::class),
                $di->g(CodeGenerator::class), $di->g(Translator::class));
        });
        // Package manager
        $this->set(PackageManager::class, function($di) {
            return new PackageManager($di->g(Container::class), $di->g(Translator::class),
                $di->g(PluginManager::class), $di->g(ConfigManager::class),
                $di->g(CodeGenerator::class), $di->g(ViewRenderer::class),
                $di->g(CallbackManager::class), $di->g(CallableRegistry::class));
        });
        // Code Generation
        $this->set(MinifierInterface::class, function() {
            return new class extends FileMinifier implements MinifierInterface
            {};
        });
        $this->set(AssetManager::class, function($di) {
            return new AssetManager($di->g(ConfigManager::class), $di->g(ParameterReader::class),
                $di->g(MinifierInterface::class));
        });
        $this->set(CodeGenerator::class, function($di) {
            return new CodeGenerator(Jaxon::VERSION, $di->g(Container::class), $di->g(TemplateEngine::class));
        });

        // Script response plugin
        $this->set(ScriptPlugin::class, function($di) {
            return new ScriptPlugin($di->g(CallFactory::class));
        });
        // DataBag response plugin
        $this->set(DataBagPlugin::class, function($di) {
            return new DataBagPlugin($di->g(Container::class));
        });
        // Dialog response plugin
        $this->set(DialogPlugin::class, function($di) {
            return new DialogPlugin($di->g(DialogManager::class), $di->g(DialogLibraryManager::class));
        });
    }

    /**
     * Get the plugin manager
     *
     * @return PluginManager
     */
    public function getPluginManager(): PluginManager
    {
        return $this->g(PluginManager::class);
    }

    /**
     * Get the package manager
     *
     * @return PackageManager
     */
    public function getPackageManager(): PackageManager
    {
        return $this->g(PackageManager::class);
    }

    /**
     * Get the code generator
     *
     * @return CodeGenerator
     */
    public function getCodeGenerator(): CodeGenerator
    {
        return $this->g(CodeGenerator::class);
    }

    /**
     * Get the asset manager
     *
     * @return AssetManager
     */
    public function getAssetManager(): AssetManager
    {
        return $this->g(AssetManager::class);
    }

    /**
     * Get the jQuery plugin
     *
     * @return ScriptPlugin
     */
    public function getScriptPlugin(): ScriptPlugin
    {
        return $this->g(ScriptPlugin::class);
    }

    /**
     * Get the dialog plugin
     *
     * @return DialogPlugin
     */
    public function getDialogPlugin(): DialogPlugin
    {
        return $this->g(DialogPlugin::class);
    }

    /**
     * @param string $sClassName    The package class name
     *
     * @return string
     */
    private function getPackageConfigKey(string $sClassName): string
    {
        return $sClassName . '_PackageConfig';
    }

    /**
     * Register a package
     *
     * @param string $sClassName    The package class name
     * @param Config $xPkgConfig    The user provided package options
     *
     * @return void
     * @throws SetupException
     */
    public function registerPackage(string $sClassName, Config $xPkgConfig)
    {
        // Register the user class, but only if the user didn't already.
        if(!$this->h($sClassName))
        {
            $this->set($sClassName, function() use($sClassName) {
                return $this->make($sClassName);
            });
        }

        // Save the package config in the container.
        $this->val($this->getPackageConfigKey($sClassName), $xPkgConfig);

        // Initialize the package instance.
        $this->xLibContainer->extend($sClassName, function($xPackage) use($sClassName) {
            $xPkgConfig = $this->getPackageConfig($sClassName);
            $xViewRenderer = $this->g(ViewRenderer::class);
            $cSetter = function() use($xPkgConfig, $xViewRenderer) {
                // Set the protected attributes of the Package instance.
                $this->xPkgConfig = $xPkgConfig;
                $this->xRenderer = $xViewRenderer;
                $this->init();
            };
            // Can now access protected attributes
            call_user_func($cSetter->bindTo($xPackage, $xPackage));
            return $xPackage;
        });
    }

    /**
     * Get the config of a package
     *
     * @param string $sClassName    The package class name
     *
     * @return Config
     */
    public function getPackageConfig(string $sClassName): Config
    {
        return $this->g($this->getPackageConfigKey($sClassName));
    }
}
