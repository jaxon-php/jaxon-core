<?php

namespace Jaxon\Container\Traits;

use Jaxon\Jaxon;
use Jaxon\Plugin\Code\AssetManager;
use Jaxon\Plugin\Code\CodeGenerator;
use Jaxon\Plugin\PluginManager;
use Jaxon\Request\Handler\UploadHandler;
use Jaxon\Request\Upload\UploadManager;
use Jaxon\Request\Validator;
use Jaxon\Response\Plugin\DataBag\DataBagPlugin;
use Jaxon\Response\Plugin\JQuery\JQueryPlugin;
use Jaxon\Response\ResponseManager;
use Jaxon\Utils\Config\Config;
use Jaxon\Utils\File\Minifier;
use Jaxon\Utils\Http\UriDetector;
use Jaxon\Utils\Template\Engine as TemplateEngine;
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
            return new PluginManager($c->g(Jaxon::class), $c->g(Config::class),
                $c->g(Translator::class), $c->g(CodeGenerator::class));
        });
        // Code Generation
        $this->set(AssetManager::class, function($c) {
            return new AssetManager($c->g(Config::class), $c->g(UriDetector::class), $c->g(Minifier::class));
        });
        $this->set(CodeGenerator::class, function($c) {
            return new CodeGenerator($c->g(Jaxon::class), $c->g(TemplateEngine::class), $c->g(AssetManager::class));
        });
        // File upload manager
        $this->set(UploadManager::class, function($c) {
            return new UploadManager($c->g(Config::class), $c->g(Validator::class), $c->g(Translator::class));
        });
        // File upload plugin
        $this->set(UploadHandler::class, function($c) {
            return !$c->g(Config::class)->getOption('core.upload.enabled') ? null :
                new UploadHandler($c->g(UploadManager::class), $c->g(Translator::class), $c->g(ResponseManager::class));
        });
        // JQuery response plugin
        $this->set(JQueryPlugin::class, function($c) {
            return new JQueryPlugin($c->g(Config::class));
        });
        // DataBagPlugin response plugin
        $this->set(DataBagPlugin::class, function() {
            return new DataBagPlugin();
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
     * Get the upload plugin
     *
     * @return UploadHandler|null
     */
    public function getUploadHandler(): ?UploadHandler
    {
        return $this->g(UploadHandler::class);
    }
}
