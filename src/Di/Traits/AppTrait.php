<?php

namespace Jaxon\Di\Traits;

use Jaxon\Jaxon;
use Jaxon\App\App;
use Jaxon\App\Bootstrap;
use Jaxon\Config\ConfigManager;
use Jaxon\Plugin\Manager\PluginManager;
use Jaxon\Request\Handler\RequestHandler;
use Jaxon\Response\Manager\ResponseManager;
use Jaxon\Utils\Config\ConfigReader;
use Jaxon\Utils\Translation\Translator;

trait AppTrait
{
    /**
     * @var array The default config options
     */
    protected $aConfig =  [
        'core' => [
            'version'               => Jaxon::VERSION,
            'language'              => 'en',
            'encoding'              => 'utf-8',
            'decode_utf8'           => false,
            'prefix' => [
                'function'          => 'jaxon_',
                'class'             => 'Jaxon',
            ],
            'request' => [
                // 'uri'            => '',
                'mode'              => 'asynchronous',
                'method'            => 'POST', // W3C: Method is case sensitive
            ],
            'response' => [
                'send'              => true,
                'merge.ap'          => true,
                'merge.js'          => true,
            ],
            'debug' => [
                'on'                => false,
                'verbose'           => false,
            ],
            'process' => [
                'exit'              => true,
                'clean'             => false,
                'timeout'           => 6000,
            ],
            'error' => [
                'handle'            => false,
                'log_file'          => '',
            ],
            'jquery' => [
                'no_conflict'       => false,
            ],
            'upload' => [
                'enabled'           => true,
            ],
        ],
        'js' => [
            'lib' => [
                'output_id'         => 0,
                'queue_size'        => 0,
                'load_timeout'      => 2000,
                'show_status'       => false,
                'show_cursor'       => true,
            ],
            'app' => [
                'dir'               => '',
                'minify'            => true,
                'options'           => '',
            ],
        ],
    ];

    /**
     * Register the values into the container
     *
     * @return void
     */
    private function registerApp()
    {
        $this->set(ConfigManager::class, function($c) {
            $xConfigManager = new ConfigManager($c->g(ConfigReader::class), $c->g(Translator::class));
            $xConfigManager->setOptions($this->aConfig);
            return $xConfigManager;
        });
        // Jaxon App
        $this->set(App::class, function($c) {
            return new App($c->g(Jaxon::class), $c->g(ConfigManager::class),
                $c->g(ResponseManager::class), $c->g(Translator::class));
        });
        // Jaxon App bootstrap
        $this->set(Bootstrap::class, function($c) {
            return new Bootstrap($c->g(ConfigManager::class), $c->g(PluginManager::class), $c->g(RequestHandler::class));
        });
    }

    /**
     * Get the App instance
     *
     * @return App
     */
    public function getApp(): App
    {
        return $this->g(App::class);
    }

    /**
     * Get the App bootstrap
     *
     * @return Bootstrap
     */
    public function getBootstrap(): Bootstrap
    {
        return $this->g(Bootstrap::class);
    }
}
