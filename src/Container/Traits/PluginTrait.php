<?php

namespace Jaxon\Container\Traits;

use Jaxon\Jaxon;
use Jaxon\Plugin\Code\Generator as CodeGenerator;
use Jaxon\Plugin\Manager as PluginManager;
use Jaxon\Request\Plugin\Upload\Manager as UploadManager;
use Jaxon\Request\Plugin\Upload\UploadPlugin;
use Jaxon\Request\Validator;
use Jaxon\Response\Manager as ResponseManager;
use Jaxon\Response\Plugin\DataBag\DataBagPlugin;
use Jaxon\Response\Plugin\JQuery\JQueryPlugin;
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
        // UploadPlugin Manager
        $this->set(PluginManager::class, function($c) {
            return new PluginManager($c->g(Jaxon::class), $c->g(Config::class),
                $c->g(Translator::class), $c->g(CodeGenerator::class));
        });
        // File upload support
        $this->set(UploadManager::class, function($c) {
            return new UploadManager($c->g(Config::class), $c->g(Validator::class), $c->g(Translator::class));
        });
        // File upload plugin
        $this->set(UploadPlugin::class, function($c) {
            $xConfig = $c->g(Config::class);
            if(!$xConfig->getOption('core.upload.enabled'))
            {
                return null;
            }
            return new UploadPlugin($c->g(UploadManager::class), $c->g(Translator::class), $c->g(ResponseManager::class));
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
     * Get the upload plugin
     *
     * @return UploadPlugin|null
     */
    public function getUploadPlugin(): ?UploadPlugin
    {
        return $this->g(UploadPlugin::class);
    }
}
