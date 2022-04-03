<?php

namespace Jaxon\Di\Traits;

use Jaxon\Jaxon;
use Jaxon\Config\ConfigManager;
use Jaxon\Di\Container;
use Jaxon\Plugin\Code\AssetManager;
use Jaxon\Plugin\Code\CodeGenerator;
use Jaxon\Plugin\Code\MinifierInterface;
use Jaxon\Plugin\Manager\PackageManager;
use Jaxon\Plugin\Manager\PluginManager;
use Jaxon\Request\Handler\ParameterReader;
use Jaxon\Response\Plugin\DataBag\DataBagPlugin;
use Jaxon\Response\Plugin\JQuery\JQueryPlugin;
use Jaxon\Ui\View\ViewManager;
use Jaxon\Utils\File\FileMinifier;
use Jaxon\Utils\Template\TemplateEngine;
use Jaxon\Utils\Translation\Translator;

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
        $this->set(PluginManager::class, function($c) {
            return new PluginManager($c->g(Container::class), $c->g(CodeGenerator::class), $c->g(Translator::class));
        });
        // Package manager
        $this->set(PackageManager::class, function($c) {
            return new PackageManager($c->g(Container::class), $c->g(PluginManager::class), $c->g(ConfigManager::class),
                $c->g(ViewManager::class), $c->g(CodeGenerator::class), $c->g(Translator::class));
        });
        // Code Generation
        $this->set(MinifierInterface::class, function() {
            return new class extends FileMinifier implements MinifierInterface {};
        });
        $this->set(AssetManager::class, function($c) {
            return new AssetManager($c->g(ConfigManager::class), $c->g(ParameterReader::class),
                $c->g(MinifierInterface::class));
        });
        $this->set(CodeGenerator::class, function($c) {
            $sVersion = $c->g(Jaxon::class)->getVersion();
            return new CodeGenerator($sVersion, $c->g(Container::class), $c->g(TemplateEngine::class));
        });
        // JQuery response plugin
        $this->set(JQueryPlugin::class, function($c) {
            $jQueryNs = $c->g(ConfigManager::class)->getOption('core.jquery.no_conflict', false) ? 'jQuery' : '$';
            return new JQueryPlugin($jQueryNs);
        });
        // DataBagPlugin response plugin
        $this->set(DataBagPlugin::class, function($c) {
            return new DataBagPlugin($c->g(Container::class));
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
}
