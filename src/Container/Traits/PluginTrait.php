<?php

namespace Jaxon\Container\Traits;

use Jaxon\Jaxon;
use Jaxon\Plugin\Code\Generator as CodeGenerator;
use Jaxon\Plugin\Manager as PluginManager;
use Jaxon\Request\Upload\Plugin as UploadPlugin;
use Jaxon\Request\Upload\Upload as UploadSupport;
use Jaxon\Request\Validator;
use Jaxon\Response\Manager as ResponseManager;
use Jaxon\Response\Plugin\DataBag;
use Jaxon\Response\Plugin\JQuery as JQueryPlugin;
use Jaxon\Utils\Config\Config;
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
        // Plugin Manager
        $this->set(PluginManager::class, function($c) {
            return new PluginManager($c->g(Jaxon::class), $c->g(Config::class),
                $c->g(Translator::class), $c->g(CodeGenerator::class));
        });
        // File upload support
        $this->set(UploadSupport::class, function($c) {
            return new UploadSupport($c->g(Config::class), $c->g(Validator::class), $c->g(Translator::class));
        });
        // File upload plugin
        $this->set(UploadPlugin::class, function($c) {
            $xConfig = $c->g(Config::class);
            if(!$xConfig->getOption('core.upload.enabled'))
            {
                return null;
            }
            return new UploadPlugin($c->g(UploadSupport::class), $c->g(Translator::class), $c->g(ResponseManager::class));
        });
        // JQuery response plugin
        $this->set(JQueryPlugin::class, function($c) {
            return new JQueryPlugin($c->g(Config::class));
        });
        // DataBag response plugin
        $this->set(DataBag::class, function() {
            return new DataBag();
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
     * Get the upload plugin
     *
     * @return UploadPlugin|null
     */
    public function getUploadPlugin(): ?UploadPlugin
    {
        return $this->g(UploadPlugin::class);
    }
}
