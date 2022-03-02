<?php

namespace Jaxon\Container\Traits;

use Jaxon\Jaxon;
use Jaxon\Plugin\Code\Generator as CodeGenerator;
use Jaxon\Plugin\Manager as PluginManager;
use Jaxon\Request\Plugin\FileUpload;
use Jaxon\Request\Support\FileUpload as FileUploadSupport;
use Jaxon\Request\Validator;
use Jaxon\Response\Manager as ResponseManager;
use Jaxon\Response\Plugin\DataBag;
use Jaxon\Response\Plugin\JQuery as JQueryPlugin;

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
            return new PluginManager($c->g(Jaxon::class), $c->g(CodeGenerator::class));
        });
        // File upload support
        $this->set(FileUploadSupport::class, function($c) {
            return new FileUploadSupport($c->g(Validator::class));
        });
        // File upload plugin
        $this->set(FileUpload::class, function($c) {
            return new FileUpload($c->g(ResponseManager::class), $c->g(FileUploadSupport::class));
        });
        // JQuery response plugin
        $this->set(JQueryPlugin::class, function() {
            return new JQueryPlugin();
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
    public function getPluginManager()
    {
        return $this->g(PluginManager::class);
    }
}
