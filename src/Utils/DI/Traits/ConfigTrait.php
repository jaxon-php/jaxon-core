<?php

namespace Jaxon\Utils\DI\Traits;

use Jaxon\Utils\Config\Config;
use Jaxon\Utils\Config\Reader;

trait ConfigTrait
{
    /**
     * Register the values into the container
     *
     * @return void
     */
    private function registerConfigs()
    {
        $this->set(Config::class, function($c) {
            $config = new Config();
            $config->setOptions($c->g('jaxon.core.options'));
            return $config;
        });
        $this->set(Reader::class, function($c) {
            return new Reader($c->g(Config::class));
        });
    }

    /**
     * Get the config manager
     *
     * @return Config
     */
    public function getConfig()
    {
        return $this->g(Config::class);
    }

    /**
     * Get the config reader
     *
     * @return Reader
     */
    public function getConfigReader()
    {
        return $this->g(Reader::class);
    }

    /**
     * Create a new the config manager
     *
     * @param array             $aOptions           The options array
     * @param string            $sKeys              The keys of the options in the array
     *
     * @return Config            The config manager
     */
    public function newConfig(array $aOptions = [], $sKeys = '')
    {
        return new Config($aOptions, $sKeys);
    }
}
