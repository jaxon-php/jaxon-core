<?php

namespace Jaxon\Di\Traits;

use Jaxon\Jaxon;
use Jaxon\App\Config\ConfigManager;
use Jaxon\App\Dialog\Library\DialogLibraryManager;
use Jaxon\App\I18n\Translator;
use Jaxon\App\View\ViewRenderer;
use Jaxon\Di\Container;
use Jaxon\Plugin\Code\AssetManager;
use Jaxon\Plugin\Code\CodeGenerator;
use Jaxon\Plugin\Code\MinifierInterface;
use Jaxon\Plugin\Manager\PackageManager;
use Jaxon\Plugin\Manager\PluginManager;
use Jaxon\Plugin\Response\DataBag\DataBagPlugin;
use Jaxon\Plugin\Response\Dialog\DialogPlugin;
use Jaxon\Plugin\Response\JQuery\JQueryPlugin;
use Jaxon\Request\Handler\CallbackManager;
use Jaxon\Request\Handler\ParameterReader;
use Jaxon\Utils\File\FileMinifier;
use Jaxon\Utils\Template\TemplateEngine;

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
            return new PackageManager($di->g(Container::class), $di->g(PluginManager::class),
                $di->g(ConfigManager::class), $di->g(CallbackManager::class),
                $di->g(CodeGenerator::class), $di->g(ViewRenderer::class), $di->g(Translator::class));
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

        // JQuery response plugin
        $this->set(JQueryPlugin::class, function($di) {
            $jQueryNs = $di->g(ConfigManager::class)->getOption('core.jquery.no_conflict', false) ? 'jQuery' : '$';
            return new JQueryPlugin($jQueryNs);
        });
        // DataBag response plugin
        $this->set(DataBagPlugin::class, function($di) {
            return new DataBagPlugin($di->g(Container::class));
        });
        // Dialog response plugin
        $this->set(DialogPlugin::class, function($di) {
            return new DialogPlugin($di->g(DialogLibraryManager::class));
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
     * @return JQueryPlugin
     */
    public function getJQueryPlugin(): JQueryPlugin
    {
        return $this->g(JQueryPlugin::class);
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
}
