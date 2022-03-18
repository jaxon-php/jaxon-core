<?php

namespace Jaxon\Container\Traits;

use Jaxon\Config\ConfigManager;
use Jaxon\Utils\Config\Config;
use Jaxon\Utils\Config\Reader;
use Jaxon\Utils\Translation\Translator;

trait ConfigTrait
{
    /**
     * Register the values into the container
     *
     * @return void
     */
    private function registerConfigs()
    {
        $this->set(Reader::class, function() {
            return new Reader();
        });
        $this->set(ConfigManager::class, function($c) {
            return new ConfigManager($c->g(Config::class), $c->g(Reader::class), $c->g(Translator::class));
        });
        $this->set(Config::class, function($c) {
            return new Config($c->g('jaxon.core.options'));
        });
    }
}
