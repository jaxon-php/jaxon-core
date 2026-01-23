<?php

namespace Jaxon\Di\Traits;

use Jaxon\Jaxon;
use Jaxon\App\Config\ConfigManager;
use Jaxon\App\Dialog\Manager\DialogCommand;
use Jaxon\App\I18n\Translator;
use Jaxon\App\Pagination\PaginationRenderer;
use Jaxon\App\View\ViewRenderer;
use Jaxon\Config\Config;
use Jaxon\Di\Container;
use Jaxon\Exception\SetupException;
use Jaxon\Plugin\AbstractPackage;
use Jaxon\Plugin\Code\AssetManager;
use Jaxon\Plugin\Code\CodeGenerator;
use Jaxon\Plugin\Code\ConfigScriptGenerator;
use Jaxon\Plugin\Code\MinifierInterface;
use Jaxon\Plugin\Code\ReadyScriptGenerator;
use Jaxon\Plugin\Manager\PackageManager;
use Jaxon\Plugin\Manager\PluginManager;
use Jaxon\Plugin\Request\CallableClass\ComponentRegistry;
use Jaxon\Plugin\Response\Databag\DatabagPlugin;
use Jaxon\Plugin\Response\Dialog\DialogPlugin;
use Jaxon\Plugin\Response\Script\ScriptPlugin;
use Jaxon\Request\Handler\CallbackManager;
use Jaxon\Request\Handler\ParameterReader;
use Jaxon\Script\CallFactory;
use Jaxon\Storage\StorageManager;
use Jaxon\Utils\File\FileMinifier;
use Jaxon\Utils\Template\TemplateEngine;
use Pimple\Container as PimpleContainer;
use Closure;

use function call_user_func;

trait PluginTrait
{
    /**
     * The library Dependency Injection Container
     *
     * @var PimpleContainer
     */
    private $xLibContainer;

    /**
     * Register the values into the container
     *
     * @return void
     */
    private function registerPlugins(): void
    {
        // Plugin manager
        $this->set(PluginManager::class, function($di) {
            $xPluginManager = new PluginManager($di->g(Container::class),
                $di->g(CodeGenerator::class), $di->g(Translator::class));
            // Register the Jaxon request and response plugins
            $xPluginManager->registerPlugins();
            return $xPluginManager;
        });
        // Package manager
        $this->set(PackageManager::class, fn($di) =>
            new PackageManager($di->g(Container::class), $di->g(Translator::class),
                $di->g(PluginManager::class), $di->g(ConfigManager::class),
                $di->g(ViewRenderer::class), $di->g(CallbackManager::class),
                $di->g(ComponentRegistry::class)));
        // Code Generation
        $this->set(MinifierInterface::class, fn()=>
            new class extends FileMinifier implements MinifierInterface {});
        $this->set(AssetManager::class, fn($di) =>
            new AssetManager($di->g(ConfigManager::class),
                $di->g(StorageManager::class), $di->g(MinifierInterface::class)));
        $this->set(CodeGenerator::class, fn($di) =>
            new CodeGenerator(Jaxon::VERSION, $di->g(Container::class),
                $di->g(TemplateEngine::class), $di->g(ConfigManager::class)));
        $this->set(ConfigScriptGenerator::class, fn($di) =>
            new ConfigScriptGenerator($di->g(ParameterReader::class),
                $di->g(TemplateEngine::class), $di->g(ConfigManager::class)));
        $this->set(ReadyScriptGenerator::class, fn() => new ReadyScriptGenerator());

        // Script response plugin
        $this->set(ScriptPlugin::class, fn($di) => new ScriptPlugin($di->g(CallFactory::class)));
        // Databag response plugin. Get the databag contents from the HTTP request parameters.
        $this->set(DatabagPlugin::class, fn($di) =>
            new DatabagPlugin(fn() => $di->getRequest()->getAttribute('jxnbags', [])));
        // Dialog response plugin
        $this->set(DialogPlugin::class, fn($di) =>
            new DialogPlugin($di->g(DialogCommand::class)));
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
     * Get the storage manager
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
     * @param class-string $sClassName    The package class name
     *
     * @return string
     */
    private function getPackageConfigKey(string $sClassName): string
    {
        return $sClassName . '_PackageConfig';
    }

    /**
     * @param class-string $sClassName    The package class name
     * @param-closure-this AbstractPackage $cSetter
     *
     * @return void
     */
    private function extendPackage(string $sClassName, Closure $cSetter): void
    {
        // Initialize the package instance.
        $this->xLibContainer->extend($sClassName, function($xPackage) use($cSetter) {
            // Allow the setter to access protected attributes.
            call_user_func($cSetter->bindTo($xPackage, $xPackage));
            return $xPackage;
        });
    }

    /**
     * Register a package
     *
     * @param class-string $sClassName    The package class name
     * @param array $aUserOptions    The user provided package options
     *
     * @return void
     * @throws SetupException
     */
    public function registerPackage(string $sClassName, array $aUserOptions): void
    {
        // Register the user class, but only if the user didn't already.
        if(!$this->h($sClassName))
        {
            $this->set($sClassName, fn() => $this->make($sClassName));
        }

        // Save the package config in the container.
        $sConfigKey = $this->getPackageConfigKey($sClassName);
        $this->set($sConfigKey, function($di) use($aUserOptions) {
            $xOptionsProvider = $aUserOptions['provider'] ?? null;
            // The user can provide a callable that returns the package options.
            if(is_callable($xOptionsProvider))
            {
                $aUserOptions = $xOptionsProvider($aUserOptions);
            }
            return $di->g(ConfigManager::class)->newConfig($aUserOptions);
        });

        // Initialize the package instance.
        $di = $this;
        $this->extendPackage($sClassName, function() use($di, $sConfigKey) {
            // $this here refers to the AbstractPackage instance.
            $this->xPkgConfig = $di->g($sConfigKey);
            $this->xRenderer = $di->g(ViewRenderer::class);
            $this->init();
        });
    }

    /**
     * Get the config of a package
     *
     * @param class-string $sClassName    The package class name
     *
     * @return Config
     */
    public function getPackageConfig(string $sClassName): Config
    {
        return $this->g($this->getPackageConfigKey($sClassName));
    }
}
